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
            'check_in_date' => $week['start'] ?? '', 'check_out_date' => $week['end'] ?? '',
            'admin_booked' => true,
        ]);
    } catch (Exception $e) {}

    json_success(['booking_id' => $bookingId], 201);
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
