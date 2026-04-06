<?php
// ============================================================
// Stats: bookings per user/branch, occupancy, timeline
// ============================================================

function handle_stats(string $method) {
    if ($method !== 'GET') json_error('GET required', 405);
    $admin = require_admin();

    $year = (int)($_GET['year'] ?? date('Y'));
    $db = get_db();
    $weeks = generate_weeks($year);
    $totalWeeks = count($weeks);

    // Confirmed bookings for this year
    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, u.last_login, br.name AS branch_name
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        WHERE b.year = ? AND b.cancellation_status NOT IN ('approved', 'pending')
    ");
    $stmt->execute([$year]);
    $bookings = $stmt->fetchAll();

    // Bookings per user
    $perUser = [];
    foreach ($bookings as $b) {
        $name = $b['display_name'];
        if (!isset($perUser[$name])) {
            $perUser[$name] = [
                'name' => $name,
                'branch_name' => $b['branch_name'],
                'branch_id' => (int)$b['branch_id'],
                'clan' => 0, 'priority' => 0, 'regular' => 0, 'total' => 0,
                'last_login' => $b['last_login'],
            ];
        }
        $phase = $b['phase'];
        if (isset($perUser[$name][$phase])) $perUser[$name][$phase]++;
        $perUser[$name]['total']++;
    }

    // Bookings per branch
    $perBranch = [];
    $branches = $db->query("SELECT id, name, color FROM fargny_branches ORDER BY id")->fetchAll();
    foreach ($branches as $br) {
        $perBranch[$br['name']] = ['name' => $br['name'], 'color' => $br['color'], 'count' => 0];
    }
    foreach ($bookings as $b) {
        $bn = $b['branch_name'];
        if (isset($perBranch[$bn])) $perBranch[$bn]['count']++;
    }

    // Occupancy
    $bookedWeekIds = [];
    foreach ($bookings as $b) $bookedWeekIds[$b['week_id']] = true;
    $bookedCount = count($bookedWeekIds);
    $occupancyPct = $totalWeeks > 0 ? round($bookedCount / $totalWeeks * 100) : 0;

    // Timeline: bookings grouped by creation month
    $timeline = array_fill(0, 12, 0);
    foreach ($bookings as $b) {
        if ($b['booked_at']) {
            $month = (int)date('n', strtotime($b['booked_at'])) - 1;
            $timeline[$month]++;
        }
    }

    json_success([
        'per_user'    => array_values($perUser),
        'per_branch'  => array_values($perBranch),
        'occupancy'   => [
            'booked_weeks'   => $bookedCount,
            'total_weeks'    => $totalWeeks,
            'available_weeks'=> $totalWeeks - $bookedCount,
            'percentage'     => $occupancyPct,
        ],
        'timeline' => $timeline,
    ]);
}
