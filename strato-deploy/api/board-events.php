<?php
// ============================================================
// Board Events: CRUD and signup
// ============================================================

// True if fargny_board_events has the optional max_participants column.
// Cached per-request so we don't introspect the schema repeatedly.
function board_events_has_max_col(): bool {
    static $cached = null;
    if ($cached !== null) return $cached;
    try {
        $db = get_db();
        $stmt = $db->query("SHOW COLUMNS FROM fargny_board_events LIKE 'max_participants'");
        $cached = (bool)$stmt->fetch();
    } catch (Exception $e) {
        $cached = false;
    }
    return $cached;
}

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
    $user = get_auth_user(); // May be null for public access

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
            'id'              => $eid,
            'name'            => $ev['name'],
            'start_date'      => $ev['start_date'],
            'end_date'        => $ev['end_date'],
            'description'     => $ev['description'],
            'creator_name'    => $ev['creator_name'],
            'signup_count'    => (int)$ev['signup_count'],
            'max_participants'=> isset($ev['max_participants']) && $ev['max_participants']!==null ? (int)$ev['max_participants'] : null,
            'participants'    => $participants,
            'user_signed_up'  => $userSignedUp,
        ];
    }, $events);

    json_success($result);
}

function board_events_create() {
    // Special events: any logged-in user can create one. The creator is
    // automatically signed up as the first participant.
    $user = require_auth();
    $body = get_json_body();

    $name  = trim($body['name'] ?? '');
    $start = $body['start_date'] ?? '';
    $end   = $body['end_date'] ?? '';
    $desc  = trim($body['description'] ?? '');
    $maxP  = isset($body['max_participants']) && $body['max_participants']!==null && $body['max_participants']!=='' ? (int)$body['max_participants'] : null;

    if (!$name || !$start || !$end) json_error('name, start_date, end_date required');

    $db = get_db();
    if (board_events_has_max_col()) {
        $db->prepare("INSERT INTO fargny_board_events (name, start_date, end_date, description, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$name, $start, $end, $desc, $maxP, $user['id']]);
    } else {
        $db->prepare("INSERT INTO fargny_board_events (name, start_date, end_date, description, created_by) VALUES (?, ?, ?, ?, ?)")
           ->execute([$name, $start, $end, $desc, $user['id']]);
    }
    $eventId = (int)$db->lastInsertId();

    // Auto-signup the creator as the first participant
    try {
        $db->prepare("INSERT INTO fargny_board_signups (event_id, user_id) VALUES (?, ?)")
           ->execute([$eventId, $user['id']]);
    } catch (Exception $e) { /* ignore dup */ }

    json_success(['id' => $eventId], 201);
}

function board_events_signup(int $eventId) {
    $user = require_auth();
    $db = get_db();

    // Check event exists
    $hasMax = board_events_has_max_col();
    $sql = $hasMax
        ? "SELECT id, max_participants FROM fargny_board_events WHERE id = ? LIMIT 1"
        : "SELECT id FROM fargny_board_events WHERE id = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$eventId]);
    $ev = $stmt->fetch();
    if (!$ev) json_error('Event not found', 404);

    // Check not already signed up
    $stmt = $db->prepare("SELECT id FROM fargny_board_signups WHERE event_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$eventId, $user['id']]);
    if ($stmt->fetch()) json_error('Already signed up');

    // Enforce max_participants (only when the column exists)
    if ($hasMax && !empty($ev['max_participants'])) {
        $cstmt = $db->prepare("SELECT COUNT(*) AS c FROM fargny_board_signups WHERE event_id = ?");
        $cstmt->execute([$eventId]);
        $cnt = (int)$cstmt->fetch()['c'];
        if ($cnt >= (int)$ev['max_participants']) json_error('This event is full');
    }

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
