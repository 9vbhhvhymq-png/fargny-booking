<?php
// ============================================================
// Scheduled announcements for the clan and priority rounds.
//
// Strato has no scheduler this app can reach, so this endpoint is
// driven from outside — a cron job in the Strato panel or a GitHub
// Actions schedule calling it once a day. Everything it sends is
// recorded in fargny_settings, so calling it twice, or ten times,
// sends nothing twice. That matters more than getting the timing
// exactly right: a duplicate "booking is open" mail to the whole
// family is worse than one arriving a few hours late.
// ============================================================

// GET|POST /api/notify/run
//   ?token=<NOTIFY_TOKEN>   for the scheduler
//   or an admin session     for a manual run from the admin panel
//   &dry=1                  report what would go out, send nothing
function notify_run() {
    $token    = (string)($_GET['token'] ?? '');
    $expected = (string)env('NOTIFY_TOKEN', '');
    $isAdmin  = false;
    $u = get_auth_user();
    if ($u && !empty($u['is_admin'])) $isAdmin = true;

    if (!$isAdmin) {
        if ($expected === '' || !hash_equals($expected, $token)) {
            json_error('Not authorised', 403);
        }
    }

    $dry   = !empty($_GET['dry']);
    $today = date('Y-m-d');
    $db    = get_db();
    require_once __DIR__ . '/email.php';

    $sent = [];
    $rows = $db->query("SELECT * FROM fargny_phase_config ORDER BY year")->fetchAll();

    foreach ($rows as $cfg) {
        $year = (int)$cfg['year'];

        // 1. The clan round opens.
        if ($cfg['clan_start'] && $today >= $cfg['clan_start'] && $today <= $cfg['clan_end']) {
            $sent[] = notify_once("clan_open_$year", $dry, function () use ($cfg, $year) {
                $n = 0;
                foreach (notify_shareholders() as $m) { send_clan_open($m, $cfg, $year); $n++; }
                return $n;
            });
        }

        // 3. The clan round is revealed, clashes and all.
        if ($cfg['clan_reveal'] && $today >= $cfg['clan_reveal']) {
            $sent[] = notify_once("clan_reveal_$year", $dry, function () use ($year) {
                $clashes = clan_clash_map($year);
                $n = 0;
                foreach (notify_shareholders() as $m) { send_clan_reveal($m, $year, count($clashes) > 0); $n++; }
                // The members actually caught in a clash get their own mail
                // naming who else claimed those nights.
                foreach (notify_clash_parties($year, $clashes) as $party) {
                    send_clan_clash($party['user'], $party['booking'], $party['others'], $year);
                    $n++;
                }
                return $n;
            });
        }

        // Priority is booked all year but still stays hidden until its
        // reveal date, so that date is worth announcing too.
        if ($cfg['priority_reveal'] && $today >= $cfg['priority_reveal']) {
            $sent[] = notify_once("priority_reveal_$year", $dry, function () use ($year) {
                $n = 0;
                foreach (notify_shareholders() as $m) { send_priority_reveal($m, $year); $n++; }
                return $n;
            });
        }
    }

    json_success([
        'today'   => $today,
        'dry_run' => $dry,
        'actions' => array_values(array_filter($sent)),
    ]);
}

// Run $fn only if this announcement has never gone out. The marker is
// written before sending: a half-finished run is better than a second
// full one landing in everyone's inbox.
function notify_once(string $key, bool $dry, callable $fn) {
    $settingKey = 'notified_' . $key;
    if (setting_get($settingKey, '') !== '') return null;
    if ($dry) return ['announcement' => $key, 'would_send' => true];
    setting_set($settingKey, date('Y-m-d H:i:s'));
    try {
        $count = $fn();
        return ['announcement' => $key, 'emails' => $count];
    } catch (Exception $e) {
        return ['announcement' => $key, 'error' => $e->getMessage()];
    }
}

// Everyone who can actually make a clan or priority booking. Family
// members are left out on purpose: telling them a round they cannot enter
// is open is noise.
function notify_shareholders(): array {
    ensure_role_columns();
    $rows = get_db()->query("
        SELECT id, display_name, email, branch_id
        FROM fargny_users
        WHERE email <> '' AND (role IS NULL OR role <> 'family_member')
    ")->fetchAll();
    return $rows ?: [];
}

// For each clashing clan booking, the member who made it and who else
// claimed those nights.
function notify_clash_parties(int $year, array $clashMap): array {
    if (!$clashMap) return [];
    $db = get_db();
    $ids = array_unique(array_merge(array_keys($clashMap), ...array_values($clashMap)));
    if (!$ids) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("
        SELECT b.*, u.display_name, u.email, br.name AS branch_name
        FROM fargny_bookings b
        JOIN fargny_users u ON u.id = b.user_id
        JOIN fargny_branches br ON br.id = b.branch_id
        WHERE b.id IN ($in)
    ");
    $stmt->execute(array_values($ids));
    $byId = [];
    foreach ($stmt->fetchAll() as $r) $byId[(int)$r['id']] = $r;

    $out = [];
    foreach ($clashMap as $id => $otherIds) {
        if (!isset($byId[$id])) continue;
        $others = [];
        foreach ($otherIds as $oid) if (isset($byId[$oid])) $others[] = $byId[$oid];
        if (!$others) continue;
        $out[] = [
            'user'    => ['display_name' => $byId[$id]['display_name'], 'email' => $byId[$id]['email']],
            'booking' => $byId[$id],
            'others'  => $others,
        ];
    }
    return $out;
}
