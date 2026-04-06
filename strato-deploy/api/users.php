<?php
// ============================================================
// Users: admin-only user list
// ============================================================

function handle_users(string $action, string $method) {
    if ($method !== 'GET') json_error('GET required', 405);
    $user = require_admin();

    $db = get_db();
    $users = $db->query("
        SELECT u.id, u.display_name, u.email, u.branch_id, u.is_admin, u.last_login, u.created_at,
               b.name AS branch_name
        FROM fargny_users u
        JOIN fargny_branches b ON b.id = u.branch_id
        ORDER BY u.display_name
    ")->fetchAll();

    $result = array_map(function($u) {
        return [
            'id'           => (int)$u['id'],
            'display_name' => $u['display_name'],
            'email'        => $u['email'],
            'branch_id'    => (int)$u['branch_id'],
            'branch_name'  => $u['branch_name'],
            'is_admin'     => (bool)$u['is_admin'],
            'last_login'   => $u['last_login'],
            'created_at'   => $u['created_at'],
        ];
    }, $users);

    json_success($result);
}
