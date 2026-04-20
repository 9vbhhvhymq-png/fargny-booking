<?php
// ============================================================
// ONE-SHOT: link Moritz to the correct shareholder + branch.
// Moves user "Moritz Fromageot" from Henk (branch 1) to Bertrand
// (branch 9, the Fromageot side of the family), and links him
// to shareholder "Moritz (1992)".
// Delete this file from the repo immediately after running.
// Invoke with ?key=<APP_SECRET>.
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

    // Find the user
    $stmt = $db->prepare("SELECT id, display_name, email, branch_id FROM fargny_users WHERE email = ? LIMIT 1");
    $stmt->execute(['moritz.fromageot@gmail.com']);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'user not found']);
        exit;
    }

    // Find the matching shareholder (Moritz (1992))
    $stmt = $db->prepare("SELECT id, full_name, branch_id FROM fargny_shareholders WHERE full_name LIKE ? LIMIT 1");
    $stmt->execute(['Moritz (1992)%']);
    $sh = $stmt->fetch();
    if (!$sh) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'shareholder not found']);
        exit;
    }

    // Unlink any other user on that shareholder slot, link Moritz
    $db->prepare("UPDATE fargny_shareholders SET user_id = NULL WHERE user_id = ?")->execute([$user['id']]);
    $db->prepare("UPDATE fargny_shareholders SET user_id = ? WHERE id = ?")->execute([$user['id'], $sh['id']]);

    // Move the user to the correct branch
    $db->prepare("UPDATE fargny_users SET branch_id = ? WHERE id = ?")->execute([$sh['branch_id'], $user['id']]);

    $out = $db->prepare("SELECT u.id, u.display_name, u.email, u.branch_id, b.name AS branch_name FROM fargny_users u JOIN fargny_branches b ON b.id = u.branch_id WHERE u.id = ?");
    $out->execute([$user['id']]);

    echo json_encode(['success' => true, 'user' => $out->fetch(), 'linked_shareholder' => $sh], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
