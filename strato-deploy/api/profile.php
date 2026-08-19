<?php
// ============================================================
// Profiles: own profile, another member's profile (filtered by
// their visibility flags), the directory, stay history, photos
// ============================================================

function handle_profile(string $action, string $id, string $method) {
    ensure_profile_columns();

    switch ($action) {
        case 'me':
            if ($method === 'GET') { profile_me(); }
            elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'POST') { profile_update_me(); }
            else json_error('Method not allowed', 405);
            break;

        case 'photo':
            if ($method === 'POST') { profile_photo_upload(); }
            elseif ($method === 'DELETE') { profile_photo_delete(); }
            else json_error('Method not allowed', 405);
            break;

        case 'directory':
            if ($method !== 'GET') json_error('GET required', 405);
            profile_directory();
            break;

        case 'stays':
            if ($method !== 'GET') json_error('GET required', 405);
            profile_stays($id !== '' ? (int)$id : 0);
            break;

        default:
            // action is a user id
            if ($action === '' || !ctype_digit($action)) json_error('Not found', 404);
            if ($method !== 'GET') json_error('Method not allowed', 405);
            profile_get((int)$action);
    }
}

// ---- Normalisation and validation --------------------------------------

// Phone numbers are stored E.164: a leading '+' then digits only. Anything
// a member types — spaces, dashes, brackets, a leading 00 — is reduced to
// that. Returns null when nothing usable is left.
function profile_normalise_phone($raw): ?string {
    $raw = trim((string)$raw);
    if ($raw === '') return null;
    $hadPlus = strpos($raw, '+') === 0;
    $digits  = preg_replace('/\D+/', '', $raw);
    if ($digits === '') return null;
    if (!$hadPlus) {
        // 0049... is the international prefix written the old way.
        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        } else {
            // A national number still carrying its trunk zero (06..., 0157...).
            // The country code comes from the picker in front of the field, so
            // dropping the zero here keeps the stored value E.164-shaped rather
            // than an invalid "+0...".
            $digits = ltrim($digits, '0');
        }
    }
    if ($digits === '') return null;
    return '+' . substr($digits, 0, 19);
}

function profile_trim_bio($raw): ?string {
    $bio = trim((string)$raw);
    if ($bio === '') return null;
    // mb_substr keeps multi-byte characters whole at the 200 cap.
    return function_exists('mb_substr') ? mb_substr($bio, 0, 200) : substr($bio, 0, 200);
}

// Only known slugs survive, de-duplicated and in a stable order.
function profile_clean_skills($raw): ?string {
    if (!is_array($raw)) return null;
    $allowed = profile_skill_slugs();
    $out = [];
    foreach ($raw as $slug) {
        $slug = strtolower(trim((string)$slug));
        if (in_array($slug, $allowed, true) && !in_array($slug, $out, true)) $out[] = $slug;
    }
    return json_encode(array_values($out));
}

// Language codes: two-letter codes, comma separated, capped so the column
// cannot overflow.
function profile_clean_languages($raw): ?string {
    $list = is_array($raw) ? $raw : explode(',', (string)$raw);
    $out = [];
    foreach ($list as $code) {
        $code = strtolower(trim((string)$code));
        if (preg_match('/^[a-z]{2}$/', $code) && !in_array($code, $out, true)) $out[] = $code;
        if (count($out) >= 10) break;
    }
    return $out ? implode(',', $out) : null;
}

// ---- Shaping -----------------------------------------------------------

function profile_decode_skills($raw): array {
    $decoded = json_decode((string)($raw ?? '[]'), true);
    return is_array($decoded) ? array_values($decoded) : [];
}

function profile_photo_url(array $u): ?string {
    if (empty($u['photo_path'])) return null;
    // Cache-buster so a re-upload (same filename) is picked up.
    $v = !empty($u['updated_at']) ? strtotime($u['updated_at']) : (!empty($u['last_login']) ? strtotime($u['last_login']) : 0);
    return $u['photo_path'] . '?v=' . $v;
}

// Everything about a member, as they see it themselves.
function profile_shape_full(array $u): array {
    return [
        'user_id'               => (int)$u['id'],
        'display_name'          => $u['display_name'],
        'email'                 => $u['email'],
        'branch_id'             => (int)$u['branch_id'],
        'role'                  => user_role($u),
        'photo_path'            => $u['photo_path'] ?? null,
        'photo_url'             => profile_photo_url($u),
        'bio'                   => $u['bio'] ?? null,
        'phone_e164'            => $u['phone_e164'] ?? null,
        'pref_stay'             => $u['pref_stay'] ?? 'none',
        'pref_season'           => $u['pref_season'] ?? null,
        'home_town'             => $u['home_town'] ?? null,
        'languages'             => $u['languages'] ?? null,
        'household_size'        => isset($u['household_size']) && $u['household_size'] !== null ? (int)$u['household_size'] : null,
        'skills'                => profile_decode_skills($u['skills'] ?? null),
        'open_to_share_default' => !empty($u['open_to_share_default']),
        'vis_photo_bio'         => !empty($u['vis_photo_bio']),
        'vis_phone'             => !empty($u['vis_phone']),
        'vis_town'              => !empty($u['vis_town']),
        'vis_stays'             => !empty($u['vis_stays']),
        'is_self'               => true,
    ];
}

// The same member as another logged-in family member sees them. Hidden
// fields are removed here, in PHP — they are never sent and then hidden in
// the browser. There is one flag for contact details, vis_phone, and it
// governs both the phone number and the email address.
function profile_shape_visible(array $u): array {
    $showPhotoBio = !empty($u['vis_photo_bio']);
    $showContact  = !empty($u['vis_phone']);
    $showTown     = !empty($u['vis_town']);

    $out = [
        'user_id'               => (int)$u['id'],
        'display_name'          => $u['display_name'],
        'branch_id'             => (int)$u['branch_id'],
        'role'                  => user_role($u),
        'pref_stay'             => $u['pref_stay'] ?? 'none',
        'pref_season'           => $u['pref_season'] ?? null,
        'languages'             => $u['languages'] ?? null,
        'household_size'        => isset($u['household_size']) && $u['household_size'] !== null ? (int)$u['household_size'] : null,
        'skills'                => profile_decode_skills($u['skills'] ?? null),
        'open_to_share_default' => !empty($u['open_to_share_default']),
        'stays_visible'         => !empty($u['vis_stays']),
        'is_self'               => false,
    ];
    if ($showPhotoBio) {
        $out['photo_path'] = $u['photo_path'] ?? null;
        $out['photo_url']  = profile_photo_url($u);
        $out['bio']        = $u['bio'] ?? null;
    }
    if ($showContact) {
        $out['phone_e164'] = $u['phone_e164'] ?? null;
        $out['email']      = $u['email'];
    }
    if ($showTown) {
        $out['home_town'] = $u['home_town'] ?? null;
    }
    return $out;
}

// ---- Endpoints ---------------------------------------------------------

function profile_me() {
    $user = require_auth();
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$user['id']]);
    $u = $stmt->fetch();
    if (!$u) json_error('User not found', 404);
    json_success(profile_shape_full($u));
}

function profile_update_me() {
    $user = require_auth();
    $body = get_json_body();
    $db = get_db();

    $fields = [];
    $params = [];
    $set = function ($col, $val) use (&$fields, &$params) {
        $fields[] = "`$col` = ?";
        $params[] = $val;
    };

    if (array_key_exists('bio', $body))        $set('bio', profile_trim_bio($body['bio']));
    if (array_key_exists('phone_e164', $body)) $set('phone_e164', profile_normalise_phone($body['phone_e164']));
    if (array_key_exists('home_town', $body)) {
        $town = trim((string)$body['home_town']);
        $set('home_town', $town === '' ? null : mb_substr($town, 0, 80));
    }
    if (array_key_exists('pref_season', $body)) {
        $season = trim((string)$body['pref_season']);
        $set('pref_season', $season === '' ? null : mb_substr($season, 0, 40));
    }
    if (array_key_exists('pref_stay', $body)) {
        $pref = (string)$body['pref_stay'];
        if (!in_array($pref, profile_stay_prefs(), true)) json_error('Unknown stay preference');
        $set('pref_stay', $pref);
    }
    if (array_key_exists('languages', $body))  $set('languages', profile_clean_languages($body['languages']));
    if (array_key_exists('skills', $body)) {
        if ($body['skills'] !== null && !is_array($body['skills'])) json_error('Skills must be a list');
        // Reject unknown slugs outright rather than silently dropping them.
        if (is_array($body['skills'])) {
            $allowed = profile_skill_slugs();
            foreach ($body['skills'] as $slug) {
                if (!in_array(strtolower(trim((string)$slug)), $allowed, true)) {
                    json_error('Unknown skill: ' . (string)$slug);
                }
            }
        }
        $set('skills', profile_clean_skills($body['skills']));
    }
    if (array_key_exists('household_size', $body)) {
        $n = $body['household_size'];
        if ($n === null || $n === '') $set('household_size', null);
        else {
            $n = (int)$n;
            if ($n < 1 || $n > 50) json_error('Household size must be between 1 and 50');
            $set('household_size', $n);
        }
    }
    foreach (['open_to_share_default', 'vis_photo_bio', 'vis_phone', 'vis_town', 'vis_stays'] as $flag) {
        if (array_key_exists($flag, $body)) $set($flag, $body[$flag] ? 1 : 0);
    }

    if (!$fields) json_error('Nothing to update');

    $params[] = (int)$user['id'];
    $db->prepare("UPDATE fargny_users SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$user['id']]);
    json_success(profile_shape_full($stmt->fetch()));
}

function profile_get(int $userId) {
    $user = require_auth();
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) json_error('Member not found', 404);

    if ($userId === (int)$user['id']) json_success(profile_shape_full($u));
    json_success(profile_shape_visible($u));
}

function profile_directory() {
    $user = require_auth();
    ensure_role_columns();
    $db = get_db();

    // Registered accounts only — the shareholder roster is a separate list
    // and includes people who have never signed up.
    $users = $db->query("SELECT * FROM fargny_users ORDER BY display_name")->fetchAll();

    $result = array_map(function ($u) use ($user) {
        $isSelf = (int)$u['id'] === (int)$user['id'];
        $showPhoto = $isSelf || !empty($u['vis_photo_bio']);
        $showTown  = $isSelf || !empty($u['vis_town']);
        $showPhone = $isSelf || !empty($u['vis_phone']);
        return [
            'user_id'               => (int)$u['id'],
            'display_name'          => $u['display_name'],
            'branch_id'             => (int)$u['branch_id'],
            'role'                  => user_role($u),
            'photo_path'            => $showPhoto ? ($u['photo_path'] ?? null) : null,
            'photo_url'             => $showPhoto ? profile_photo_url($u) : null,
            'pref_stay'             => $u['pref_stay'] ?? 'none',
            'home_town'             => $showTown ? ($u['home_town'] ?? null) : null,
            'has_phone'             => $showPhone && !empty($u['phone_e164']),
            'open_to_share_default' => !empty($u['open_to_share_default']),
            'skills'                => profile_decode_skills($u['skills'] ?? null),
            'has_profile'           => !empty($u['photo_path']) || !empty($u['bio']) || !empty($u['home_town'])
                                       || !empty($u['skills']) || ($u['pref_stay'] ?? 'none') !== 'none',
            'is_self'               => $isSelf,
        ];
    }, $users);

    json_success($result);
}

// Stay history. Deliberately neutral about how a week was obtained: the
// booking phase is not part of this response.
function profile_stays(int $userId = 0) {
    $user = require_auth();
    $db = get_db();

    $targetId = $userId ?: (int)$user['id'];
    $isSelf = $targetId === (int)$user['id'];

    if (!$isSelf) {
        $stmt = $db->prepare("SELECT vis_stays FROM fargny_users WHERE id = ? LIMIT 1");
        $stmt->execute([$targetId]);
        $target = $stmt->fetch();
        if (!$target) json_error('Member not found', 404);
        if (empty($target['vis_stays'])) json_error('This member keeps their stay history private', 403);
    }

    $stmt = $db->prepare("
        SELECT b.id, b.week_id, b.year, b.check_in_date, b.check_out_date,
               p.guest_data
        FROM fargny_bookings b
        LEFT JOIN fargny_payments p ON p.booking_id = b.id
        WHERE b.user_id = ? AND b.cancellation_status != 'approved'
    ");
    $stmt->execute([$targetId]);
    $rows = $stmt->fetchAll();

    $today = date('Y-m-d');
    $weeksByYear = [];
    $stays = [];
    foreach ($rows as $r) {
        $range = booking_night_range($r, $weeksByYear);
        if (!$range) continue;
        [$in, $out] = $range;

        $nights = (int)(new DateTime($in))->diff(new DateTime($out))->days;

        // Guests: the largest single night in the grid is the party size.
        $guests = null;
        $grid = json_decode((string)($r['guest_data'] ?? 'null'), true);
        if (is_array($grid)) {
            $max = 0;
            foreach ($grid as $night) {
                if (!is_array($night)) continue;
                $n = (int)($night['child04'] ?? 0) + (int)($night['child59'] ?? 0) + (int)($night['adult'] ?? 0);
                if ($n > $max) $max = $n;
            }
            if ($max > 0) $guests = $max;
        }

        $stays[] = [
            'booking_id'     => (int)$r['id'],
            'check_in_date'  => $in,
            'check_out_date' => $out,
            'nights'         => $nights,
            'guests'         => $guests,
            'upcoming'       => $out > $today,
        ];
    }

    // Newest first.
    usort($stays, function ($a, $b) {
        return strcmp($b['check_in_date'], $a['check_in_date']);
    });

    $past = array_values(array_filter($stays, function ($s) { return !$s['upcoming']; }));
    $totalNights = 0;
    foreach ($past as $s) $totalNights += $s['nights'];
    $firstVisit = $past ? $past[count($past) - 1]['check_in_date'] : null;

    json_success([
        'user_id' => $targetId,
        'stays'   => $stays,
        'totals'  => [
            'stays'       => count($past),
            'nights'      => $totalNights,
            'first_visit' => $firstVisit,
        ],
    ]);
}

// ---- Photos ------------------------------------------------------------

function profile_uploads_dir(): string {
    return dirname(__DIR__) . '/uploads';
}

function profile_photo_upload() {
    $user = require_auth();

    if (!isset($_FILES['photo']) || !is_array($_FILES['photo'])) json_error('No file received');
    $file = $_FILES['photo'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        json_error('Upload failed, please try again');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) json_error('Photo must be 5 MB or smaller');
    if (!is_uploaded_file($file['tmp_name'])) json_error('Upload failed, please try again');

    // Trust the file's own bytes, never the browser-supplied type.
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($info[2])) json_error('That file is not an image');
    $type = $info[2];
    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
    if (!in_array($type, $allowed, true)) json_error('Photos must be JPEG, PNG or WebP');

    if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
        json_error('Image processing is unavailable on the server, so photos cannot be accepted right now', 503);
    }

    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($file['tmp_name']); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($file['tmp_name']);  break;
        case IMAGETYPE_WEBP:
            if (!function_exists('imagecreatefromwebp')) json_error('WebP is not supported on this server, please use JPEG or PNG');
            $src = @imagecreatefromwebp($file['tmp_name']);
            break;
        default: $src = false;
    }
    if (!$src) json_error('That image could not be read');

    // Centre-crop to a square, then scale to 256x256.
    $w = imagesx($src); $h = imagesy($src);
    $side = min($w, $h);
    $sx = (int)(($w - $side) / 2);
    $sy = (int)(($h - $side) / 2);

    $dst = imagecreatetruecolor(256, 256);
    // Flatten transparency onto white so PNG/WebP alpha does not go black.
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, 256, 256, $white);
    imagecopyresampled($dst, $src, 0, 0, $sx, $sy, 256, 256, $side, $side);
    imagedestroy($src);

    $dir = profile_uploads_dir();
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir) || !is_writable($dir)) {
        imagedestroy($dst);
        json_error('The uploads folder is not writable on the server', 500);
    }

    $rel = 'uploads/u' . (int)$user['id'] . '.jpg';
    $abs = $dir . '/u' . (int)$user['id'] . '.jpg';
    $ok = imagejpeg($dst, $abs, 82);
    imagedestroy($dst);
    if (!$ok) json_error('The photo could not be saved', 500);

    $db = get_db();
    $db->prepare("UPDATE fargny_users SET photo_path = ? WHERE id = ?")
       ->execute([$rel, (int)$user['id']]);

    json_success(['photo_path' => $rel, 'photo_url' => $rel . '?v=' . time()]);
}

function profile_photo_delete() {
    $user = require_auth();
    $db = get_db();

    $stmt = $db->prepare("SELECT photo_path FROM fargny_users WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$user['id']]);
    $row = $stmt->fetch();

    // Only ever remove this user's own generated file.
    if ($row && !empty($row['photo_path'])) {
        $abs = profile_uploads_dir() . '/u' . (int)$user['id'] . '.jpg';
        if (is_file($abs)) @unlink($abs);
    }
    $db->prepare("UPDATE fargny_users SET photo_path = NULL WHERE id = ?")->execute([(int)$user['id']]);

    json_success(['photo_path' => null]);
}
