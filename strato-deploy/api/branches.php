<?php
// ============================================================
// Branches: list branches, members per branch, shareholders
// ============================================================

function handle_branches(string $action, string $method) {
    if ($method !== 'GET') json_error('GET required', 405);

    switch ($action) {
        case '':
        case 'list':
            branches_list();
            break;
        case 'shareholders':
            branches_shareholders();
            break;
        default:
            json_error('Unknown action', 404);
    }
}

function branches_list() {
    $db = get_db();

    $branches = $db->query("SELECT id, name, color FROM fargny_branches ORDER BY id")->fetchAll();

    // Get member counts per branch
    $members = $db->query("
        SELECT branch_id, COUNT(*) AS member_count
        FROM fargny_users
        GROUP BY branch_id
    ")->fetchAll();
    $memberMap = [];
    foreach ($members as $m) $memberMap[(int)$m['branch_id']] = (int)$m['member_count'];

    // Get registered users grouped by branch
    $users = $db->query("SELECT id, display_name, email, branch_id, is_admin FROM fargny_users ORDER BY display_name")->fetchAll();
    $usersByBranch = [];
    foreach ($users as $u) {
        $bid = (int)$u['branch_id'];
        if (!isset($usersByBranch[$bid])) $usersByBranch[$bid] = [];
        $usersByBranch[$bid][] = [
            'id'           => (int)$u['id'],
            'display_name' => $u['display_name'],
            'email'        => $u['email'],
            'is_admin'     => (bool)$u['is_admin'],
        ];
    }

    $result = array_map(function($br) use ($memberMap, $usersByBranch) {
        $id = (int)$br['id'];
        return [
            'id'           => $id,
            'name'         => $br['name'],
            'color'        => $br['color'],
            'member_count' => $memberMap[$id] ?? 0,
            'members'      => $usersByBranch[$id] ?? [],
        ];
    }, $branches);

    json_success($result);
}

function branches_shareholders() {
    // Return all shareholders with their registration status
    $db = get_db();
    $shareholders = $db->query("
        SELECT s.id, s.full_name, s.branch_id, s.user_id, b.name AS branch_name
        FROM fargny_shareholders s
        JOIN fargny_branches b ON b.id = s.branch_id
        ORDER BY s.full_name
    ")->fetchAll();

    $result = array_map(function($s) {
        return [
            'id'          => (int)$s['id'],
            'full_name'   => $s['full_name'],
            'branch_id'   => (int)$s['branch_id'],
            'branch_name' => $s['branch_name'],
            'registered'  => $s['user_id'] !== null,
        ];
    }, $shareholders);

    json_success($result);
}
