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
        case 'request-reset':
            if ($method !== 'POST') json_error('POST required', 405);
            auth_request_reset();
            break;
        case 'reset-password':
            if ($method !== 'POST') json_error('POST required', 405);
            auth_reset_password();
            break;
        default:
            json_error('Unknown auth action', 404);
    }
}

// Password-reset tokens live in their own table. Created on demand so no
// manual migration is needed on the shared host.
function ensure_reset_table() {
    get_db()->exec("
        CREATE TABLE IF NOT EXISTS `fargny_password_resets` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` INT UNSIGNED NOT NULL,
          `token_hash` CHAR(64) NOT NULL,
          `expires_at` DATETIME NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_token_hash` (`token_hash`),
          KEY `idx_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// Absolute URL of the app root (the folder containing index.html), derived
// from the current request so it works at /booking/ or any other sub-path.
function app_base_url(): string {
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'fargny.org';
    // SCRIPT_NAME is e.g. /booking/api/index.php -> app root is /booking
    $dir = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/api/index.php')));
    if ($dir === '/' || $dir === '.') $dir = '';
    return $scheme . '://' . $host . $dir . '/';
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
            'role'         => user_role($user),
            'connected_shareholder_id' => isset($user['connected_shareholder_id']) && $user['connected_shareholder_id'] !== null
                                          ? (int)$user['connected_shareholder_id'] : null,
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
    if (($body['role'] ?? '') === 'family_member') {
        auth_register_family_member($body);
        return;
    }

    $shareholderId = (int)($body['shareholder_id'] ?? 0);
    $email = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';

    if (!$shareholderId || !$email || !$password) {
        json_error('Shareholder, email, and password required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Invalid email address');
    }
    if (strlen($password) < 8) {
        json_error('Password must be at least 8 characters');
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

// Step 1: user asks for a reset link. Always reports success so the
// endpoint can't be used to discover which emails are registered.
function auth_request_reset() {
    $body  = get_json_body();
    $email = strtolower(trim($body['email'] ?? ''));

    $done = ['sent' => true];
    if (!$email) json_success($done);

    try {
        $db = get_db();
        ensure_reset_table();

        $stmt = $db->prepare("SELECT id, display_name, email FROM fargny_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) json_success($done);

        // Drop older tokens for this user, then issue a fresh one (1 hour).
        $db->prepare("DELETE FROM fargny_password_resets WHERE user_id = ? OR expires_at < NOW()")
           ->execute([$user['id']]);

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db->prepare("INSERT INTO fargny_password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)")
           ->execute([$user['id'], hash('sha256', $token), $expires]);

        require_once __DIR__ . '/email.php';
        send_password_reset($user, app_base_url() . '?reset=' . $token);
    } catch (Exception $e) {
        // Never leak internals — the user still sees the neutral message.
    }

    json_success($done);
}

// Step 2: user submits a new password together with the emailed token.
function auth_reset_password() {
    $body     = get_json_body();
    $token    = trim($body['token'] ?? '');
    $password = $body['password'] ?? '';

    if (!$token) json_error('Reset token required');
    if (strlen($password) < 8) json_error('Password must be at least 8 characters');

    $db = get_db();
    ensure_reset_table();

    $stmt = $db->prepare("
        SELECT r.id, r.user_id
        FROM fargny_password_resets r
        WHERE r.token_hash = ? AND r.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([hash('sha256', $token)]);
    $row = $stmt->fetch();
    if (!$row) json_error('This reset link is invalid or has expired', 400);

    $userId = (int)$row['user_id'];
    $db->prepare("UPDATE fargny_users SET password_hash = ? WHERE id = ?")
       ->execute([password_hash($password, PASSWORD_BCRYPT), $userId]);

    // Burn the token and sign out everywhere so a stolen link is useless.
    $db->prepare("DELETE FROM fargny_password_resets WHERE user_id = ?")->execute([$userId]);
    $db->prepare("DELETE FROM fargny_sessions WHERE user_id = ?")->execute([$userId]);

    json_success(['reset' => true]);
}

// Family members are not shareholders: they give their own name and pick
// the shareholder they belong to, inheriting that shareholder's branch.
function auth_register_family_member(array $body) {
    $name     = trim($body['display_name'] ?? '');
    $email    = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';
    $connId   = (int)($body['connected_shareholder_id'] ?? 0);

    if (!$name || !$email || !$password || !$connId) {
        json_error('Name, email, password and connected shareholder are required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email address');
    if (strlen($password) < 8) json_error('Password must be at least 8 characters');

    $db = get_db();
    ensure_role_columns();

    $stmt = $db->prepare("SELECT id, branch_id FROM fargny_shareholders WHERE id = ? LIMIT 1");
    $stmt->execute([$connId]);
    $sh = $stmt->fetch();
    if (!$sh) json_error('Shareholder not found');

    $stmt = $db->prepare("SELECT id FROM fargny_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) json_error('Email already in use');

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $db->prepare("
        INSERT INTO fargny_users
            (display_name, email, password_hash, branch_id, is_admin, role, connected_shareholder_id)
        VALUES (?, ?, ?, ?, 0, 'family_member', ?)
    ")->execute([$name, $email, $hash, (int)$sh['branch_id'], $connId]);
    $userId = (int)$db->lastInsertId();

    // Note: fargny_shareholders.user_id is deliberately NOT set — a family
    // member does not consume a shareholder slot.
    $db->prepare("UPDATE fargny_users SET last_login = NOW() WHERE id = ?")->execute([$userId]);

    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    json_success(format_user_response($user, create_session($userId)), 201);
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

    // Self-heal: if this user is linked to a shareholder whose branch_id
    // differs from fargny_users.branch_id, sync it. This fixes historical
    // accounts that were created against the old dummy seed.
    $db = get_db();
    $stmt = $db->prepare("SELECT branch_id FROM fargny_shareholders WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $sh = $stmt->fetch();
    if ($sh && (int)$sh['branch_id'] !== (int)$user['branch_id'] && !$user['is_admin']) {
        $db->prepare("UPDATE fargny_users SET branch_id = ? WHERE id = ?")
           ->execute([(int)$sh['branch_id'], (int)$user['id']]);
        $user['branch_id'] = (int)$sh['branch_id'];
    }

    json_success([
        'user' => [
            'id'           => (int)$user['id'],
            'display_name' => $user['display_name'],
            'email'        => $user['email'],
            'branch_id'    => (int)$user['branch_id'],
            'is_admin'     => (bool)$user['is_admin'],
            'role'         => user_role($user),
            'connected_shareholder_id' => isset($user['connected_shareholder_id']) && $user['connected_shareholder_id'] !== null
                                          ? (int)$user['connected_shareholder_id'] : null,
            'last_login'   => $user['last_login'],
        ],
    ]);
}
