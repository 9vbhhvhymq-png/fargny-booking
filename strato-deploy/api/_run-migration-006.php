<?php
// One-shot migration: add max_participants column to fargny_board_events.
// Usage: GET /api/_run-migration-006.php?key=APP_SECRET
require_once __DIR__ . '/config.php';

$key = $_GET['key'] ?? '';
if ($key !== env('APP_SECRET', '')) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

header('Content-Type: text/plain');

try {
    $db = get_db();
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM fargny_board_events LIKE 'max_participants'");
    if ($stmt->fetch()) {
        echo "Column max_participants already exists. Nothing to do.\n";
    } else {
        $db->exec("ALTER TABLE fargny_board_events ADD COLUMN max_participants INT NULL AFTER description");
        echo "Migration 006 applied: max_participants column added.\n";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Migration failed: " . $e->getMessage() . "\n";
}
