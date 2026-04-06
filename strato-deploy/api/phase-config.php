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
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM fargny_phase_config WHERE year = ? LIMIT 1");
    $stmt->execute([$year]);
    $cfg = $stmt->fetch();

    if (!$cfg) {
        // Return defaults
        json_success([
            'year'           => $year,
            'clan_start'     => ($year - 1) . '-11-01',
            'clan_end'       => ($year - 1) . '-12-15',
            'clan_reveal'    => ($year - 1) . '-12-15',
            'priority_start' => ($year - 1) . '-12-15',
            'priority_end'   => ($year - 1) . '-12-31',
            'priority_reveal'=> ($year - 1) . '-12-31',
            'regular_start'  => $year . '-01-01',
        ]);
        return;
    }

    json_success([
        'year'            => (int)$cfg['year'],
        'clan_start'      => $cfg['clan_start'],
        'clan_end'        => $cfg['clan_end'],
        'clan_reveal'     => $cfg['clan_reveal'],
        'priority_start'  => $cfg['priority_start'],
        'priority_end'    => $cfg['priority_end'],
        'priority_reveal' => $cfg['priority_reveal'],
        'regular_start'   => $cfg['regular_start'],
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
