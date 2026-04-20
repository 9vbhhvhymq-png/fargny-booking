<?php
// ============================================================
// Bookings: CRUD, calendar, public-calendar
// ============================================================

function handle_bookings(string $action, string $id, string $method) {
    switch ($action) {
        case 'calendar':
            if ($method !== 'GET') json_error('GET required', 405);
            bookings_calendar();
            break;
        case 'public-calendar':
            if ($method !== 'GET') json_error('GET required', 405);
            bookings_public_calendar();
            break;
        case '':
            if ($method === 'GET') { bookings_list(); }
            elseif ($method === 'POST') { bookings_create(); }
            else json_error('Method not allowed', 405);
            break;
        default:
            // action is a booking ID
            if ($method === 'DELETE') { bookings_cancel($action); }
            else json_error('Method not allowed', 405);
    }
}

function format_booking(array $b): array {
    return [
        'id'                  => (int)$b['id'],
        'week_id'             => $b['week_id'],
        'year'                => (int)$b['year'],
        'user_id'             => (int)$b['user_id'],
        'user_name'           => $b['display_name'] ?? '',
        'user_email'          => $b['email'] ?? '',
        'branch_id'           => (int)$b['branch_id'],
        'branch_name'         => $b['branch_name'] ?? '',
        'phase'               => $b['phase'],
        'check_in_date'       => $b['check_in_date'],
        'check_out_date'      => $b['check_out_date'],
        'open_to_share'       => (bool)$b['open_to_share'],
        'remarks'             => $b['remarks'] ?? '',
        'linked_user_ids'     => json_decode($b['linked_user_ids'] ?? '[]', true) ?: [],
        'admin_booked'        => (bool)$b['admin_booked'],
        'cancellation_status' => $b['cancellation_status'],
        'booked_at'           => $b['booked_at'],
        'payment_status'      => $b['payment_status'] ?? 'not_paid',
        'include_cleaning'    => (isset($b['cleaning_fee']) && $b['cleaning_fee'] !== null) ? ((float)$b['cleaning_fee'] > 0) : true,
    ];
}

function bookings_list() {
    $user = require_auth();
    $year = (int)($_GET['year'] ?? date('Y'));
    $db = get_db();

    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status,
               p.cleaning_fee AS cleaning_fee
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.year = ? AND b.cancellation_status != 'approved'
        ORDER BY b.booked_at DESC
    ");
    $stmt->execute([$year]);
    $all = $stmt->fetchAll();

    // Also get ALL bookings for current user (across years) for My Bookings sidebar
    $stmtMy = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status,
               p.cleaning_fee AS cleaning_fee
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.user_id = ? AND b.cancellation_status != 'approved'
        ORDER BY b.booked_at DESC
    ");
    $stmtMy->execute([$user['id']]);
    $myAll = $stmtMy->fetchAll();

    json_success([
        'bookings'    => array_map('format_booking', $all),
        'my_bookings' => array_map('format_booking', $myAll),
        'weeks'       => generate_weeks($year),
    ]);
}

function bookings_create() {
    $user = require_auth();
    $body = get_json_body();
    $db = get_db();

    $weekId       = $body['week_id'] ?? '';
    $year         = (int)($body['year'] ?? date('Y'));
    $phase        = $body['phase'] ?? '';
    $openToShare  = (bool)($body['open_to_share'] ?? false);
    $remarks      = trim($body['remarks'] ?? '');
    $linkedIds    = $body['linked_user_ids'] ?? [];
    $checkIn      = $body['check_in_date'] ?? null;
    $checkOut     = $body['check_out_date'] ?? null;

    if (!$weekId || !$phase) {
        json_error('week_id and phase required');
    }
    if (!in_array($phase, ['clan', 'priority', 'regular'])) {
        json_error('Invalid phase');
    }

    // Get phase config
    $stmt = $db->prepare("SELECT * FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $stmt->execute([$year]);
    $cfg = $stmt->fetch();

    $today = date('Y-m-d');
    $isAdmin = (bool)$user['is_admin'];

    // ---- Phase validation (admin can bypass timing) ----
    if (!$isAdmin && $cfg) {
        if ($phase === 'clan') {
            if ($today < $cfg['clan_start'] || $today > $cfg['clan_end']) {
                json_error('Clan booking phase is not currently open');
            }
        } elseif ($phase === 'priority') {
            if ($today < $cfg['priority_start'] || $today > $cfg['priority_end']) {
                json_error('Priority booking phase is not currently open');
            }
        } elseif ($phase === 'regular') {
            if ($today < $cfg['regular_start']) {
                json_error('Regular booking phase has not opened yet');
            }
        }
    }

    // ---- Booking rules ----
    $branchId = (int)$user['branch_id'];

    // Clan: max 1 per branch per year
    if ($phase === 'clan') {
        $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE year = ? AND branch_id = ? AND phase = 'clan' AND cancellation_status NOT IN ('approved') LIMIT 1");
        $stmt->execute([$year, $branchId]);
        if ($stmt->fetch()) json_error('Your branch already has a clan booking this year');
    }

    // Priority: max 1 per user per year
    if ($phase === 'priority') {
        $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE year = ? AND user_id = ? AND phase = 'priority' AND cancellation_status NOT IN ('approved') LIMIT 1");
        $stmt->execute([$year, $user['id']]);
        if ($stmt->fetch()) json_error('You already have a priority booking this year');
    }

    // Regular: week must be within 6 months, max 7 nights
    if ($phase === 'regular' && !$isAdmin) {
        $weeks = generate_weeks($year);
        $week = null;
        foreach ($weeks as $w) {
            if ($w['id'] === $weekId) { $week = $w; break; }
        }
        if ($week) {
            $weekStart = new DateTime($week['start']);
            $sixMonths = new DateTime();
            $sixMonths->modify('+6 months');
            if ($weekStart > $sixMonths) {
                json_error('This week is not yet open for regular booking (6-month rule)');
            }
        }
        if ($checkIn && $checkOut) {
            $ci = new DateTime($checkIn);
            $co = new DateTime($checkOut);
            $nights = (int)$ci->diff($co)->days;
            if ($nights > 7) json_error('Maximum 7 nights for regular bookings');
            if ($nights < 1) json_error('Check-out must be after check-in');
        }

        // Reject if the week is already occupied by another Fargny booking
        $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE week_id = ? AND cancellation_status NOT IN ('approved') LIMIT 1");
        $stmt->execute([$weekId]);
        if ($stmt->fetch()) json_error('This week is already booked');

        // Reject if the week overlaps with any Google Calendar event
        if ($week) {
            require_once __DIR__ . '/google-calendar.php';
            $gcalEvents = @gcal_get_events();
            if (is_array($gcalEvents)) {
                $ws = $week['start']; $we = $week['end'];
                foreach ($gcalEvents as $ev) {
                    $es = $ev['start_date'] ?? ''; $ee = $ev['end_date'] ?? $es;
                    if (!$es) continue;
                    // overlap: es <= we && ee >= ws
                    if ($es <= $we && $ee >= $ws) {
                        json_error('This week is blocked by the legacy calendar');
                    }
                }
            }
        }
    }

    // No double booking: same week, same user
    $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE week_id = ? AND user_id = ? AND cancellation_status NOT IN ('approved') LIMIT 1");
    $stmt->execute([$weekId, $user['id']]);
    if ($stmt->fetch()) json_error('You already have a booking for this week');

    // ---- Insert booking ----
    $stmt = $db->prepare("
        INSERT INTO fargny_bookings
            (week_id, year, user_id, branch_id, phase, check_in_date, check_out_date, open_to_share, remarks, linked_user_ids, admin_booked, admin_user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NULL)
    ");
    $stmt->execute([
        $weekId, $year, $user['id'], $branchId, $phase,
        $checkIn, $checkOut,
        $openToShare ? 1 : 0,
        $remarks,
        json_encode($linkedIds),
    ]);
    $bookingId = (int)$db->lastInsertId();

    // Create default payment record
    $db->prepare("INSERT INTO fargny_payments (booking_id) VALUES (?)")->execute([$bookingId]);

    // Send confirmation email
    try {
        require_once __DIR__ . '/email.php';
        $weeks = generate_weeks($year);
        $week = null;
        foreach ($weeks as $w) { if ($w['id'] === $weekId) { $week = $w; break; } }
        send_booking_confirmation($user, [
            'id' => $bookingId, 'week_id' => $weekId, 'phase' => $phase,
            'check_in_date' => $checkIn ?: ($week['start'] ?? ''), 'check_out_date' => $checkOut ?: ($week['end'] ?? ''),
        ]);
    } catch (Exception $e) {
        // Don't fail the booking if email fails
    }

    // Return the new booking
    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name, 'not_paid' AS payment_status
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    json_success(format_booking($stmt->fetch()), 201);
}

function bookings_cancel(string $idStr) {
    $user = require_auth();
    $id = (int)$idStr;
    $db = get_db();

    $stmt = $db->prepare("SELECT * FROM fargny_bookings WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);

    // Only the booking owner or admin can request cancellation
    if ((int)$booking['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
        json_error('Not authorized', 403);
    }

    // Set to pending cancellation
    $db->prepare("UPDATE fargny_bookings SET cancellation_status = 'pending' WHERE id = ?")->execute([$id]);

    // Notify admin
    try {
        require_once __DIR__ . '/email.php';
        send_cancellation_request($user, $booking);
    } catch (Exception $e) {}

    json_success(['cancellation_status' => 'pending']);
}

function bookings_calendar() {
    $user = require_auth();
    $year = (int)($_GET['year'] ?? date('Y'));
    $db = get_db();

    // Get phase config for reveal logic
    $stmt = $db->prepare("SELECT * FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $stmt->execute([$year]);
    $cfg = $stmt->fetch();

    $today = date('Y-m-d');
    $clanRevealed = $cfg ? ($today >= $cfg['clan_reveal']) : true;
    $priorityRevealed = $cfg ? ($today >= $cfg['priority_reveal']) : true;

    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status,
               p.cleaning_fee AS cleaning_fee
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.year = ? AND b.cancellation_status != 'approved'
    ");
    $stmt->execute([$year]);
    $all = $stmt->fetchAll();

    $visible = [];
    foreach ($all as $b) {
        // Always show user's own bookings
        if ((int)$b['user_id'] === (int)$user['id']) {
            $visible[] = format_booking($b);
            continue;
        }
        // Admin sees everything
        if ($user['is_admin']) {
            $visible[] = format_booking($b);
            continue;
        }
        // Regular phase always visible
        if ($b['phase'] === 'regular') {
            $visible[] = format_booking($b);
            continue;
        }
        // Clan: only after reveal
        if ($b['phase'] === 'clan' && $clanRevealed) {
            $visible[] = format_booking($b);
            continue;
        }
        // Priority: only after reveal
        if ($b['phase'] === 'priority' && $priorityRevealed) {
            $visible[] = format_booking($b);
            continue;
        }
    }

    json_success([
        'bookings'           => $visible,
        'weeks'              => generate_weeks($year),
        'clan_revealed'      => $clanRevealed,
        'priority_revealed'  => $priorityRevealed,
    ]);
}

function bookings_public_calendar() {
    // No auth required
    $year = (int)($_GET['year'] ?? date('Y'));
    $db = get_db();

    $stmt = $db->prepare("SELECT * FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $stmt->execute([$year]);
    $cfg = $stmt->fetch();

    $today = date('Y-m-d');
    $clanRevealed = $cfg ? ($today >= $cfg['clan_reveal']) : true;
    $priorityRevealed = $cfg ? ($today >= $cfg['priority_reveal']) : true;

    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        WHERE b.year = ? AND b.cancellation_status NOT IN ('approved')
    ");
    $stmt->execute([$year]);
    $all = $stmt->fetchAll();

    $visible = [];
    foreach ($all as $b) {
        if ($b['phase'] === 'regular') {
            $visible[] = format_booking(array_merge($b, ['payment_status' => 'not_paid']));
        } elseif ($b['phase'] === 'clan' && $clanRevealed) {
            $visible[] = format_booking(array_merge($b, ['payment_status' => 'not_paid']));
        } elseif ($b['phase'] === 'priority' && $priorityRevealed) {
            $visible[] = format_booking(array_merge($b, ['payment_status' => 'not_paid']));
        }
    }

    // Also include board events
    $events = $db->query("
        SELECT be.*, COUNT(bs.id) AS signup_count
        FROM fargny_board_events be
        LEFT JOIN fargny_board_signups bs ON bs.event_id = be.id
        GROUP BY be.id
        ORDER BY be.start_date
    ")->fetchAll();

    json_success([
        'bookings'     => $visible,
        'board_events' => array_map(function($e) {
            return [
                'id'           => (int)$e['id'],
                'name'         => $e['name'],
                'start_date'   => $e['start_date'],
                'end_date'     => $e['end_date'],
                'description'  => $e['description'],
                'signup_count' => (int)$e['signup_count'],
            ];
        }, $events),
        'weeks' => generate_weeks($year),
    ]);
}
