<?php
// ============================================================
// Auth: login, register, logout, me
// ============================================================

function handle_auth(string $action, string $method) {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') json_error('POST required', 405);
            auth_login();
            break;
        case 'register':
            if ($method !== 'POST') json_error('POST required', 405);
            auth_register();
            break;
        case 'logout':
            if ($method !== 'POST') json_error('POST required', 405);
            auth_logout();
            break;
        case 'me':
            if ($method !== 'GET') json_error('GET required', 405);
            auth_me();
            break;
        default:
            json_error('Unknown auth action', 404);
    }
}

function create_session(int $userId): string {
    $db = get_db();
    $token = bin2hex(random_bytes(32)); // 64-char hex
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $db->prepare("INSERT INTO fargny_sessions (user_id, token, expires_at) VALUES (?, ?, ?)")
       ->execute([$userId, $token, $expires]);
    // Clean up old sessions for this user (keep last 5)
    $db->prepare("
        DELETE FROM fargny_sessions
        WHERE user_id = ? AND id NOT IN (
            SELECT id FROM (SELECT id FROM fargny_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5) AS t
        )
    ")->execute([$userId, $userId]);
    return $token;
}

function format_user_response(array $user, string $token): array {
    return [
        'token' => $token,
        'user' => [
            'id'           => (int)$user['id'],
            'display_name' => $user['display_name'],
            'email'        => $user['email'],
            'branch_id'    => (int)$user['branch_id'],
            'is_admin'     => (bool)$user['is_admin'],
            'last_login'   => $user['last_login'],
        ],
    ];
}

function auth_login() {
    $body = get_json_body();
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!$email || !$password) {
        json_error('Email and password required');
    }

    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE email = ? LIMIT 1");
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_error('Invalid email or password', 401);
    }

    // Update last_login
    $db->prepare("UPDATE fargny_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    $user['last_login'] = date('Y-m-d H:i:s');

    $token = create_session((int)$user['id']);
    json_success(format_user_response($user, $token));
}

function auth_register() {
    $body = get_json_body();
    $shareholderId = (int)($body['shareholder_id'] ?? 0);
    $email = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';

    if (!$shareholderId || !$email || !$password) {
        json_error('Shareholder, email, and password required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Invalid email address');
    }
    if (strlen($password) < 3) {
        json_error('Password too short');
    }

    $db = get_db();

    // Check shareholder exists and is not yet linked
    $stmt = $db->prepare("SELECT * FROM fargny_shareholders WHERE id = ? LIMIT 1");
    $stmt->execute([$shareholderId]);
    $sh = $stmt->fetch();
    if (!$sh) json_error('Shareholder not found');
    if ($sh['user_id']) json_error('This person has already registered');

    // Check email not taken
    $stmt = $db->prepare("SELECT id FROM fargny_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) json_error('Email already in use');

    // Create user
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO fargny_users (display_name, email, password_hash, branch_id, is_admin) VALUES (?, ?, ?, ?, 0)")
       ->execute([$sh['full_name'], $email, $hash, $sh['branch_id']]);
    $userId = (int)$db->lastInsertId();

    // Link shareholder
    $db->prepare("UPDATE fargny_shareholders SET user_id = ? WHERE id = ?")->execute([$userId, $shareholderId]);

    // Update last_login
    $db->prepare("UPDATE fargny_users SET last_login = NOW() WHERE id = ?")->execute([$userId]);

    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $token = create_session($userId);
    json_success(format_user_response($user, $token), 201);
}

function auth_logout() {
    $token = get_bearer_token();
    if ($token) {
        $db = get_db();
        $db->prepare("DELETE FROM fargny_sessions WHERE token = ?")->execute([$token]);
    }
    json_success(null);
}

function auth_me() {
    $user = require_auth();
    json_success([
        'user' => [
            'id'           => (int)$user['id'],
            'display_name' => $user['display_name'],
            'email'        => $user['email'],
            'branch_id'    => (int)$user['branch_id'],
            'is_admin'     => (bool)$user['is_admin'],
            'last_login'   => $user['last_login'],
        ],
    ]);
}
