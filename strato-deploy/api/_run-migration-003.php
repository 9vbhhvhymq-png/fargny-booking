<?php
// ============================================================
// ONE-SHOT migration runner — make Moritz an admin.
// Delete this file from the repo immediately after running it.
// Must be invoked with ?key=<APP_SECRET>.
// ============================================================
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$secret = env('APP_SECRET', '');
$key    = $_GET['key'] ?? '';
if (!$secret || !hash_equals($secret, $key)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'forbidden']);
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("UPDATE fargny_users SET is_admin = 1 WHERE display_name LIKE ? OR email LIKE ?");
    $stmt->execute(['%Moritz%Fromageot%', 'moritz.fromageot%']);
    $rows = $stmt->rowCount();

    $check = $db->query("SELECT id, display_name, email, is_admin FROM fargny_users WHERE display_name LIKE '%Moritz%' OR email LIKE 'moritz%'")->fetchAll();
    echo json_encode(['success' => true, 'rows_updated' => $rows, 'matched' => $check], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
