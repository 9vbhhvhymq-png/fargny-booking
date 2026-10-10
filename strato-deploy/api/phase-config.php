<?php
// ============================================================
// Phase Config: get/update phase configuration per year
// ============================================================

function handle_phase_config(string $action, string $method) {
    if ($method === 'GET') {
        phase_config_get();
    } elseif ($method === 'POST' || $method === 'PUT') {
        phase_config_update();
    } else {
        json_error('Method not allowed', 405);
    }
}

function phase_config_get() {
    $year = (int)($_GET['year'] ?? date('Y'));

    // The cycle is the source of truth. A stored phase_config row is still
    // returned for anything that reads the old field names, but the dates
    // come from cycle_dates() so the tiles, the rules and the emails can
    // never quote different days.
    $c = cycle_dates($year);

    json_success([
        'year'            => $year,
        // New shape: the cycle as the app now understands it.
        'cycle'           => $c,
        'cycle_phase'     => cycle_phase($year),
        'cycle_frozen'    => cycle_is_frozen($year),
        'clan_blind'      => booking_is_blind('clan', $year),
        'priority_blind'  => booking_is_blind('priority', $year),

        // Old field names, kept so nothing that still reads them breaks.
        // priority_end is deliberately absent: priority no longer closes.
        'clan_start'      => $c['clan_open'],
        'clan_end'        => $c['clan_close'],
        'clan_reveal'     => $c['clan_publish'],
        'priority_start'  => $c['priority_open'],
        'priority_reveal' => $c['priority_publish'],
        'regular_start'   => $year . '-01-01',
    ]);
}

function phase_config_update() {
    $admin = require_admin();
    $body = get_json_body();
    $year = (int)($body['year'] ?? 0);
    if (!$year) json_error('year required');

    $fields = ['clan_start', 'clan_end', 'clan_reveal', 'priority_start', 'priority_end', 'priority_reveal', 'regular_start'];
    foreach ($fields as $f) {
        if (empty($body[$f])) json_error("$f required");
    }

    $db = get_db();
    $stmt = $db->prepare("SELECT id FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $stmt->execute([$year]);

    if ($stmt->fetch()) {
        $db->prepare("
            UPDATE fargny_phase_config
            SET clan_start = ?, clan_end = ?, clan_reveal = ?,
                priority_start = ?, priority_end = ?, priority_reveal = ?,
                regular_start = ?
            WHERE year = ?
        ")->execute([
            $body['clan_start'], $body['clan_end'], $body['clan_reveal'],
            $body['priority_start'], $body['priority_end'], $body['priority_reveal'],
            $body['regular_start'], $year,
        ]);
    } else {
        $db->prepare("
            INSERT INTO fargny_phase_config (year, clan_start, clan_end, clan_reveal, priority_start, priority_end, priority_reveal, regular_start)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $year,
            $body['clan_start'], $body['clan_end'], $body['clan_reveal'],
            $body['priority_start'], $body['priority_end'], $body['priority_reveal'],
            $body['regular_start'],
        ]);
    }

    json_success(null);
}
