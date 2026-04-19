<?php
// Standalone diagnostic — NOT routed through .htaccess.
// Reports PHP version, .env visibility, DB connection, and table presence.
// Remove after debugging.

header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "=== Fargny API Diagnostic ===\n\n";

// 1) PHP version
echo "PHP version: " . PHP_VERSION . "\n";

// 2) Required extensions
foreach (['pdo', 'pdo_mysql', 'json', 'mbstring'] as $ext) {
    echo "ext $ext: " . (extension_loaded($ext) ? 'OK' : 'MISSING') . "\n";
}
echo "\n";

// 3) .env file
$envCandidates = [__DIR__ . '/../.env', __DIR__ . '/.env'];
foreach ($envCandidates as $p) {
    echo ".env at $p: " . (file_exists($p) ? 'EXISTS (' . filesize($p) . ' bytes)' : 'missing') . "\n";
}
echo "\n";

// 4) Try loading config and DB
try {
    require_once __DIR__ . '/config.php';
    echo "config.php loaded: OK\n";
    echo "DB_HOST=" . (env('DB_HOST') ?: '(empty)') . "\n";
    echo "DB_NAME=" . (env('DB_NAME') ?: '(empty)') . "\n";
    echo "DB_USER=" . (env('DB_USER') ?: '(empty)') . "\n";
    echo "DB_PASS length=" . strlen(env('DB_PASS') ?: '') . "\n\n";
} catch (Throwable $e) {
    echo "config.php ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit;
}

// 5) DB connection
try {
    $db = get_db();
    echo "DB connection: OK\n";
} catch (Throwable $e) {
    echo "DB connection ERROR: " . $e->getMessage() . "\n";
    exit;
}

// 6) Query shareholders
try {
    $rows = $db->query("SELECT COUNT(*) AS n FROM fargny_shareholders")->fetch();
    echo "fargny_shareholders rows: " . $rows['n'] . "\n";

    $rows = $db->query("SELECT COUNT(*) AS n FROM fargny_branches")->fetch();
    echo "fargny_branches rows: " . $rows['n'] . "\n";

    $rows = $db->query("SELECT COUNT(*) AS n FROM fargny_users")->fetch();
    echo "fargny_users rows: " . $rows['n'] . "\n";
} catch (Throwable $e) {
    echo "Query ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic complete ===\n";
