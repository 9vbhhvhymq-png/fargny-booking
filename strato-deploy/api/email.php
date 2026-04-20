<?php
// ============================================================
// Email: send booking confirmations and notifications
// ============================================================

function send_email(string $to, string $subject, string $body) {
    $from = env('MAIL_FROM', 'noreply@fargny.org');
    $headers = [
        "From: Fargny Booking <$from>",
        "Reply-To: $from",
        "Content-Type: text/html; charset=UTF-8",
        "X-Mailer: Fargny-Booking/1.0",
    ];
    @mail($to, $subject, $body, implode("\r\n", $headers));
}

function email_template(string $title, string $content): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:\'Segoe UI\',Arial,sans-serif;background:#F5F0EB;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:white;border-radius:12px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
    <h1 style="font-family:Georgia,serif;color:#2C1810;font-size:24px;margin:0 0 8px 0;">Fargny</h1>
    <h2 style="color:#8B7D6B;font-size:14px;font-weight:normal;margin:0 0 24px 0;">' . htmlspecialchars($title) . '</h2>
    ' . $content . '
    <hr style="border:none;border-top:1px solid #E0D8CF;margin:24px 0 16px 0;">
    <p style="font-size:12px;color:#8B7D6B;">This is an automated message from the Fargny Holiday House Booking System.</p>
    </div></body></html>';
}

function send_booking_confirmation(array $user, array $booking) {
    $email = $user['email'] ?? '';
    if (!$email) return;

    $phase = ucfirst($booking['phase'] ?? 'regular');
    $weekId = $booking['week_id'] ?? '';
    $checkIn = $booking['check_in_date'] ?? '';
    $checkOut = $booking['check_out_date'] ?? '';
    $adminBooked = !empty($booking['admin_booked']) ? ' (booked by admin)' : '';

    $content = '<p style="color:#2C1810;font-size:15px;">Dear ' . htmlspecialchars($user['display_name'] ?? $user['email']) . ',</p>
    <p style="color:#2C1810;font-size:15px;">Your booking has been confirmed!</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Week</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;font-weight:600;">' . htmlspecialchars($weekId) . '</td></tr>
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Phase</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;font-weight:600;">' . htmlspecialchars($phase . $adminBooked) . '</td></tr>
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Check-in</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;">' . htmlspecialchars($checkIn) . '</td></tr>
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Check-out</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;">' . htmlspecialchars($checkOut) . '</td></tr>
    </table>
    <p style="color:#8B7D6B;font-size:13px;">You will receive a payment request separately. Transfer the amount to penningmeester@fargny.org.</p>
    <p style="color:#8B7D6B;font-size:13px;">Questions? Contact Rogier: +31-6-57711402</p>';

    send_email($email, "Booking Confirmed: $weekId", email_template('Booking Confirmation', $content));
}

function send_cancellation_request(array $user, array $booking) {
    $weekId = $booking['week_id'] ?? '';
    $userName = $user['display_name'] ?? $user['email'] ?? '';

    // Send only to admins (users with is_admin = 1).
    // Falls back to ADMIN_EMAIL if no admin rows exist, so we never drop the alert.
    $recipients = [];
    try {
        $db = get_db();
        $rows = $db->query("SELECT email FROM fargny_users WHERE is_admin = 1 AND email IS NOT NULL AND email <> ''")->fetchAll();
        foreach ($rows as $r) { if (!empty($r['email'])) $recipients[] = $r['email']; }
    } catch (Exception $e) { /* fall through to env fallback */ }
    if (empty($recipients)) {
        $recipients = [env('ADMIN_EMAIL', 'admin@fargny.org')];
    }

    $content = '<p style="color:#2C1810;font-size:15px;">A cancellation has been requested:</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">User</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;font-weight:600;">' . htmlspecialchars($userName) . '</td></tr>
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Week</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;font-weight:600;">' . htmlspecialchars($weekId) . '</td></tr>
    <tr><td style="padding:8px 12px;color:#8B7D6B;font-size:13px;">Phase</td><td style="padding:8px 12px;color:#2C1810;font-size:13px;">' . htmlspecialchars($booking['phase'] ?? '') . '</td></tr>
    </table>
    <p style="color:#C4853B;font-size:13px;font-weight:600;">Please log in to approve or reject this cancellation.</p>';

    $subject = "Cancellation Request: $userName - $weekId";
    $body = email_template('Cancellation Request', $content);
    foreach ($recipients as $to) {
        send_email($to, $subject, $body);
    }
}

function send_cancellation_approved(array $user, array $booking) {
    $email = $user['email'] ?? '';
    if (!$email) return;
    $weekId = $booking['week_id'] ?? '';

    $content = '<p style="color:#2C1810;font-size:15px;">Dear ' . htmlspecialchars($user['display_name'] ?? '') . ',</p>
    <p style="color:#2C1810;font-size:15px;">Your cancellation request for week <strong>' . htmlspecialchars($weekId) . '</strong> has been approved.</p>
    <p style="color:#4A7C59;font-size:13px;">The week is now available for others to book.</p>';

    send_email($email, "Cancellation Approved: $weekId", email_template('Cancellation Approved', $content));
}
