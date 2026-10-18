<?php
// ============================================================
// Admin: book on behalf, parse email, cancellations, payments
// ============================================================

function handle_admin(string $action, string $id, string $method) {
    switch ($action) {
        case 'book':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_book();
            break;
        case 'parse-email':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_parse_email();
            break;
        case 'pending-cancellations':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_pending_cancellations();
            break;
        case 'approve-cancellation':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_approve_cancellation($id);
            break;
        case 'reject-cancellation':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_reject_cancellation($id);
            break;
        case 'update-payment-status':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_update_payment_status($id);
            break;
        case 'payment-overview':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_payment_overview();
            break;
        case 'delete-booking':
            if ($method !== 'POST' && $method !== 'DELETE') json_error('POST or DELETE required', 405);
            admin_delete_booking($id);
            break;
        case 'empty-dates':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_empty_dates();
            break;
        case 'no-email':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_members_without_email();
            break;
        case 'notifications':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_notifications();
            break;
        case 'notification-preview':
            if ($method !== 'GET') json_error('GET required', 405);
            admin_notification_preview();
            break;
        case 'notification-test':
            if ($method !== 'POST') json_error('POST required', 405);
            admin_notification_test();
            break;
        case 'notification-settings':
            if ($method !== 'POST' && $method !== 'PUT') json_error('POST required', 405);
            admin_notification_settings();
            break;
        case 'gate':
            if ($method === 'GET') gate_admin_get();
            elseif ($method === 'POST' || $method === 'PUT') gate_admin_save();
            else json_error('GET or POST required', 405);
            break;
        default:
            json_error('Unknown admin action', 404);
    }
}

function admin_book() {
    $admin = require_admin();
    $body = get_json_body();
    $db = get_db();

    $targetUserId = (int)($body['user_id'] ?? 0);
    $weekId       = $body['week_id'] ?? '';
    $year         = (int)($body['year'] ?? date('Y'));
    $phase        = $body['phase'] ?? 'regular';

    if (!$targetUserId || !$weekId) json_error('user_id and week_id required');
    if (!in_array($phase, ['clan', 'priority', 'regular'])) json_error('Invalid phase');

    // Get target user
    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$targetUserId]);
    $target = $stmt->fetch();
    if (!$target) json_error('User not found', 404);

    // Check no duplicate
    $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE week_id = ? AND user_id = ? AND cancellation_status != 'approved' LIMIT 1");
    $stmt->execute([$weekId, $targetUserId]);
    if ($stmt->fetch()) json_error('User already has a booking for this week');

    $stmt = $db->prepare("
        INSERT INTO fargny_bookings
            (week_id, year, user_id, branch_id, phase, admin_booked, admin_user_id)
        VALUES (?, ?, ?, ?, ?, 1, ?)
    ");
    $stmt->execute([$weekId, $year, $targetUserId, $target['branch_id'], $phase, $admin['id']]);
    $bookingId = (int)$db->lastInsertId();
    $seq = assign_booking_number($bookingId, $year);

    // Create payment record
    $db->prepare("INSERT INTO fargny_payments (booking_id) VALUES (?)")->execute([$bookingId]);

    // Send confirmation
    try {
        require_once __DIR__ . '/email.php';
        $weeks = generate_weeks($year);
        $week = null;
        foreach ($weeks as $w) { if ($w['id'] === $weekId) { $week = $w; break; } }
        send_booking_confirmation($target, [
            'id' => $bookingId, 'week_id' => $weekId, 'phase' => $phase,
            'booking_number' => format_booking_number($year, $seq),
            'check_in_date' => $week['start'] ?? '', 'check_out_date' => $week['end'] ?? '',
            'admin_booked' => true,
        ]);
    } catch (Exception $e) {}

    json_success(['booking_id' => $bookingId, 'booking_number' => format_booking_number($year, $seq)], 201);
}

// Bookings stored without explicit dates. These are the only rows the old
// backfill would have touched, and the only ones whose real dates are
// guesswork: the app falls back to "the whole week" when it displays them.
// Read-only on purpose — deciding what each one should be is a human job.
function admin_empty_dates() {
    require_admin();
    $db = get_db();
    $rows = $db->query("
        SELECT b.id, b.booking_seq, b.week_id, b.year, b.phase,
               b.check_in_date, b.check_out_date, b.booked_at,
               u.display_name, u.email, br.name AS branch_name
        FROM fargny_bookings b
        JOIN fargny_users u  ON u.id  = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        WHERE (b.check_in_date IS NULL OR b.check_out_date IS NULL)
          AND b.cancellation_status NOT IN ('approved')
        ORDER BY b.year, b.week_id
    ")->fetchAll();

    // Show what the week grid says now (Friday) against what the old
    // migration would have written (Saturday), so the difference is visible.
    $out = [];
    foreach ($rows as $r) {
        $y = (int)$r['year'];
        $friday = null; $saturday = null;
        foreach (generate_weeks($y) as $w) {
            if ($w['id'] === $r['week_id']) { $friday = $w; break; }
        }
        foreach (generate_weeks_saturday($y) as $w) {
            if ($w['id'] === $r['week_id']) { $saturday = $w; break; }
        }
        $out[] = [
            'id'              => (int)$r['id'],
            'booking_number'  => format_booking_number($r['year'], $r['booking_seq']),
            'member'          => $r['display_name'],
            'branch'          => $r['branch_name'],
            'phase'           => $r['phase'],
            'week_id'         => $r['week_id'],
            'booked_at'       => $r['booked_at'],
            'stored_check_in' => $r['check_in_date'],
            'stored_check_out'=> $r['check_out_date'],
            'friday_grid'     => $friday   ? ($friday['start']   . ' .. ' . $friday['end'])   : null,
            'saturday_grid'   => $saturday ? ($saturday['start'] . ' .. ' . $saturday['end']) : null,
        ];
    }
    json_success(['count' => count($out), 'bookings' => $out]);
}

// People on the shareholder roster who have never registered an account.
// fargny_users.email is NOT NULL, so a registered member always has an
// address; the ones who hear nothing are those with no account at all.
function admin_members_without_email() {
    require_admin();
    $db = get_db();
    $rows = $db->query("
        SELECT s.id, s.full_name, br.name AS branch_name
        FROM fargny_shareholders s
        JOIN fargny_branches br ON br.id = s.branch_id
        WHERE s.user_id IS NULL
        ORDER BY br.name, s.full_name
    ")->fetchAll();
    json_success([
        'count'   => count($rows),
        'members' => array_map(function ($r) {
            return ['shareholder_id' => (int)$r['id'],
                    'full_name'      => $r['full_name'],
                    'branch'         => $r['branch_name']];
        }, $rows),
    ]);
}

// ---- Notifications ---------------------------------------------------

// What is scheduled, what has gone out, and to whom.
function admin_notifications() {
    require_admin();
    require_once __DIR__ . '/email.php';
    require_once __DIR__ . '/notifications.php';
    require_once __DIR__ . '/notify.php';

    $status = notify_status($_GET['as_of'] ?? null);

    $log = [];
    try {
        $log = get_db()->query("
            SELECT notification_key, recipient_email, subject, status, attempts,
                   error, transport, created_at, sent_at
            FROM fargny_notifications_log
            ORDER BY COALESCE(sent_at, created_at) DESC
            LIMIT 300
        ")->fetchAll() ?: [];
    } catch (Exception $e) {}

    // A per-notification roll-up, which is what you actually want to see
    // after a broadcast: how many landed, how many failed, who failed.
    $summary = [];
    foreach ($log as $r) {
        $k = $r['notification_key'];
        if (!isset($summary[$k])) $summary[$k] = ['key'=>$k,'sent'=>0,'failed'=>0,
                                                  'dry_run'=>0,'sending'=>0,'last'=>null,'failures'=>[]];
        $st = $r['status'];
        if (!isset($summary[$k][$st])) $summary[$k][$st] = 0;
        $summary[$k][$st]++;
        if ($st === 'failed') $summary[$k]['failures'][] = $r['recipient_email'];
        $when = $r['sent_at'] ?: $r['created_at'];
        if ($when && (!$summary[$k]['last'] || $when > $summary[$k]['last'])) $summary[$k]['last'] = $when;
    }

    $status['log']     = $log;
    $status['summary'] = array_values($summary);
    $status['contact'] = notify_contact();
    // Shown so it can be copied into the GitHub secret. Admin-only, and
    // the admin is the person who set it.
    $status['notify_token']      = notify_token();
    $status['notify_token_from'] = ((string)env('NOTIFY_TOKEN', '') !== '') ? 'env' : 'settings';
    $status['scheduler_fallback'] = setting_get('scheduler_fallback', '0') === '1';
    json_success($status);
}

// Render a notification exactly as it would be sent, with sample data if
// asked. Never sends; safe to open at any time.
function admin_notification_preview() {
    $admin = require_admin();
    require_once __DIR__ . '/email.php';
    require_once __DIR__ . '/notifications.php';
    require_once __DIR__ . '/notify_templates.php';
    require_once __DIR__ . '/notify.php';

    $tpl  = (string)($_GET['template'] ?? 'kickoff');
    $year = (int)($_GET['year'] ?? active_booking_year());
    if (!isset(notify_default_templates()[$tpl])) json_error('Unknown template', 404);

    // Sample data by default so the preview shows what a member sees,
    // not what the admin happens to be called.
    $sample = !isset($_GET['real']) || $_GET['real'] !== '1';
    $member = $sample
        ? ['id' => 0, 'display_name' => 'Barbara van de Loo', 'email' => 'voorbeeld@example.org']
        : ['id' => (int)$admin['id'], 'display_name' => $admin['display_name'], 'email' => $admin['email']];

    [$subject, $html] = notify_compose($tpl, $member, $year);
    json_success(['template' => $tpl, 'year' => $year, 'sample' => $sample,
                  'subject' => $subject, 'html' => $html]);
}

// Send one notification to one address, now, ignoring both the master
// switch and the log. This is how the wording gets checked against a
// real inbox before it goes to the family.
function admin_notification_test() {
    $admin = require_admin();
    require_once __DIR__ . '/email.php';
    require_once __DIR__ . '/notifications.php';
    require_once __DIR__ . '/notify_templates.php';
    require_once __DIR__ . '/notify.php';

    $body = get_json_body();
    $tpl  = (string)($body['template'] ?? 'kickoff');
    $to   = trim((string)($body['to'] ?? ($admin['email'] ?? '')));
    $year = (int)($body['year'] ?? active_booking_year());

    if (!isset(notify_default_templates()[$tpl])) json_error('Unknown template', 404);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) json_error('A valid address is required');

    // Addressed to the admin by name, so the test reads like the real thing.
    $member = ['id' => (int)$admin['id'],
               'display_name' => $admin['display_name'], 'email' => $to];
    [$subject, $html] = notify_compose($tpl, $member, $year);

    // Deliberately NOT notif_send_once: a test must be repeatable, and it
    // must not consume the log entry that protects the real broadcast.
    $subject = '[TEST] ' . $subject;
    $result  = send_email_result($to, $subject, $html, true);

    json_success(['to' => $to, 'template' => $tpl, 'subject' => $subject,
                  'sent' => $result['ok'], 'transport' => $result['transport'],
                  'error' => $result['error'],
                  'note' => mail_dry_run()
                      ? 'Dry run is ON, so nothing was actually sent.'
                      : 'Sent via ' . $result['transport'] . '.']);
}

// The two switches and the contact details.
function admin_notification_settings() {
    require_admin();
    $body = get_json_body();
    $allowed = ['notifications_enabled', 'mail_dry_run', 'broadcast_pause_ms',
                'contact_name', 'contact_email', 'contact_whatsapp', 'app_url',
                'notify_token', 'scheduler_fallback'];
    $saved = [];
    foreach ($allowed as $k) {
        if (!array_key_exists($k, $body)) continue;
        $v = $body[$k];
        if (in_array($k, ['notifications_enabled', 'mail_dry_run'], true)) {
            $v = !empty($v) ? '1' : '0';
        }
        setting_set($k, (string)$v);
        $saved[$k] = (string)$v;
    }
    json_success(['saved' => $saved]);
}

function admin_parse_email() {
    require_admin();
    $body = get_json_body();
    $emailText = $body['email_text'] ?? '';
    if (!$emailText) json_error('email_text required');

    $apiKey = env('ANTHROPIC_API_KEY');
    if (!$apiKey) json_error('Anthropic API key not configured', 500);

    $db = get_db();
    $users = $db->query("SELECT id, display_name, email FROM fargny_users ORDER BY display_name")->fetchAll();
    $userNames = array_map(fn($u) => $u['display_name'], $users);

    $year = (int)($_GET['year'] ?? date('Y'));
    $weeks = generate_weeks($year);
    $weekList = array_map(fn($w) => $w['id'] . ' (' . $w['start'] . ' to ' . $w['end'] . ')', $weeks);

    $prompt = "Extract the booking request from this email. The registered users are: " . implode(', ', $userNames) .
              ". Available weeks for $year: " . implode('; ', array_slice($weekList, 0, 52)) .
              ".\n\nEmail:\n" . $emailText .
              "\n\nRespond with JSON only: {\"user_name\": \"...\", \"week_id\": \"...\", \"confidence\": \"high|medium|low\"}";

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 200,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        json_error('Failed to parse email (API error)', 502);
    }

    $data = json_decode($response, true);
    $text = $data['content'][0]['text'] ?? '';

    // Try to extract JSON from the response
    if (preg_match('/\{[^}]+\}/', $text, $m)) {
        $parsed = json_decode($m[0], true);
        if ($parsed) {
            // Match user
            $matchedUser = null;
            foreach ($users as $u) {
                if (stripos($u['display_name'], $parsed['user_name'] ?? '') !== false) {
                    $matchedUser = $u;
                    break;
                }
            }
            // Match week
            $matchedWeek = null;
            foreach ($weeks as $w) {
                if ($w['id'] === ($parsed['week_id'] ?? '')) {
                    $matchedWeek = $w;
                    break;
                }
            }
            json_success([
                'parsed_name'  => $parsed['user_name'] ?? null,
                'parsed_week'  => $parsed['week_id'] ?? null,
                'confidence'   => $parsed['confidence'] ?? 'low',
                'matched_user' => $matchedUser ? ['id' => (int)$matchedUser['id'], 'display_name' => $matchedUser['display_name']] : null,
                'matched_week' => $matchedWeek,
            ]);
        }
    }
    json_error('Could not parse the email');
}

function admin_pending_cancellations() {
    require_admin();
    $db = get_db();

    $stmt = $db->query("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.cancellation_status = 'pending'
        ORDER BY b.booked_at DESC
    ");
    $all = $stmt->fetchAll();
    json_success(array_map('format_booking', $all));
}

function admin_approve_cancellation(string $idStr) {
    require_admin();
    $id = (int)$idStr;
    $db = get_db();

    $stmt = $db->prepare("SELECT b.*, u.display_name, u.email FROM fargny_bookings b JOIN fargny_users u ON u.id = b.user_id WHERE b.id = ? LIMIT 1");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);

    // Giving up a priority booking hands the priority back for other dates,
    // but shuts these nights to that member as a regular booking.
    record_priority_release($booking);

    // Actually delete the booking (and its payment record) so the week
    // becomes free again and the user disappears from all lists.
    $db->prepare("DELETE FROM fargny_payments WHERE booking_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM fargny_bookings WHERE id = ?")->execute([$id]);

    // Notify user
    try {
        require_once __DIR__ . '/email.php';
        send_cancellation_approved(['email' => $booking['email'], 'display_name' => $booking['display_name']], $booking);
    } catch (Exception $e) {}

    json_success(null);
}

function admin_reject_cancellation(string $idStr) {
    require_admin();
    $id = (int)$idStr;
    $db = get_db();

    $db->prepare("UPDATE fargny_bookings SET cancellation_status = 'none' WHERE id = ?")->execute([$id]);
    json_success(null);
}

function admin_update_payment_status(string $bookingIdStr) {
    require_admin();
    $bookingId = (int)$bookingIdStr;
    $body = get_json_body();
    $status = $body['status'] ?? '';

    if (!in_array($status, ['not_paid', 'invoice_sent', 'paid'])) {
        json_error('Invalid payment status');
    }

    $db = get_db();
    // Upsert payment record
    $stmt = $db->prepare("SELECT id FROM fargny_payments WHERE booking_id = ? LIMIT 1");
    $stmt->execute([$bookingId]);
    if ($stmt->fetch()) {
        $db->prepare("UPDATE fargny_payments SET status = ? WHERE booking_id = ?")->execute([$status, $bookingId]);
    } else {
        $db->prepare("INSERT INTO fargny_payments (booking_id, status) VALUES (?, ?)")->execute([$bookingId, $status]);
    }

    json_success(['status' => $status]);
}

function admin_payment_overview() {
    require_admin();
    $year = (int)($_GET['year'] ?? date('Y'));
    $statusFilter = $_GET['status'] ?? null;
    $db = get_db();

    $sql = "
        SELECT b.id, b.week_id, b.year, b.phase, b.booked_at,
               u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status,
               COALESCE(p.total, 0) AS total,
               p.guest_data, p.house_fee, p.person_fee, p.cleaning_fee
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.year = ? AND b.cancellation_status NOT IN ('approved')
    ";
    $params = [$year];
    if ($statusFilter && in_array($statusFilter, ['not_paid', 'invoice_sent', 'paid'])) {
        $sql .= " AND COALESCE(p.status, 'not_paid') = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY b.booked_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    json_success($stmt->fetchAll());
}

// Remove a booking outright. Unlike a member cancellation this needs no
// approval step — it is the admin acting directly — so the row and its
// payment record are deleted and the dates are free again.
function admin_delete_booking(string $idStr) {
    require_admin();
    $id = (int)$idStr;
    if (!$id) json_error('Booking id required');

    $body = get_json_body();
    $note = (string)($body['admin_note'] ?? '');

    $db = get_db();
    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        WHERE b.id = ? LIMIT 1
    ");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);

    // Same as an approved cancellation: a priority stay that is deleted
    // frees the member's priority for other dates and closes these nights
    // to them as a regular booking.
    record_priority_release($booking);

    $db->prepare("DELETE FROM fargny_payments WHERE booking_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM fargny_bookings WHERE id = ?")->execute([$id]);

    // Let the member know their booking is gone, and why.
    try {
        require_once __DIR__ . '/email.php';
        send_booking_deleted(
            ['display_name' => $booking['display_name'], 'email' => $booking['email']],
            $booking,
            $note
        );
    } catch (Exception $e) {}

    json_success(['deleted' => $id]);
}
