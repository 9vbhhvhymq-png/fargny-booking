<?php
// ============================================================
// Board Events: CRUD and signup
// ============================================================

function handle_board_events(string $action, string $id, string $method) {
    if ($action === '' && $method === 'GET') {
        board_events_list();
    } elseif ($action === '' && $method === 'POST') {
        board_events_create();
    } elseif ($id === 'signup' && $method === 'POST') {
        // $action is the event ID
        board_events_signup((int)$action);
    } elseif ($id === 'signup' && $method === 'DELETE') {
        board_events_unsignup((int)$action);
    } else {
        json_error('Not found', 404);
    }
}

function board_events_list() {
    $db = get_db();
    $user = get_current_user(); // May be null for public access

    $events = $db->query("
        SELECT be.*,
               u.display_name AS creator_name,
               (SELECT COUNT(*) FROM fargny_board_signups bs WHERE bs.event_id = be.id) AS signup_count
        FROM fargny_board_events be
        LEFT JOIN fargny_users u ON u.id = be.created_by
        ORDER BY be.start_date
    ")->fetchAll();

    // Get signups per event
    $signups = $db->query("
        SELECT bs.event_id, bs.user_id, u.display_name, u.email
        FROM fargny_board_signups bs
        JOIN fargny_users u ON u.id = bs.user_id
        ORDER BY bs.signed_up_at
    ")->fetchAll();

    $signupMap = [];
    foreach ($signups as $s) {
        $eid = (int)$s['event_id'];
        if (!isset($signupMap[$eid])) $signupMap[$eid] = [];
        $signupMap[$eid][] = [
            'user_id'      => (int)$s['user_id'],
            'display_name' => $s['display_name'],
            'email'        => $s['email'],
        ];
    }

    $result = array_map(function($ev) use ($signupMap, $user) {
        $eid = (int)$ev['id'];
        $participants = $signupMap[$eid] ?? [];
        $userSignedUp = false;
        if ($user) {
            foreach ($participants as $p) {
                if ($p['user_id'] === (int)$user['id']) { $userSignedUp = true; break; }
            }
        }
        return [
            'id'            => $eid,
            'name'          => $ev['name'],
            'start_date'    => $ev['start_date'],
            'end_date'      => $ev['end_date'],
            'description'   => $ev['description'],
            'creator_name'  => $ev['creator_name'],
            'signup_count'  => (int)$ev['signup_count'],
            'participants'  => $participants,
            'user_signed_up'=> $userSignedUp,
        ];
    }, $events);

    json_success($result);
}

function board_events_create() {
    $admin = require_admin();
    $body = get_json_body();

    $name  = trim($body['name'] ?? '');
    $start = $body['start_date'] ?? '';
    $end   = $body['end_date'] ?? '';
    $desc  = trim($body['description'] ?? '');

    if (!$name || !$start || !$end) json_error('name, start_date, end_date required');

    $db = get_db();
    $db->prepare("INSERT INTO fargny_board_events (name, start_date, end_date, description, created_by) VALUES (?, ?, ?, ?, ?)")
       ->execute([$name, $start, $end, $desc, $admin['id']]);

    json_success(['id' => (int)$db->lastInsertId()], 201);
}

function board_events_signup(int $eventId) {
    $user = require_auth();
    $db = get_db();

    // Check event exists
    $stmt = $db->prepare("SELECT id FROM fargny_board_events WHERE id = ? LIMIT 1");
    $stmt->execute([$eventId]);
    if (!$stmt->fetch()) json_error('Event not found', 404);

    // Check not already signed up
    $stmt = $db->prepare("SELECT id FROM fargny_board_signups WHERE event_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$eventId, $user['id']]);
    if ($stmt->fetch()) json_error('Already signed up');

    $db->prepare("INSERT INTO fargny_board_signups (event_id, user_id) VALUES (?, ?)")
       ->execute([$eventId, $user['id']]);

    json_success(null, 201);
}

function board_events_unsignup(int $eventId) {
    $user = require_auth();
    $db = get_db();

    $db->prepare("DELETE FROM fargny_board_signups WHERE event_id = ? AND user_id = ?")
       ->execute([$eventId, $user['id']]);

    json_success(null);
}
