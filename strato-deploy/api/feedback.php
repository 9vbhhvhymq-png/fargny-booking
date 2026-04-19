<?php
// ============================================================
// Feedback: submit, count, export
// ============================================================

function handle_feedback(string $action, string $method) {
    switch ($action) {
        case '':
            if ($method === 'POST') { feedback_submit(); }
            elseif ($method === 'GET') { feedback_count(); }
            else json_error('Method not allowed', 405);
            break;
        case 'count':
            if ($method !== 'GET') json_error('GET required', 405);
            feedback_count();
            break;
        case 'export':
            if ($method !== 'GET') json_error('GET required', 405);
            feedback_export();
            break;
        default:
            json_error('Unknown action', 404);
    }
}

function feedback_submit() {
    $user = get_auth_user(); // Optional auth
    $body = get_json_body();
    $responses = $body['responses'] ?? null;

    $db = get_db();
    $db->prepare("INSERT INTO fargny_feedback (user_id, responses) VALUES (?, ?)")
       ->execute([$user ? $user['id'] : null, json_encode($responses)]);

    json_success(['id' => (int)$db->lastInsertId()], 201);
}

function feedback_count() {
    $db = get_db();
    $count = (int)$db->query("SELECT COUNT(*) FROM fargny_feedback")->fetchColumn();
    json_success(['count' => $count]);
}

function feedback_export() {
    require_admin();
    $db = get_db();

    $rows = $db->query("
        SELECT f.id, f.responses, f.submitted_at, u.display_name, u.email
        FROM fargny_feedback f
        LEFT JOIN fargny_users u ON u.id = f.user_id
        ORDER BY f.submitted_at DESC
    ")->fetchAll();

    $result = array_map(function($r) {
        return [
            'id'         => (int)$r['id'],
            'user_name'  => $r['display_name'] ?? 'Anonymous',
            'user_email' => $r['email'] ?? '',
            'responses'  => json_decode($r['responses'] ?? '{}', true),
            'submitted_at'=> $r['submitted_at'],
        ];
    }, $rows);

    json_success($result);
}
