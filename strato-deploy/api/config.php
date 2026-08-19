<?php
// ============================================================
// Fargny Booking System — Configuration & Helpers
// ============================================================

// Hide PHP warnings/notices from response so they never corrupt JSON output.
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
error_reporting(0);

// Buffer all output: anything echoed accidentally before json_response()
// is captured and discarded so the response stays valid JSON.
if (!ob_get_level()) ob_start();

// Convert uncaught exceptions and fatal errors into JSON error responses.
set_exception_handler(function ($e) {
    if (function_exists('ob_get_length') && ob_get_length()) { @ob_clean(); }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit;
});
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_clean(); }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'error' => 'Fatal: ' . $err['message']]);
    }
});

// Load .env from parent directory (not web-accessible)
function load_env() {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        $envFile = __DIR__ . '/.env';
    }
    if (!file_exists($envFile)) return;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // Remove surrounding quotes
        if ((strlen($val) > 1) && ($val[0] === '"' || $val[0] === "'") && $val[strlen($val)-1] === $val[0]) {
            $val = substr($val, 1, -1);
        }
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}
load_env();

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ---- Database connection (singleton) ----
function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'fargny');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

// ---- CORS ----
function cors_headers() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json; charset=utf-8");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ---- JSON helpers ----
function json_response($data, int $code = 200) {
    // Discard any stray output (PHP warnings, BOMs, accidental whitespace)
    // captured by the output buffer started in config.php so the body is
    // pure JSON.
    if (function_exists('ob_get_length') && ob_get_length()) { @ob_clean(); }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    } else {
        http_response_code($code);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = null, int $code = 200) {
    json_response(['success' => true, 'data' => $data], $code);
}

function json_error(string $message, int $code = 400) {
    json_response(['success' => false, 'error' => $message], $code);
}

function get_json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---- Auth helpers ----
function get_bearer_token(): ?string {
    // Try every location Apache/PHP might stash the Authorization header.
    // Strato strips HTTP_AUTHORIZATION on some request types; fall back to
    // REDIRECT_HTTP_AUTHORIZATION (set by mod_rewrite) and getallheaders().
    $candidates = [
        $_SERVER['HTTP_AUTHORIZATION']          ?? '',
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['Authorization']               ?? '',
    ];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $candidates[] = $value;
            }
        }
    }
    foreach ($candidates as $header) {
        if ($header && preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return $m[1];
        }
    }
    return null;
}

function get_auth_user(): ?array {
    $token = get_bearer_token();
    if (!$token) return null;

    $db = get_db();
    $stmt = $db->prepare("
        SELECT u.*
        FROM fargny_sessions s
        JOIN fargny_users u ON u.id = s.user_id
        WHERE s.token = ? AND s.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_auth(): array {
    $user = get_auth_user();
    if (!$user) {
        json_error('Authentication required', 401);
    }
    return $user;
}

function require_admin(): array {
    $user = require_auth();
    if (!$user['is_admin']) {
        json_error('Admin access required', 403);
    }
    return $user;
}

// How many months ahead regular bookings open. Mirrors
// REGULAR_MONTHS_AHEAD in index.html — keep the two in step.
if (!defined('REGULAR_MONTHS_AHEAD')) define('REGULAR_MONTHS_AHEAD', 3);

// Weeks run Friday to Friday: arrive Friday, depart the next Friday.
// 5 = Friday in PHP's 'w' (0 = Sunday).
if (!defined('WEEK_START_DOW')) define('WEEK_START_DOW', 5);

// Weeks used to start on Saturday. Bookings stored with a week id but no
// explicit dates would silently shift when the grid moved, so their real
// dates are pinned once, using the old definition, before the shift.
// Naturally idempotent: after the backfill no NULL-date rows remain.
function backfill_week_dates() {
    $db = get_db();
    $stmt = $db->query("
        SELECT id, week_id, year FROM fargny_bookings
        WHERE check_in_date IS NULL OR check_out_date IS NULL
    ");
    $rows = $stmt->fetchAll();
    if (!$rows) return;

    $legacyByYear = [];
    $upd = $db->prepare("UPDATE fargny_bookings SET check_in_date = ?, check_out_date = ? WHERE id = ?");
    foreach ($rows as $r) {
        $y = (int)$r['year'];
        if (!isset($legacyByYear[$y])) $legacyByYear[$y] = generate_weeks_saturday($y);
        foreach ($legacyByYear[$y] as $w) {
            if ($w['id'] === $r['week_id']) {
                // Whole week = 7 nights, departing the day after week end.
                $dep = new DateTime($w['end']);
                $dep->modify('+1 day');
                $upd->execute([$w['start'], $dep->format('Y-m-d'), $r['id']]);
                break;
            }
        }
    }
}

function generate_weeks_saturday(int $year): array {
    return build_weeks($year, 6);
}

// ---- Week generation (mirrors frontend logic) ----
function generate_weeks(int $year): array {
    return build_weeks($year, WEEK_START_DOW);
}

function build_weeks(int $year, int $startDow): array {
    $weeks = [];
    $d = new DateTime("$year-01-01");
    while ((int)$d->format('w') !== $startDow) {
        $d->modify('+1 day');
    }
    $weekNum = 1;
    while ((int)$d->format('Y') <= $year) {
        $start = clone $d;
        $end = clone $d;
        $end->modify('+6 days');
        if ((int)$start->format('Y') !== $year) break;
        $weeks[] = [
            'id' => $year . '-W' . str_pad($weekNum, 2, '0', STR_PAD_LEFT),
            'weekNum' => $weekNum,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'month' => (int)$start->format('n') - 1,
        ];
        $d->modify('+7 days');
        $weekNum++;
    }
    return $weeks;
}

// ---- Roles -------------------------------------------------------------
// 'admin' | 'shareholder' | 'family_member'. Applied automatically so a
// deploy works without anyone running database/add-family-member-role.sql.
function ensure_role_columns() {
    static $done = false;
    if ($done) return;
    $done = true;
    $db = get_db();
    try {
        $has = $db->query("SHOW COLUMNS FROM fargny_users LIKE 'role'")->fetch();
        if (!$has) {
            $db->exec("ALTER TABLE fargny_users
                       ADD COLUMN role ENUM('admin','shareholder','family_member')
                       NOT NULL DEFAULT 'shareholder' AFTER is_admin");
            $db->exec("UPDATE fargny_users SET role = 'admin' WHERE is_admin = 1");
        }
        $hasSh = $db->query("SHOW COLUMNS FROM fargny_users LIKE 'connected_shareholder_id'")->fetch();
        if (!$hasSh) {
            $db->exec("ALTER TABLE fargny_users
                       ADD COLUMN connected_shareholder_id INT UNSIGNED DEFAULT NULL AFTER role");
        }
    } catch (Exception $e) { /* tolerate: callers fall back to is_admin */ }
}

function user_role(array $user): string {
    if (!empty($user['is_admin'])) return 'admin';
    $r = $user['role'] ?? 'shareholder';
    return in_array($r, ['admin', 'shareholder', 'family_member'], true) ? $r : 'shareholder';
}

function is_family_member(array $user): bool {
    return user_role($user) === 'family_member';
}

// Family members may look, sign up for events and join shared bookings,
// but they cannot create or change bookings, events or payments.
function require_shareholder(string $message = 'Family members cannot create bookings'): array {
    $user = require_auth();
    if (is_family_member($user)) json_error($message, 403);
    return $user;
}

// ---- Seed admin on first run ----
function seed_admin_if_needed() {
    $db = get_db();
    ensure_role_columns();

    // 1. Ensure 'Moritz Fromageot' exists in fargny_shareholders (branch 9 = Bertrand).
    //    Rename the legacy 'Moritz (1992)' entry if present; insert otherwise.
    $shStmt = $db->prepare("SELECT id, user_id FROM fargny_shareholders WHERE full_name = 'Moritz Fromageot' LIMIT 1");
    $shStmt->execute();
    $shRow = $shStmt->fetch();

    if (!$shRow) {
        $oldStmt = $db->prepare("SELECT id FROM fargny_shareholders WHERE full_name = 'Moritz (1992)' LIMIT 1");
        $oldStmt->execute();
        $oldRow = $oldStmt->fetch();
        if ($oldRow) {
            $db->prepare("UPDATE fargny_shareholders SET full_name = 'Moritz Fromageot', branch_id = 9 WHERE id = ?")
               ->execute([$oldRow['id']]);
            $shId = (int)$oldRow['id'];
        } else {
            $db->prepare("INSERT INTO fargny_shareholders (full_name, branch_id) VALUES ('Moritz Fromageot', 9)")
               ->execute();
            $shId = (int)$db->lastInsertId();
        }
        $shUserId = null;
    } else {
        $shId     = (int)$shRow['id'];
        $shUserId = $shRow['user_id'] ? (int)$shRow['user_id'] : null;
        $db->prepare("UPDATE fargny_shareholders SET branch_id = 9 WHERE id = ? AND branch_id != 9")
           ->execute([$shId]);
    }

    // 2. Ensure admin user exists as moritz@fromageot.eu (also handles old admin@fargny.org seed).
    $stmt = $db->prepare("SELECT id, email FROM fargny_users WHERE email IN ('moritz@fromageot.eu','admin@fargny.org') LIMIT 1");
    $stmt->execute();
    $existing = $stmt->fetch();

    if (!$existing) {
        $hash = password_hash('admin', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO fargny_users (display_name, email, password_hash, branch_id, is_admin) VALUES (?, ?, ?, ?, ?)")
           ->execute(['Moritz Fromageot', 'moritz@fromageot.eu', $hash, 9, 1]);
        $userId = (int)$db->lastInsertId();
    } else {
        $userId = (int)$existing['id'];
        // Migrate old admin@fargny.org identity and always ensure is_admin=1
        $db->prepare("
            UPDATE fargny_users
            SET display_name = 'Moritz Fromageot',
                email        = 'moritz@fromageot.eu',
                branch_id    = 9,
                is_admin     = 1
            WHERE id = ?
              AND (email = 'admin@fargny.org' OR is_admin != 1 OR branch_id != 9)
        ")->execute([$userId]);
    }

    // 3. Link the shareholder row to the admin user if not already done.
    if ($shUserId !== $userId) {
        $db->prepare("UPDATE fargny_shareholders SET user_id = ? WHERE id = ?")
           ->execute([$userId, $shId]);
    }
}

// Try to seed admin (silently ignore if tables don't exist yet)
try { seed_admin_if_needed(); } catch (Exception $e) {}
try { backfill_week_dates(); } catch (Exception $e) {}
