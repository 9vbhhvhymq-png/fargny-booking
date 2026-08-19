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
            if ($id === 'join') {
                if ($method === 'POST')        { bookings_join($action); }
                elseif ($method === 'DELETE')  { bookings_leave($action); }
                else json_error('Method not allowed', 405);
            }
            elseif ($method === 'DELETE') { bookings_cancel($action); }
            elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'POST') { bookings_update($action); }
            else json_error('Method not allowed', 405);
    }
}

// ---- Occupancy helpers -------------------------------------------------
// A stay occupies NIGHTS, not days. Departure day and arrival day are the
// same changeover day: the guest who leaves on the 13th frees that date for
// the guest arriving on the 13th. Every range below is therefore expressed
// as [start, endExclusive) where endExclusive is the departure date.

function date_plus_days(string $d, int $n): string {
    $dt = new DateTime($d);
    $dt->modify(($n >= 0 ? '+' : '-') . abs($n) . ' days');
    return $dt->format('Y-m-d');
}

// Effective night range of a stored booking. A booking without explicit
// dates occupies its whole week (7 nights), so it departs the day after
// week end.
function booking_night_range(array $b, array &$weeksByYear): ?array {
    $start = $b['check_in_date'] ?? null;
    $end   = $b['check_out_date'] ?? null;
    if (!$start || !$end) {
        $y = (int)($b['year'] ?? 0);
        if (!isset($weeksByYear[$y])) $weeksByYear[$y] = generate_weeks($y);
        foreach ($weeksByYear[$y] as $w) {
            if ($w['id'] === ($b['week_id'] ?? '')) {
                if (!$start) $start = $w['start'];
                if (!$end)   $end   = date_plus_days($w['end'], 1);
                break;
            }
        }
    }
    return ($start && $end) ? [$start, $end] : null;
}

// Google Calendar: multi-day entries name the departure date (family
// convention), single-day entries occupy that one day.
function gcal_night_range(array $ev): ?array {
    $s = $ev['start_date'] ?? '';
    if (!$s) return null;
    $e = $ev['end_date'] ?? $s;
    $endExcl = ($e > $s) ? $e : date_plus_days($s, 1);
    return [$s, $endExcl];
}

// Which week a date belongs to. A late-December date can fall in a week
// that belongs to the following year's grid, so both are checked.
function week_id_for_date(string $date): ?array {
    $y = (int)substr($date, 0, 4);
    foreach ([$y, $y - 1, $y + 1] as $cand) {
        foreach (generate_weeks($cand) as $w) {
            if ($date >= $w['start'] && $date <= $w['end']) {
                return ['week_id' => $w['id'], 'year' => $cand];
            }
        }
    }
    return null;
}

// Half-open interval overlap: touching at the changeover date is allowed.
function ranges_overlap(string $aStart, string $aEndExcl, string $bStart, string $bEndExcl): bool {
    return $aStart < $bEndExcl && $bStart < $aEndExcl;
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

    // Blind booking: before the reveal date, other users' clan/priority
    // bookings must not expose who booked. The week still shows as taken
    // (so blocking stays consistent with the calendar) but identity is
    // stripped. Own bookings and admins always see full details. For clan
    // bookings the requester's own branch stays visible so the "your branch
    // already booked" warning keeps working.
    $cfgStmt = $db->prepare("SELECT * FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $cfgStmt->execute([$year]);
    $cfg = $cfgStmt->fetch();
    $today = date('Y-m-d');
    $clanRevealed     = $cfg ? ($today >= $cfg['clan_reveal']) : true;
    $priorityRevealed = $cfg ? ($today >= $cfg['priority_reveal']) : true;

    $anonymize = function (array $b) use ($user, $clanRevealed, $priorityRevealed): array {
        $fmt = format_booking($b);
        if ($user['is_admin'] || (int)$b['user_id'] === (int)$user['id']) return $fmt;
        $hidden = ($b['phase'] === 'clan' && !$clanRevealed)
               || ($b['phase'] === 'priority' && !$priorityRevealed);
        if (!$hidden) return $fmt;
        $fmt['user_id']         = 0;
        $fmt['user_name']       = '';
        $fmt['user_email']      = '';
        $fmt['remarks']         = '';
        $fmt['linked_user_ids'] = [];
        $fmt['is_hidden']       = true;
        $ownBranchClan = ($b['phase'] === 'clan' && (int)$b['branch_id'] === (int)$user['branch_id']);
        if (!$ownBranchClan) {
            $fmt['branch_id']   = 0;
            $fmt['branch_name'] = '';
        }
        return $fmt;
    };

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
        'bookings'    => array_map($anonymize, $all),
        'my_bookings' => array_map('format_booking', $myAll),
        'weeks'       => generate_weeks($year),
    ]);
}

function bookings_create() {
    $user = require_shareholder('Family members cannot create bookings');
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
        }
        // Regular booking is open all year round; the rolling horizon below
        // is the only restriction, so there is no phase window to check.
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

    // Clan/Priority book a whole week. The house can only be used once per
    // week, so a week already taken by ANY booking, a Google Calendar event,
    // or a special event is not bookable — even during the blind phases.
    if (($phase === 'clan' || $phase === 'priority') && !$isAdmin) {
        $weeks = generate_weeks($year);
        $week = null;
        foreach ($weeks as $w) { if ($w['id'] === $weekId) { $week = $w; break; } }
        if ($week) {
            // Whole week = 7 nights, departing the day after week end.
            $rs = $week['start']; $re = date_plus_days($week['end'], 1);

            $s = $db->prepare("
                SELECT week_id, year, check_in_date, check_out_date
                FROM fargny_bookings
                WHERE cancellation_status NOT IN ('approved')
            ");
            $s->execute();
            $wby = [];
            foreach ($s->fetchAll() as $ex) {
                $r = booking_night_range($ex, $wby);
                if ($r && ranges_overlap($r[0], $r[1], $rs, $re)) json_error('This week is already booked');
            }

            require_once __DIR__ . '/google-calendar.php';
            $gc = @gcal_get_events();
            if (is_array($gc)) {
                foreach ($gc as $ev) {
                    $r = gcal_night_range($ev);
                    if ($r && ranges_overlap($r[0], $r[1], $rs, $re)) json_error('These dates are already booked via the existing calendar');
                }
            }

            $evStmt = $db->prepare("SELECT id FROM fargny_board_events WHERE start_date < ? AND DATE_ADD(end_date, INTERVAL 1 DAY) > ? LIMIT 1");
            $evStmt->execute([$re, $rs]);
            if ($evStmt->fetch()) json_error('These dates are reserved for a special event — join the event instead');
        }
    }

    // Regular: arrival must fall inside the booking window, max 7 nights.
    if ($phase === 'regular' && !$isAdmin) {
        $weeks = generate_weeks($year);
        $week = null;
        foreach ($weeks as $w) {
            if ($w['id'] === $weekId) { $week = $w; break; }
        }
        $horizon = new DateTime();
        $horizon->modify('+' . REGULAR_MONTHS_AHEAD . ' months');

        // Check the date actually being reserved: with custom dates that is
        // the chosen arrival, otherwise the start of the week.
        $arrival = $checkIn ?: ($week['start'] ?? null);
        if ($arrival && new DateTime($arrival) > $horizon) {
            json_error('These dates are not yet open for regular booking — bookings open '
                       . REGULAR_MONTHS_AHEAD . ' months ahead');
        }
        if ($checkIn && $checkOut) {
            $ci = new DateTime($checkIn);
            $co = new DateTime($checkOut);
            $nights = (int)$ci->diff($co)->days;
            if ($nights > 7) json_error('Maximum 7 nights for regular bookings');
            if ($nights < 1) json_error('Check-out must be after check-in');
        }

        // Nights we are about to reserve, as [start, departure). Without
        // custom dates the booking takes the whole week and departs the day
        // after week end.
        $rangeStart = $checkIn ?: ($week['start'] ?? null);
        $rangeEnd   = $checkOut ?: (isset($week['end']) ? date_plus_days($week['end'], 1) : null);

        // Reject if the chosen nights overlap another Fargny booking.
        // Bookings stored without explicit dates occupy their full week, so
        // resolve effective dates from the week id in PHP — a NULL-date
        // fallback inside the SQL would match every booking.
        if ($rangeStart && $rangeEnd) {
            $stmt = $db->prepare("
                SELECT week_id, year, check_in_date, check_out_date
                FROM fargny_bookings
                WHERE cancellation_status NOT IN ('approved')
            ");
            $stmt->execute();
            $weeksByYear = [];
            foreach ($stmt->fetchAll() as $ex) {
                $r = booking_night_range($ex, $weeksByYear);
                if (!$r) continue;
                if (ranges_overlap($r[0], $r[1], $rangeStart, $rangeEnd)) {
                    json_error('These dates overlap with an existing booking');
                }
            }
        } else {
            // Fallback: same-week-id check
            $stmt = $db->prepare("SELECT id FROM fargny_bookings WHERE week_id = ? AND cancellation_status NOT IN ('approved') LIMIT 1");
            $stmt->execute([$weekId]);
            if ($stmt->fetch()) json_error('This week is already booked');
        }

        // Reject only if the chosen nights overlap a Google Calendar event —
        // arriving on the day a previous guest departs is fine.
        if ($rangeStart && $rangeEnd) {
            require_once __DIR__ . '/google-calendar.php';
            $gcalEvents = @gcal_get_events();
            if (is_array($gcalEvents)) {
                foreach ($gcalEvents as $ev) {
                    $r = gcal_night_range($ev);
                    if (!$r) continue;
                    if (ranges_overlap($r[0], $r[1], $rangeStart, $rangeEnd)) {
                        json_error('These dates are already booked via the existing calendar');
                    }
                }
            }
        }

        // Reject if the chosen nights overlap a special event — those dates
        // are reserved and members should join the event instead of booking.
        // An event occupies its days, so it "departs" the day after end_date.
        if ($rangeStart && $rangeEnd) {
            $evStmt = $db->prepare("
                SELECT id FROM fargny_board_events
                WHERE start_date < ? AND DATE_ADD(end_date, INTERVAL 1 DAY) > ?
                LIMIT 1
            ");
            $evStmt->execute([$rangeEnd, $rangeStart]);
            if ($evStmt->fetch()) {
                json_error('These dates are reserved for a special event — join the event instead');
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
    $user = require_shareholder('Family members cannot cancel bookings');
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

// Update an existing booking's editable fields (open_to_share, remarks).
// Only the owner or an admin may edit. Dates/week/phase are intentionally
// NOT editable here — those go through cancel + re-book.
function bookings_update(string $idStr) {
    $user = require_shareholder('Family members cannot change bookings');
    $id = (int)$idStr;
    $body = get_json_body();
    $db = get_db();

    $stmt = $db->prepare("SELECT * FROM fargny_bookings WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);

    if ((int)$booking['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
        json_error('Not authorized', 403);
    }

    $fields = [];
    $params = [];
    // What an admin changed, so the member can be told exactly what moved.
    $changes = [];
    $noteYes = function($v){ return $v ? 'yes' : 'no'; };
    if (array_key_exists('open_to_share', $body)) {
        $new = $body['open_to_share'] ? 1 : 0;
        if ((int)$booking['open_to_share'] !== $new) {
            $changes[] = ['label' => 'Open to share', 'from' => $noteYes($booking['open_to_share']), 'to' => $noteYes($new)];
        }
        $fields[] = 'open_to_share = ?';
        $params[] = $new;
    }
    if (array_key_exists('remarks', $body)) {
        $new = trim((string)$body['remarks']);
        if ((string)($booking['remarks'] ?? '') !== $new) {
            $changes[] = ['label' => 'Remark', 'from' => (string)($booking['remarks'] ?? '') ?: '—', 'to' => $new ?: '—'];
        }
        $fields[] = 'remarks = ?';
        $params[] = $new;
    }
    if (array_key_exists('linked_user_ids', $body)) {
        $fields[] = 'linked_user_ids = ?';
        $params[] = json_encode($body['linked_user_ids'] ?: []);
    }

    // Admins may also move a booking to different dates. The week it is
    // filed under follows the new arrival date so it appears in the right
    // row, and the new nights must not collide with another booking.
    if ($user['is_admin'] && (array_key_exists('check_in_date', $body) || array_key_exists('check_out_date', $body))) {
        $newIn  = $body['check_in_date']  ?? $booking['check_in_date'];
        $newOut = $body['check_out_date'] ?? $booking['check_out_date'];
        if (!$newIn || !$newOut) json_error('Both arrival and departure dates are required');
        if ($newOut <= $newIn) json_error('Departure must be after arrival');

        $stmt = $db->prepare("
            SELECT id, week_id, year, check_in_date, check_out_date
            FROM fargny_bookings
            WHERE id <> ? AND cancellation_status NOT IN ('approved')
        ");
        $stmt->execute([$id]);
        $weeksByYear = [];
        foreach ($stmt->fetchAll() as $ex) {
            $r = booking_night_range($ex, $weeksByYear);
            if ($r && ranges_overlap($r[0], $r[1], $newIn, $newOut)) {
                json_error('These dates overlap with an existing booking');
            }
        }

        if ((string)$booking['check_in_date'] !== (string)$newIn) {
            $changes[] = ['label' => 'Arrival', 'from' => (string)$booking['check_in_date'] ?: '—', 'to' => (string)$newIn];
        }
        if ((string)$booking['check_out_date'] !== (string)$newOut) {
            $changes[] = ['label' => 'Departure', 'from' => (string)$booking['check_out_date'] ?: '—', 'to' => (string)$newOut];
        }
        $fields[] = 'check_in_date = ?';  $params[] = $newIn;
        $fields[] = 'check_out_date = ?'; $params[] = $newOut;

        $wk = week_id_for_date($newIn);
        if ($wk) {
            $fields[] = 'week_id = ?'; $params[] = $wk['week_id'];
            $fields[] = 'year = ?';    $params[] = $wk['year'];
        }
    }

    // Admins can also clear a pending cancellation or cancel outright.
    if ($user['is_admin'] && array_key_exists('cancellation_status', $body)) {
        $cs = $body['cancellation_status'];
        if (!in_array($cs, ['none', 'pending', 'approved', 'rejected'], true)) {
            json_error('Invalid cancellation status');
        }
        $fields[] = 'cancellation_status = ?'; $params[] = $cs;
    }

    if (!$fields) json_error('Nothing to update');

    $params[] = $id;
    $db->prepare("UPDATE fargny_bookings SET " . implode(', ', $fields) . " WHERE id = ?")
       ->execute($params);

    // Tell the member what an admin changed, and why. Never mail someone
    // about their own edit, and never for a no-op.
    if ($user['is_admin'] && $changes && (int)$booking['user_id'] !== (int)$user['id']) {
        try {
            $ownerStmt = $db->prepare("SELECT display_name, email FROM fargny_users WHERE id = ? LIMIT 1");
            $ownerStmt->execute([(int)$booking['user_id']]);
            $owner = $ownerStmt->fetch();
            if ($owner) {
                require_once __DIR__ . '/email.php';
                send_booking_changed($owner, $changes, (string)($body['admin_note'] ?? ''));
            }
        } catch (Exception $e) {}
    }

    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name,
               COALESCE(p.status, 'not_paid') AS payment_status,
               p.cleaning_fee AS cleaning_fee
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.id = ?
    ");
    $stmt->execute([$id]);
    json_success(format_booking($stmt->fetch()));
}

// Join a booking the owner marked "open to share". Open to every role —
// this is how family members take part without creating bookings.
function bookings_join(string $idStr) {
    $user = require_auth();
    $id = (int)$idStr;
    $db = get_db();

    $stmt = $db->prepare("SELECT * FROM fargny_bookings WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);
    if (!$booking['open_to_share']) json_error('This booking is not open to share', 403);
    if ((int)$booking['user_id'] === (int)$user['id']) json_error('This is your own booking');

    $linked = json_decode($booking['linked_user_ids'] ?? '[]', true) ?: [];
    $linked = array_values(array_unique(array_map('intval', $linked)));
    if (in_array((int)$user['id'], $linked, true)) json_error('You already joined this booking');

    $linked[] = (int)$user['id'];
    $db->prepare("UPDATE fargny_bookings SET linked_user_ids = ? WHERE id = ?")
       ->execute([json_encode($linked), $id]);

    json_success(['linked_user_ids' => $linked], 201);
}

function bookings_leave(string $idStr) {
    $user = require_auth();
    $id = (int)$idStr;
    $db = get_db();

    $stmt = $db->prepare("SELECT * FROM fargny_bookings WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);

    $linked = json_decode($booking['linked_user_ids'] ?? '[]', true) ?: [];
    $linked = array_values(array_filter(array_map('intval', $linked),
        fn($uid) => $uid !== (int)$user['id']));

    $db->prepare("UPDATE fargny_bookings SET linked_user_ids = ? WHERE id = ?")
       ->execute([json_encode($linked), $id]);

    json_success(['linked_user_ids' => $linked]);
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
        // Always show user's own bookings in full
        if ((int)$b['user_id'] === (int)$user['id']) {
            $visible[] = format_booking($b);
            continue;
        }
        // Admin sees everything unredacted
        if ($user['is_admin']) {
            $visible[] = format_booking($b);
            continue;
        }
        // Regular phase always visible
        if ($b['phase'] === 'regular') {
            $visible[] = format_booking($b);
            continue;
        }
        // Clan: fully visible after reveal; anonymised before reveal so the
        // calendar and booking tab agree that the week is taken.
        if ($b['phase'] === 'clan') {
            if ($clanRevealed) {
                $visible[] = format_booking($b);
            } else {
                $anon = format_booking($b);
                $anon['user_name']   = '';
                $anon['user_email']  = '';
                $anon['branch_id']   = 0;
                $anon['branch_name'] = '';
                $anon['is_hidden']   = true;
                $visible[] = $anon;
            }
            continue;
        }
        // Priority: same reveal logic
        if ($b['phase'] === 'priority') {
            if ($priorityRevealed) {
                $visible[] = format_booking($b);
            } else {
                $anon = format_booking($b);
                $anon['user_name']   = '';
                $anon['user_email']  = '';
                $anon['branch_id']   = 0;
                $anon['branch_name'] = '';
                $anon['is_hidden']   = true;
                $visible[] = $anon;
            }
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
