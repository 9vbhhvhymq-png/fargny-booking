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

// Claim the (key, recipient) pair. Returns false when it is already
// claimed, which is what makes every run safe to repeat.
function notif_claim(string $key, string $email, ?int $userId, string $subject): bool {
    ensure_notification_tables();
    try {
        $stmt = get_db()->prepare("
            INSERT INTO fargny_notifications_log
                (notification_key, recipient_email, recipient_user_id, subject, status)
            VALUES (?, ?, ?, ?, 'sending')
        ");
        $stmt->execute([$key, $email, $userId, mb_substr($subject, 0, 255)]);
        return true;
    } catch (Exception $e) {
        return false;   // UNIQUE violation: already handled
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
    $result = send_email_result($email, $subject, $body);
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
