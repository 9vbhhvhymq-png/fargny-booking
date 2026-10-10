<?php
// ============================================================
// The annual notification schedule.
//
// Each job is a date, a set of recipients and a template. A job is due
// once its date has passed and it has not already gone out; the log
// decides the second part, so running this twice sends nothing twice.
//
// Nothing here fires on its own. Something must call /api/notify/run —
// an external cron, or the button in the admin panel. For six fixed
// dates a year the button is a perfectly reasonable answer.
// ============================================================

// Which season is currently being booked. The cycle starts in the autumn
// of the preceding year: in October 2026 the family is booking 2027.
function active_booking_year(?string $today = null): int {
    $t = $today ? strtotime($today) : time();
    $y = (int)date('Y', $t);
    $m = (int)date('n', $t);
    return $m >= 10 ? $y + 1 : $y;
}

// A job that fell due long ago is not sent. Without this, the first time
// a scheduler is ever switched on it would fire every announcement whose
// date had passed — in January that means posting the October kickoff.
const NOTIFY_STALE_AFTER_DAYS = 21;

// The schedule. Stage by stage: the later announcements arrive with the
// rules they describe, rather than sitting here saying the wrong thing.
function notify_jobs(int $year): array {
    return [
        [
            'key'       => 'kickoff',
            'due'       => cycle_date('kickoff', $year),
            'label'     => 'Kickoff: how booking works this year',
            'template'  => 'kickoff',
            'audience'  => 'everyone',
        ],
        [
            'key'       => 'clan_open',
            'due'       => cycle_date('clan_open', $year),
            'label'     => 'The clan round is open',
            'template'  => 'clan_open',
            'audience'  => 'shareholders',
        ],
    ];
}

// Recipients for an audience name.
function notify_audience(string $audience): array {
    if ($audience === 'shareholders') {
        ensure_role_columns();
        try {
            return get_db()->query("
                SELECT id, display_name, email, branch_id
                FROM fargny_users
                WHERE email <> '' AND (role IS NULL OR role <> 'family_member')
                ORDER BY display_name
            ")->fetchAll() ?: [];
        } catch (Exception $e) { return notify_recipients(); }
    }
    return notify_recipients();
}

// What the scheduler would do right now, without doing it.
function notify_status(?string $today = null): array {
    $todayIso = $today ?: date('Y-m-d');
    $year     = active_booking_year($todayIso);
    $out      = [];
    foreach (notify_jobs($year) as $job) {
        $key  = $job['key'] . '_' . $year;
        $due  = $job['due'];
        $sent = notif_any_sent($key);
        $ageDays = $due ? (int)floor((strtotime($todayIso) - strtotime($due)) / 86400) : null;
        $state = 'scheduled';
        if ($sent)                                        $state = 'sent';
        elseif ($due === null)                            $state = 'no date';
        elseif ($todayIso < $due)                         $state = 'scheduled';
        elseif ($ageDays > NOTIFY_STALE_AFTER_DAYS)       $state = 'skipped (too old)';
        else                                              $state = 'due';
        $out[] = [
            'key'        => $key,
            'label'      => $job['label'],
            'template'   => $job['template'],
            'audience'   => $job['audience'],
            'due_date'   => $due,
            'state'      => $state,
            'recipients' => count(notify_audience($job['audience'])),
        ];
    }
    return ['today' => $todayIso, 'booking_year' => $year,
            'notifications_enabled' => notifications_enabled(),
            'dry_run' => mail_dry_run(), 'jobs' => $out];
}

// GET|POST /api/notify/run
//   ?token=<NOTIFY_TOKEN>  for an external scheduler
//   or an admin session    for the button in the admin panel
//   &dry=1                 report what would go out, send nothing
//   &only=<key>            run one job (the manual trigger)
//   &force=1               admin only: ignore the master switch
function notify_run() {
    $isAdmin = false;
    $u = get_auth_user();
    if ($u && !empty($u['is_admin'])) $isAdmin = true;

    if (!$isAdmin) {
        $expected = (string)env('NOTIFY_TOKEN', '');
        $token    = (string)($_GET['token'] ?? '');
        if ($expected === '' || !hash_equals($expected, $token)) {
            json_error('Not authorised', 403);
        }
    }

    require_once __DIR__ . '/email.php';
    require_once __DIR__ . '/notifications.php';
    require_once __DIR__ . '/notify_templates.php';

    $dry   = !empty($_GET['dry']);
    $only  = (string)($_GET['only'] ?? '');
    $force = $isAdmin && !empty($_GET['force']);
    $today = date('Y-m-d');
    $year  = active_booking_year($today);

    // One run at a time. Cron and a hand-pressed button must not both
    // start the same broadcast.
    $lock = scheduler_lock_acquire(600);
    if (!$lock) json_error('A notification run is already in progress', 409);

    $actions = [];
    try {
        foreach (notify_jobs($year) as $job) {
            $key = $job['key'] . '_' . $year;
            if ($only !== '' && $only !== $key && $only !== $job['key']) continue;
            if (!$job['due']) continue;

            // A manual trigger may run a job before its date; the
            // scheduler may not.
            $dueNow = $today >= $job['due'];
            if (!$dueNow && !($isAdmin && $only !== '')) continue;

            $age = (int)floor((strtotime($today) - strtotime($job['due'])) / 86400);
            if ($dueNow && $age > NOTIFY_STALE_AFTER_DAYS && !($isAdmin && $only !== '')) {
                $actions[] = ['key' => $key, 'skipped' => 'past due by ' . $age . ' days'];
                continue;
            }

            if (notif_any_sent($key)) {
                $actions[] = ['key' => $key, 'skipped' => 'already sent'];
                continue;
            }

            $members = notify_audience($job['audience']);
            if ($dry) {
                $actions[] = ['key' => $key, 'would_send_to' => count($members)];
                continue;
            }

            $tpl   = $job['template'];
            $tally = notify_broadcast($key, $members,
                function (array $m) use ($tpl, $year) { return notify_compose($tpl, $m, $year); },
                $force);
            $actions[] = array_merge(['key' => $key], array_filter($tally));
        }
    } finally {
        scheduler_lock_release($lock);
    }

    json_success([
        'today'        => $today,
        'booking_year' => $year,
        'dry_run'      => $dry,
        'enabled'      => notifications_enabled(),
        'actions'      => $actions,
    ]);
}
