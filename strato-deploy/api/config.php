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
        SELECT u.id, u.display_name, u.email, u.branch_id, u.is_admin, u.last_login, u.created_at
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

// ---- Week generation (mirrors frontend logic) ----
function generate_weeks(int $year): array {
    $weeks = [];
    $d = new DateTime("$year-01-01");
    // Find first Saturday
    while ((int)$d->format('w') !== 6) {
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

// ---- Seed admin on first run ----
function seed_admin_if_needed() {
    $db = get_db();
    $stmt = $db->prepare("SELECT id FROM fargny_users WHERE email = ? LIMIT 1");
    $stmt->execute(['admin@fargny.org']);
    if (!$stmt->fetch()) {
        $hash = password_hash('admin', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO fargny_users (display_name, email, password_hash, branch_id, is_admin) VALUES (?, ?, ?, ?, ?)")
           ->execute(['Admin', 'admin@fargny.org', $hash, 1, 1]);
    }
}

// Try to seed admin (silently ignore if tables don't exist yet)
try { seed_admin_if_needed(); } catch (Exception $e) {}
