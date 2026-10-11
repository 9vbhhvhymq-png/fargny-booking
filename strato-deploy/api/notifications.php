<?php
// ============================================================
// Notification infrastructure: the log, the lock, the templates.
//
// The job definitions live in notify.php; this file is only the
// machinery that makes sending safe to repeat. The guiding rule is
// that a second send is worse than a late one — these go to the whole
// family, and a duplicate cannot be recalled.
// ============================================================

function ensure_notification_tables() {
    static $done = false;
    if ($done) return;
    $done = true;
    $db = get_db();

    // One row per (notification, recipient). The UNIQUE key is the thing
    // that actually prevents duplicates: the row is claimed BEFORE the
    // send, so a crash mid-send leaves a 'sending' row rather than an
    // unrecorded delivery that a retry would repeat.
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `fargny_notifications_log` (
              `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `notification_key` VARCHAR(100) NOT NULL,
              `recipient_email`  VARCHAR(255) NOT NULL,
              `recipient_user_id` INT UNSIGNED DEFAULT NULL,
              `subject`          VARCHAR(255) DEFAULT NULL,
              `status`           VARCHAR(20) NOT NULL DEFAULT 'sending',
              `attempts`         TINYINT UNSIGNED NOT NULL DEFAULT 1,
              `error`            VARCHAR(500) DEFAULT NULL,
              `transport`        VARCHAR(20)  DEFAULT NULL,
              `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `sent_at`          DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_notification_recipient` (`notification_key`, `recipient_email`),
              KEY `idx_key` (`notification_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (Exception $e) {}

    // A single advisory lock so two overlapping scheduler calls — cron and
    // a page load, or cron running long — cannot both start a broadcast.
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `fargny_scheduler_lock` (
              `name`       VARCHAR(40) NOT NULL,
              `holder`     VARCHAR(64) NOT NULL,
              `acquired_at` DATETIME NOT NULL,
              `expires_at` DATETIME NOT NULL,
              PRIMARY KEY (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (Exception $e) {}

    // Editable bodies, so wording can be fixed without a deploy.
    // Dutch first and English second, in one message, per the family's
    // decision — there is no per-member language preference.
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `fargny_email_templates` (
              `tpl_key`    VARCHAR(60) NOT NULL,
              `subject_nl` VARCHAR(255) NOT NULL DEFAULT '',
              `subject_en` VARCHAR(255) NOT NULL DEFAULT '',
              `body_nl`    MEDIUMTEXT,
              `body_en`    MEDIUMTEXT,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`tpl_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (Exception $e) {}
}

// ---- The lock ---------------------------------------------------------

function scheduler_lock_acquire(int $ttlSeconds = 300): ?string {
    ensure_notification_tables();
    $db = get_db();
    $holder = bin2hex(random_bytes(8));
    try {
        // Take it if free, or if the previous holder's lease has expired —
        // a crashed run must not block the scheduler for ever.
        $db->prepare("DELETE FROM fargny_scheduler_lock WHERE name = 'notify' AND expires_at < NOW()")
           ->execute();
        $db->prepare("
            INSERT INTO fargny_scheduler_lock (name, holder, acquired_at, expires_at)
            VALUES ('notify', ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND))
        ")->execute([$holder, $ttlSeconds]);
        return $holder;
    } catch (Exception $e) {
        return null;   // duplicate key: someone else holds it
    }
}

function scheduler_lock_release(?string $holder) {
    if (!$holder) return;
    try {
        get_db()->prepare("DELETE FROM fargny_scheduler_lock WHERE name = 'notify' AND holder = ?")
                ->execute([$holder]);
    } catch (Exception $e) {}
}

// ---- Sending one notification ----------------------------------------

// How many times a failed send is retried before it is left alone.
const NOTIF_MAX_ATTEMPTS = 3;

// Claim the (key, recipient) pair. Returns false when this recipient is
// already dealt with, which is what makes every run safe to repeat.
//
// A row that previously FAILED is re-claimed: mail() told us plainly that
// it did not queue the message, so trying again cannot duplicate anything
// and not trying again silently drops that person from the announcement.
// 'sent' and 'dry_run' are final. 'sending' is treated as final too — a
// run that died mid-send may well have delivered, and a possible
// duplicate is worse than a possible miss.
function notif_claim(string $key, string $email, ?int $userId, string $subject): bool {
    ensure_notification_tables();
    $db = get_db();
    try {
        $stmt = $db->prepare("
            INSERT INTO fargny_notifications_log
                (notification_key, recipient_email, recipient_user_id, subject, status)
            VALUES (?, ?, ?, ?, 'sending')
        ");
        $stmt->execute([$key, $email, $userId, mb_substr($subject, 0, 255)]);
        return true;
    } catch (Exception $e) {
        // Already a row. Retry it only if the last attempt genuinely failed
        // and we have not already tried too often.
        try {
            $upd = $db->prepare("
                UPDATE fargny_notifications_log
                SET status = 'sending', attempts = attempts + 1
                WHERE notification_key = ? AND recipient_email = ?
                  AND status = 'failed' AND attempts < ?
            ");
            $upd->execute([$key, $email, NOTIF_MAX_ATTEMPTS]);
            return $upd->rowCount() > 0;
        } catch (Exception $e2) {
            return false;
        }
    }
}

function notif_finish(string $key, string $email, array $result) {
    try {
        get_db()->prepare("
            UPDATE fargny_notifications_log
            SET status = ?, error = ?, transport = ?, sent_at = NOW()
            WHERE notification_key = ? AND recipient_email = ?
        ")->execute([
            $result['ok'] ? ($result['transport'] === 'dry-run' ? 'dry_run' : 'sent') : 'failed',
            $result['error'] ?: null,
            $result['transport'] ?? null,
            $key, $email,
        ]);
    } catch (Exception $e) {}
}

// Send one message to one person, exactly once, ever.
// $key must already identify the year, e.g. "kickoff_2027".
function notif_send_once(string $key, array $member, string $subject, string $body): string {
    $email = trim((string)($member['email'] ?? ''));
    if ($email === '') return 'skipped_no_email';
    if (!notif_claim($key, $email, isset($member['id']) ? (int)$member['id'] : null, $subject)) {
        return 'already_sent';
    }
    // Broadcasts are bulk; the headers say so.
    $result = send_email_result($email, $subject, $body, true);
    notif_finish($key, $email, $result);
    return $result['ok'] ? ($result['transport'] === 'dry-run' ? 'dry_run' : 'sent') : 'failed';
}

// Has this notification gone out to anybody yet?
function notif_any_sent(string $key): bool {
    ensure_notification_tables();
    try {
        $stmt = get_db()->prepare("
            SELECT 1 FROM fargny_notifications_log
            WHERE notification_key = ? AND status IN ('sent','dry_run','sending') LIMIT 1
        ");
        $stmt->execute([$key]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        // Unreadable log means we cannot prove it has not gone out.
        // Claim it has, and send nothing.
        return true;
    }
}

// ---- Templates --------------------------------------------------------

// Stored body for a template, or null when it has never been edited —
// in which case the built-in default in notify_templates.php is used.
function template_get(string $key): ?array {
    ensure_notification_tables();
    try {
        $stmt = get_db()->prepare("SELECT * FROM fargny_email_templates WHERE tpl_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Exception $e) { return null; }
}

function template_set(string $key, array $fields) {
    ensure_notification_tables();
    try {
        get_db()->prepare("
            INSERT INTO fargny_email_templates (tpl_key, subject_nl, subject_en, body_nl, body_en, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                subject_nl = VALUES(subject_nl), subject_en = VALUES(subject_en),
                body_nl    = VALUES(body_nl),    body_en    = VALUES(body_en),
                updated_at = NOW()
        ")->execute([
            $key,
            (string)($fields['subject_nl'] ?? ''), (string)($fields['subject_en'] ?? ''),
            (string)($fields['body_nl'] ?? ''),    (string)($fields['body_en'] ?? ''),
        ]);
    } catch (Exception $e) {}
}

// ---- The shared footer every broadcast carries -------------------------
// Three things the family asked for on every announcement: a way into the
// calendar, a reminder that this is the first year and entries are worth
// checking, and a human to contact. WhatsApp leads because that is the
// channel that actually gets read; email is the fallback.
//
// All of it is configurable — no person is hard-coded into a template.
function notify_contact(): array {
    return [
        'name'     => setting_get('contact_name',     'Moritz'),
        'email'    => setting_get('contact_email',    'moritz@fromageot.eu'),
        'whatsapp' => setting_get('contact_whatsapp', '4915788611151'),
    ];
}

function notify_app_url(): string {
    return rtrim(setting_get('app_url', 'https://fargny.org/booking/'), '/') . '/';
}

// The bilingual footer, Dutch first. $lang picks which half to render;
// 'both' returns the pair, which is what a broadcast uses.
function notify_footer_html(string $lang = 'both'): string {
    $c   = notify_contact();
    $url = htmlspecialchars(notify_app_url());
    $wa  = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $c['whatsapp']);
    $who = htmlspecialchars($c['name']);
    $mail= htmlspecialchars($c['email']);

    $nl = '<hr style="border:none;border-top:1px solid #E0D8CF;margin:24px 0;">
    <p style="color:#2C1810;font-size:14px;margin:0 0 8px 0;">
      <a href="' . $url . '" style="color:#B85042;font-weight:600;">Open de kalender</a>
    </p>
    <p style="color:#8B7D6B;font-size:13px;margin:0 0 8px 0;">
      Dit is het eerste jaar met het nieuwe systeem. Controleer alsjeblieft of jouw
      boekingen er goed in staan.
    </p>
    <p style="color:#8B7D6B;font-size:13px;margin:0;">
      Klopt er iets niet? Stuur ' . $who . ' het liefst een
      <a href="' . $wa . '" style="color:#B85042;">WhatsApp-bericht</a>,
      of mail <a href="mailto:' . $mail . '" style="color:#B85042;">' . $mail . '</a>.
    </p>';

    $en = '<hr style="border:none;border-top:1px solid #E0D8CF;margin:24px 0;">
    <p style="color:#2C1810;font-size:14px;margin:0 0 8px 0;">
      <a href="' . $url . '" style="color:#B85042;font-weight:600;">Open the calendar</a>
    </p>
    <p style="color:#8B7D6B;font-size:13px;margin:0 0 8px 0;">
      This is the first year of the new system. Please check that your bookings
      appear the way you intended.
    </p>
    <p style="color:#8B7D6B;font-size:13px;margin:0;">
      If something looks wrong, the quickest way to reach ' . $who . ' is
      <a href="' . $wa . '" style="color:#B85042;">WhatsApp</a>;
      otherwise email <a href="mailto:' . $mail . '" style="color:#B85042;">' . $mail . '</a>.
    </p>';

    if ($lang === 'nl') return $nl;
    if ($lang === 'en') return $en;
    return $nl . $en;
}

// A language divider for the one-message-two-languages format.
function notify_lang_divider(): string {
    return '<div style="margin:28px 0 8px 0;padding-top:16px;border-top:2px solid #E0D8CF;
            font-size:11px;font-weight:700;letter-spacing:.08em;color:#8B7D6B;">ENGLISH</div>';
}

// ---- Broadcasting ------------------------------------------------------
// One message per recipient, never a shared To or CC line: a family
// address list in a header is both a privacy leak and a reliable way to
// be classified as bulk mail.
//
// Paced, because a burst of thirty messages through a shared host's
// mail() is exactly what rate limiters are built to catch. The run is
// resumable by construction — each recipient is claimed individually, so
// a run cut short by a timeout simply continues where it stopped.
function notify_broadcast(string $key, array $members, callable $compose, bool $force = false): array {
    $tally = ['sent' => 0, 'already_sent' => 0, 'failed' => 0,
              'dry_run' => 0, 'skipped_no_email' => 0, 'blocked' => 0];

    if (!$force && !notifications_enabled()) {
        $tally['blocked'] = count($members);
        return $tally;
    }

    $pauseMs = (int)setting_get('broadcast_pause_ms', '400');
    $pauseMs = max(0, min($pauseMs, 5000));
    $first   = true;

    foreach ($members as $m) {
        if (!$first && $pauseMs > 0) usleep($pauseMs * 1000);
        $first = false;
        // $compose returns [subject, html] and may personalise per member.
        [$subject, $html] = $compose($m);
        $status = notif_send_once($key, $m, $subject, $html);
        if (!isset($tally[$status])) $tally[$status] = 0;
        $tally[$status]++;
    }
    return $tally;
}

// The master switch. Broadcasts are OFF until someone turns them on,
// so that configuring a cron job cannot by itself put mail in front of
// the whole family before the wording has been read.
// Test-sends and previews ignore this; only real broadcasts honour it.
function notifications_enabled(): bool {
    return setting_get('notifications_enabled', '0') === '1';
}

// Everyone who should receive a broadcast: registered members with an
// address, minus anyone who opted out of the recurring digest. The
// announcements in the annual cycle are not optional — they are how the
// rounds are run — so only the recurring mail honours the opt-out.
function notify_recipients(bool $honourOptOut = false): array {
    ensure_profile_columns();
    $sql = "SELECT id, display_name, email, branch_id FROM fargny_users WHERE email <> ''";
    if ($honourOptOut) $sql .= " AND (notify_opt_out IS NULL OR notify_opt_out = 0)";
    $sql .= " ORDER BY display_name";
    try { return get_db()->query($sql)->fetchAll() ?: []; }
    catch (Exception $e) {
        try { return get_db()->query("SELECT id, display_name, email, branch_id
                                      FROM fargny_users WHERE email <> ''")->fetchAll() ?: []; }
        catch (Exception $e2) { return []; }
    }
}

// The sign-off line for templates whose own text already carries the
// first-year note and the contact: the way in, and the number, on one
// line. Both still come from settings rather than being written in.
function notify_calendar_link_html(string $lang): string {
    $url   = htmlspecialchars(notify_app_url());
    $c     = notify_contact();
    $digits= preg_replace('/[^0-9]/', '', (string)$c['whatsapp']);
    $label = $lang === 'nl' ? 'Open de kalender' : 'Open the calendar';
    // +49 157 88611151 — grouped the way people read it, not as a blob.
    $pretty = $digits !== ''
        ? '+' . substr($digits, 0, 2) . ' ' . substr($digits, 2, 3) . ' ' . substr($digits, 5)
        : '';
    $wa = $digits !== ''
        ? ' &middot; WhatsApp: <a href="https://wa.me/' . $digits
          . '" style="color:#B85042;font-weight:600;">' . htmlspecialchars($pretty) . '</a>'
        : '';
    return '<p style="margin:20px 0 0 0;font-size:14px;color:#8B7D6B;">'
         . '<a href="' . $url . '" style="color:#B85042;font-weight:600;">' . $label . '</a>'
         . $wa . '</p>';
}
