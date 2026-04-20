<?php
// ============================================================
// Users: admin-only user list + admin role toggle
// ============================================================

function handle_users(string $action, string $id, string $method) {
    // Route:
    //   GET  /api/users                       -> list
    //   POST /api/users/{id}/toggle-admin     -> toggle is_admin
    if ($action === '' && $method === 'GET') {
        users_list();
        return;
    }
    if ($id === 'toggle-admin' && $method === 'POST') {
        users_toggle_admin((int)$action);
        return;
    }
    json_error('Not found', 404);
}

function users_list() {
    require_admin();
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

function users_toggle_admin(int $userId) {
    $me = require_admin();
    if (!$userId) json_error('user id required');

    $db = get_db();
    $stmt = $db->prepare("SELECT id, is_admin FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) json_error('User not found', 404);

    // Don't let an admin remove their own admin rights (avoid lockout)
    if ((int)$u['id'] === (int)$me['id']) {
        json_error('You cannot change your own admin status');
    }

    $new = (int)$u['is_admin'] ? 0 : 1;
    $db->prepare("UPDATE fargny_users SET is_admin = ? WHERE id = ?")->execute([$new, $userId]);
    json_success(['id' => (int)$u['id'], 'is_admin' => (bool)$new]);
}
