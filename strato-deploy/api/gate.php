<?php
// ============================================================
// Registration gate: a shared family question asked before anyone
// can see the shareholder roster or create an account.
//
// Threat model: keep out bots and passing strangers. It is not a
// password — the answer is known to the whole family and never
// rotates — so it is deliberately cheap: no lockout of real people,
// just enough friction that an automated signup cannot get through.
//
// Fails OPEN while no answer is configured, so a fresh deploy never
// locks the family out of registration. admin/gate reports that
// state so it is visible rather than silent.
// ============================================================

// Settings live in a generic key/value table so later toggles do not
// each need their own migration. Created on demand, like the
// password-reset table, so the shared host needs no manual step.
function ensure_gate_tables() {
    static $done = false;
    if ($done) return;
    $done = true;
    $db = get_db();
    $db->exec("
        CREATE TABLE IF NOT EXISTS `fargny_settings` (
          `name`       VARCHAR(64) NOT NULL,
          `value`      TEXT,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS `fargny_gate_tokens` (
          `token`      CHAR(64) NOT NULL,
          `expires_at` DATETIME NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`token`),
          KEY `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS `fargny_gate_attempts` (
          `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `ip`         VARCHAR(45) NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_ip_time` (`ip`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function setting_get(string $name, string $default = ''): string {
    ensure_gate_tables();
    $stmt = get_db()->prepare("SELECT value FROM fargny_settings WHERE name = ? LIMIT 1");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if (!$row || $row['value'] === null || $row['value'] === '') return $default;
    return (string)$row['value'];
}

function setting_set(string $name, string $value) {
    ensure_gate_tables();
    get_db()->prepare("
        INSERT INTO fargny_settings (name, value, updated_at) VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()
    ")->execute([$name, $value]);
}

// The question the family already knows the answer to. Editable from the
// admin panel; these are only the starting values.
function gate_question(string $lang = 'en'): string {
    $q = $lang === 'nl'
        ? setting_get('gate_question_nl', '')
        : setting_get('gate_question_en', '');
    if ($q === '') $q = setting_get('gate_question_en', 'What was the name of the Donkey?');
    return $q;
}

// Accepted answers, comma-separated so spelling variants can all pass.
function gate_answers(): array {
    $raw = setting_get('gate_answer', '');
    if (trim($raw) === '') return [];
    $out = [];
    foreach (explode(',', $raw) as $a) {
        $n = gate_normalise($a);
        if ($n !== '') $out[] = $n;
    }
    return $out;
}

// No answer configured means the gate is off. Checked everywhere rather
// than assumed, so the app is never accidentally sealed shut.
function gate_enabled(): bool {
    return count(gate_answers()) > 0;
}

// People will type "Barnabé ", "barnabe" or "Barnaby.". Compare only the
// letters and digits, lowercased and stripped of accents.
function gate_normalise(string $s): string {
    $s = trim($s);
    if ($s === '') return '';
    $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
    $map = [
        'á'=>'a','à'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','ô'=>'o','ö'=>'o','õ'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n','ÿ'=>'y','ß'=>'ss',
    ];
    $s = strtr($s, $map);
    return preg_replace('/[^a-z0-9]/', '', $s);
}

function gate_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return substr((string)$ip, 0, 45);
}

// Ten wrong answers from one address in fifteen minutes is a script, not
// a relative. Real people get plenty of room before this bites.
const GATE_MAX_ATTEMPTS = 10;
const GATE_WINDOW_MIN   = 15;

function gate_attempts_recent(): int {
    ensure_gate_tables();
    $stmt = get_db()->prepare("
        SELECT COUNT(*) AS n FROM fargny_gate_attempts
        WHERE ip = ? AND created_at > (NOW() - INTERVAL " . GATE_WINDOW_MIN . " MINUTE)
    ");
    $stmt->execute([gate_client_ip()]);
    $row = $stmt->fetch();
    return $row ? (int)$row['n'] : 0;
}

function gate_log_attempt() {
    ensure_gate_tables();
    $db = get_db();
    $db->prepare("INSERT INTO fargny_gate_attempts (ip) VALUES (?)")->execute([gate_client_ip()]);
    // Attempts are rare, so housekeeping inline costs nothing.
    try { $db->exec("DELETE FROM fargny_gate_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)"); }
    catch (Exception $e) {}
}

function gate_issue_token(): string {
    ensure_gate_tables();
    $token = bin2hex(random_bytes(32));
    $db = get_db();
    $db->prepare("INSERT INTO fargny_gate_tokens (token, expires_at) VALUES (?, (NOW() + INTERVAL 60 MINUTE))")
       ->execute([$token]);
    try { $db->exec("DELETE FROM fargny_gate_tokens WHERE expires_at < NOW()"); } catch (Exception $e) {}
    return $token;
}

function gate_token_valid(string $token): bool {
    if ($token === '') return false;
    ensure_gate_tables();
    $stmt = get_db()->prepare("SELECT token FROM fargny_gate_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    return (bool)$stmt->fetch();
}

// The pass travels in a header, with a body field as a fallback: Strato
// has been known to drop custom headers on some request types, and the
// same defensiveness already applies to Authorization.
function gate_token_from_request(): string {
    $candidates = [$_SERVER['HTTP_X_FARGNY_GATE'] ?? '', $_SERVER['REDIRECT_HTTP_X_FARGNY_GATE'] ?? ''];
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'X-Fargny-Gate') === 0) $candidates[] = $value;
        }
    }
    if (isset($_GET['gate_token'])) $candidates[] = $_GET['gate_token'];
    $body = get_json_body();
    if (isset($body['gate_token'])) $candidates[] = $body['gate_token'];
    foreach ($candidates as $c) {
        $c = trim((string)$c);
        if ($c !== '') return $c;
    }
    return '';
}

// Guard for anything a stranger must not reach: registration itself and
// the shareholder roster it needs. Signed-in members are already past it.
function require_gate_pass() {
    if (!gate_enabled()) return;
    if (get_auth_user()) return;
    if (gate_token_valid(gate_token_from_request())) return;
    json_error('Please answer the family question first', 403);
}

// ---- Endpoints -------------------------------------------------------

// GET /api/auth/gate — the question only. The answer never leaves the server.
function gate_public_config() {
    $lang = ($_GET['lang'] ?? 'en') === 'nl' ? 'nl' : 'en';
    json_success([
        'enabled'  => gate_enabled(),
        'question' => gate_question($lang),
    ]);
}

// POST /api/auth/gate — check an answer, hand back a short-lived pass.
function gate_submit_answer() {
    if (!gate_enabled()) {
        json_success(['token' => '', 'enabled' => false]);
        return;
    }
    if (gate_attempts_recent() >= GATE_MAX_ATTEMPTS) {
        json_error('Too many attempts. Please wait 15 minutes and try again.', 429);
    }

    $body   = get_json_body();
    $answer = gate_normalise((string)($body['answer'] ?? ''));
    if ($answer === '') json_error('Please type an answer');

    if (!in_array($answer, gate_answers(), true)) {
        gate_log_attempt();
        json_error("That's not the answer we were looking for");
    }

    json_success(['token' => gate_issue_token(), 'enabled' => true]);
}

// GET /api/admin/gate — current settings, answer included: an admin has to
// be able to read back what to tell the family.
function gate_admin_get() {
    require_admin();
    json_success([
        'enabled'     => gate_enabled(),
        'question_en' => setting_get('gate_question_en', 'What was the name of the Donkey?'),
        'question_nl' => setting_get('gate_question_nl', 'Hoe heette de ezel?'),
        'answer'      => setting_get('gate_answer', ''),
    ]);
}

// POST /api/admin/gate — clearing the answer switches the gate off again.
function gate_admin_save() {
    require_admin();
    $body = get_json_body();

    if (isset($body['question_en'])) setting_set('gate_question_en', trim((string)$body['question_en']));
    if (isset($body['question_nl'])) setting_set('gate_question_nl', trim((string)$body['question_nl']));
    if (isset($body['answer'])) {
        $answer = trim((string)$body['answer']);
        setting_set('gate_answer', $answer);
        // Old passes would outlive a changed answer; retire them together.
        try { get_db()->exec("DELETE FROM fargny_gate_tokens"); } catch (Exception $e) {}
    }

    gate_admin_get();
}
