<?php
// ============================================================
// Payments: get/save guest grid, auto-calculate fees
// ============================================================

function handle_payments(string $bookingId, string $method) {
    if (!$bookingId) json_error('booking_id required');

    if ($method === 'GET') {
        payments_get((int)$bookingId);
    } elseif ($method === 'POST') {
        payments_save((int)$bookingId);
    } else {
        json_error('Method not allowed', 405);
    }
}

function payments_get(int $bookingId) {
    $user = require_auth();
    $db = get_db();

    $stmt = $db->prepare("
        SELECT p.*, b.user_id, b.week_id, b.check_in_date, b.check_out_date
        FROM fargny_payments p
        JOIN fargny_bookings b ON b.id = p.booking_id
        WHERE p.booking_id = ?
        LIMIT 1
    ");
    $stmt->execute([$bookingId]);
    $payment = $stmt->fetch();

    if (!$payment) {
        // No payment record yet, check booking exists
        $stmt = $db->prepare("SELECT id, user_id, week_id, check_in_date, check_out_date FROM fargny_bookings WHERE id = ? LIMIT 1");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();
        if (!$booking) json_error('Booking not found', 404);

        // Only owner or admin
        if ((int)$booking['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
            json_error('Not authorized', 403);
        }

        json_success([
            'booking_id'  => $bookingId,
            'guest_data'  => null,
            'house_fee'   => 0,
            'person_fee'  => 0,
            'cleaning_fee'=> 70,
            'total'       => 0,
            'status'      => 'not_paid',
        ]);
        return;
    }

    // Only owner or admin
    if ((int)$payment['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
        json_error('Not authorized', 403);
    }

    json_success([
        'booking_id'  => $bookingId,
        'guest_data'  => json_decode($payment['guest_data'] ?? 'null', true),
        'house_fee'   => (float)$payment['house_fee'],
        'person_fee'  => (float)$payment['person_fee'],
        'cleaning_fee'=> (float)$payment['cleaning_fee'],
        'total'       => (float)$payment['total'],
        'status'      => $payment['status'],
    ]);
}

function payments_save(int $bookingId) {
    $user = require_auth();
    $body = get_json_body();
    $db = get_db();

    // Check booking exists and user is owner or admin
    $stmt = $db->prepare("SELECT * FROM fargny_bookings WHERE id = ? LIMIT 1");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    if (!$booking) json_error('Booking not found', 404);
    if ((int)$booking['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
        json_error('Not authorized', 403);
    }

    $guestData = $body['guest_data'] ?? null;
    // include_cleaning: default true if not provided
    $includeCleaning = array_key_exists('include_cleaning', $body) ? (bool)$body['include_cleaning'] : true;

    // Calculate fees from guest data
    // guest_data is an array of nights, each night has: { child04: N, child59: N, adult: N }
    $houseFee = 0;
    $personFee = 0;
    $cleaningFee = $includeCleaning ? 70.0 : 0.0;

    if (is_array($guestData)) {
        foreach ($guestData as $night) {
            $c04 = (int)($night['child04'] ?? 0);
            $c59 = (int)($night['child59'] ?? 0);
            $adult = (int)($night['adult'] ?? 0);
            $totalPeople = $c04 + $c59 + $adult;

            if ($totalPeople > 0) {
                $houseFee += 50.0; // €50 per night with ≥1 person
            }
            $personFee += ($c04 * 0) + ($c59 * 5) + ($adult * 10);
        }
    }

    $total = $houseFee + $personFee + $cleaningFee;

    // Upsert
    $stmt = $db->prepare("SELECT id FROM fargny_payments WHERE booking_id = ? LIMIT 1");
    $stmt->execute([$bookingId]);
    if ($stmt->fetch()) {
        $db->prepare("
            UPDATE fargny_payments
            SET guest_data = ?, house_fee = ?, person_fee = ?, cleaning_fee = ?, total = ?
            WHERE booking_id = ?
        ")->execute([json_encode($guestData), $houseFee, $personFee, $cleaningFee, $total, $bookingId]);
    } else {
        $db->prepare("
            INSERT INTO fargny_payments (booking_id, guest_data, house_fee, person_fee, cleaning_fee, total)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$bookingId, json_encode($guestData), $houseFee, $personFee, $cleaningFee, $total]);
    }

    json_success([
        'house_fee'   => $houseFee,
        'person_fee'  => $personFee,
        'cleaning_fee'=> $cleaningFee,
        'total'       => $total,
    ]);
}
