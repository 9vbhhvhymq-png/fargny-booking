<?php
// ============================================================
// wipe-test-data.php — Nuclear reset for development/testing
// ============================================================
// NEVER deployed automatically (excluded by deploy workflow).
// Must be manually uploaded to the server when needed.
//
// Usage:
//   Preview (shows counts, no changes):
//     GET /api/wipe-test-data.php?key=wipe-fargny-2026
//
//   Execute (wipes everything and re-seeds admin):
//     GET /api/wipe-test-data.php?key=wipe-fargny-2026&confirm=yes
//
// Accepts ?key=wipe-fargny-2026 or the APP_SECRET from .env.
// This file is never auto-deployed; upload manually when needed
// and delete it from the server afterwards.
// ============================================================

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

$key = $_GET['key'] ?? '';
$validKeys = array_filter(['wipe-fargny-2026', env('APP_SECRET', '')]);
if (!$key || !in_array($key, $validKeys, true)) {
    http_response_code(403);
    echo "403 Forbidden — provide ?key=wipe-fargny-2026\n";
    exit;
}

$confirm = (($_GET['confirm'] ?? '') === 'yes');
$db = get_db();

// -----------------------------------------------------------
// Count how many rows exist in each table
// -----------------------------------------------------------
$tables = [
    'fargny_board_signups',
    'fargny_board_events',
    'fargny_payments',
    'fargny_bookings',
    'fargny_sessions',
    'fargny_feedback',
    'fargny_users',
];

echo "=== FARGNY DATA WIPE UTILITY ===\n";
echo $confirm ? "MODE: EXECUTE\n\n" : "MODE: PREVIEW (add &confirm=yes to execute)\n\n";

echo "Current row counts:\n";
foreach ($tables as $t) {
    $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "  $t: $c row(s)\n";
}
$shLinked = $db->query("SELECT COUNT(*) FROM fargny_shareholders WHERE user_id IS NOT NULL")->fetchColumn();
echo "  fargny_shareholders with linked users: $shLinked\n";

if (!$confirm) {
    echo "\nNothing changed. Run with &confirm=yes to wipe all the above.\n";
    exit;
}

// -----------------------------------------------------------
// Execute: wipe all user-generated data
// -----------------------------------------------------------
echo "\nWiping data...\n";

$db->exec("SET FOREIGN_KEY_CHECKS = 0");

foreach ($tables as $t) {
    $db->exec("DELETE FROM `$t`");
    $db->exec("ALTER TABLE `$t` AUTO_INCREMENT = 1");
    echo "  Cleared $t\n";
}

// Unlink all shareholder → user references
$db->exec("UPDATE fargny_shareholders SET user_id = NULL");
echo "  Reset fargny_shareholders.user_id\n";

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

// -----------------------------------------------------------
// Re-create Moritz Fromageot as primary admin
// -----------------------------------------------------------
echo "\nRe-creating admin account...\n";

$hash = password_hash('admin', PASSWORD_BCRYPT);
$db->prepare("
    INSERT INTO fargny_users (display_name, email, password_hash, branch_id, is_admin)
    VALUES (?, ?, ?, ?, ?)
")->execute(['Moritz Fromageot', 'moritz@fromageot.eu', $hash, 9, 1]);
$newUserId = (int)$db->lastInsertId();

// Link Moritz's shareholder row
$linked = $db->prepare("
    UPDATE fargny_shareholders
    SET user_id = ?
    WHERE full_name = 'Moritz Fromageot'
");
$linked->execute([$newUserId]);

echo "  Created user: Moritz Fromageot <moritz@fromageot.eu> (id=$newUserId, is_admin=1)\n";
echo "  Linked fargny_shareholders row for 'Moritz Fromageot' → user_id=$newUserId\n";

// -----------------------------------------------------------
// Done
// -----------------------------------------------------------
echo "\n=== WIPE COMPLETE ===\n";
echo "Login with moritz@fromageot.eu / admin\n";
echo "IMPORTANT: Change the password immediately after logging in.\n";
