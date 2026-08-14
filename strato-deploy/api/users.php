<?php
// ============================================================
// Users: admin-only user list, admin toggle and role management
// ============================================================

function handle_users(string $action, string $id, string $method) {
    // Route:
    //   GET  /api/users                       -> list
    //   POST /api/users/{id}/toggle-admin     -> toggle is_admin
    //   POST /api/users/{id}/role             -> set role
    if ($action === '' && $method === 'GET') {
        users_list();
        return;
    }
    if ($id === 'toggle-admin' && $method === 'POST') {
        users_toggle_admin((int)$action);
        return;
    }
    if ($id === 'role' && ($method === 'POST' || $method === 'PUT')) {
        users_set_role((int)$action);
        return;
    }
    json_error('Not found', 404);
}

function users_list() {
    require_admin();
    ensure_role_columns();
    $db = get_db();
    $users = $db->query("
        SELECT u.*, b.name AS branch_name,
               sh.full_name AS connected_shareholder_name
        FROM fargny_users u
        JOIN fargny_branches b ON b.id = u.branch_id
        LEFT JOIN fargny_shareholders sh ON sh.id = u.connected_shareholder_id
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
            'role'         => user_role($u),
            'connected_shareholder_id'   => isset($u['connected_shareholder_id']) && $u['connected_shareholder_id'] !== null
                                            ? (int)$u['connected_shareholder_id'] : null,
            'connected_shareholder_name' => $u['connected_shareholder_name'] ?? null,
            'last_login'   => $u['last_login'],
            'created_at'   => $u['created_at'],
        ];
    }, $users);

    json_success($result);
}

function users_toggle_admin(int $userId) {
    $me = require_admin();
    if (!$userId) json_error('user id required');
    ensure_role_columns();

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
    // Keep role in step: promoting grants 'admin', demoting falls back to
    // 'shareholder' unless the account is a family member.
    $db->prepare("
        UPDATE fargny_users
        SET is_admin = ?,
            role = CASE
                     WHEN ? = 1 THEN 'admin'
                     WHEN role = 'family_member' THEN 'family_member'
                     ELSE 'shareholder'
                   END
        WHERE id = ?
    ")->execute([$new, $new, $userId]);

    json_success(['id' => (int)$u['id'], 'is_admin' => (bool)$new]);
}

// Set a user's role outright, e.g. promoting a family member who has
// become a shareholder.
function users_set_role(int $userId) {
    $me = require_admin();
    if (!$userId) json_error('user id required');
    ensure_role_columns();

    $body = get_json_body();
    $role = $body['role'] ?? '';
    if (!in_array($role, ['admin', 'shareholder', 'family_member'], true)) {
        json_error('Invalid role');
    }
    if ($userId === (int)$me['id']) {
        json_error('You cannot change your own role');
    }

    $db = get_db();
    $stmt = $db->prepare("SELECT id FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) json_error('User not found', 404);

    // A user who is no longer a family member keeps no shareholder link.
    $isAdmin = $role === 'admin' ? 1 : 0;
    $db->prepare("
        UPDATE fargny_users
        SET role = ?,
            is_admin = ?,
            connected_shareholder_id = CASE WHEN ? = 'family_member' THEN connected_shareholder_id ELSE NULL END
        WHERE id = ?
    ")->execute([$role, $isAdmin, $role, $userId]);

    json_success(['id' => $userId, 'role' => $role, 'is_admin' => (bool)$isAdmin]);
}
