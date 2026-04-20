<?php
// ============================================================
// Google Calendar (public iCal) import
// ------------------------------------------------------------
// Reads a Google Calendar .ics feed (URL from GOOGLE_CALENDAR_URL in
// .env), parses the VEVENT entries, and returns them as JSON. The
// result is cached to a tmp file for 15 minutes to avoid hammering
// Google on every page load.
// ============================================================

function handle_google_calendar(string $action, string $method) {
    if ($method !== 'GET') json_error('GET required', 405);
    gcal_fetch();
}

function gcal_cache_path(): string {
    return sys_get_temp_dir() . '/fargny_gcal_cache.json';
}

function gcal_fetch() {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
    // Fall back to the known Fargny public feed if the .env value is missing.
    // This is a public iCal URL — not a secret — so baking it in is safe.
    $url  = env('GOOGLE_CALENDAR_URL',
        'https://calendar.google.com/calendar/ical/fargnyonline%40gmail.com/public/basic.ics');
    if (!$url) {
        json_success(['events' => [], 'source' => 'gcal', 'cached' => false, 'error' => 'GOOGLE_CALENDAR_URL not configured']);
        return;
    }

    $cachePath = gcal_cache_path();
    $cacheTtl  = 15 * 60; // 15 minutes
    $events    = null;
    $cached    = false;

    // ---- Try cache ----
    if (file_exists($cachePath) && (time() - filemtime($cachePath)) < $cacheTtl) {
        $raw = @file_get_contents($cachePath);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $events = $decoded;
            $cached = true;
        }
    }

    // ---- Cache miss: fetch from Google ----
    if ($events === null) {
        $ics = gcal_http_get($url);
        if ($ics === false) {
            // If fetch fails, try returning stale cache if any
            if (file_exists($cachePath)) {
                $raw = @file_get_contents($cachePath);
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    json_success(['events' => filter_by_year($decoded, $year), 'source' => 'gcal', 'cached' => true, 'stale' => true]);
                    return;
                }
            }
            json_error('Failed to fetch Google Calendar feed', 502);
        }
        $events = gcal_parse_ics($ics);
        @file_put_contents($cachePath, json_encode($events));
    }

    json_success([
        'events' => filter_by_year($events, $year),
        'source' => 'gcal',
        'cached' => $cached,
    ]);
}

function filter_by_year(array $events, int $year): array {
    if (!$year) return $events;
    $out = [];
    foreach ($events as $ev) {
        // keep events that overlap the given year
        $s = substr($ev['start_date'] ?? '', 0, 4);
        $e = substr($ev['end_date']   ?? '', 0, 4);
        if ($s === (string)$year || $e === (string)$year) $out[] = $ev;
    }
    return $out;
}

// ---- HTTP fetch (cURL first, then file_get_contents fallback) ----
function gcal_http_get(string $url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_USERAGENT      => 'Fargny-Booking/1.0 (iCal sync)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($body !== false && $code >= 200 && $code < 400) return $body;
    }
    // Fallback
    $ctx = stream_context_create(['http' => [
        'timeout' => 15,
        'header'  => "User-Agent: Fargny-Booking/1.0 (iCal sync)\r\n",
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? false : $body;
}

// ---- iCal parser ----
// Handles RFC 5545 basics: line folding (continuation = space/tab
// prefix), VEVENT blocks, DATE (YYYYMMDD) and DATE-TIME (YYYYMMDDTHHMMSSZ),
// escape sequences in TEXT fields (\n, \,, \;, \\).
function gcal_parse_ics(string $ics): array {
    // Normalize line endings & unfold
    $ics = str_replace(["\r\n", "\r"], "\n", $ics);
    $raw = explode("\n", $ics);
    $lines = [];
    foreach ($raw as $line) {
        if ($line === '') continue;
        if (($line[0] === ' ' || $line[0] === "\t") && !empty($lines)) {
            $lines[count($lines) - 1] .= substr($line, 1);
        } else {
            $lines[] = $line;
        }
    }

    $events = [];
    $cur = null;
    foreach ($lines as $line) {
        if ($line === 'BEGIN:VEVENT') { $cur = []; continue; }
        if ($line === 'END:VEVENT') {
            if ($cur !== null) {
                $ev = gcal_format_event($cur);
                if ($ev) $events[] = $ev;
            }
            $cur = null;
            continue;
        }
        if ($cur === null) continue;

        // Split "KEY[;PARAMS]:VALUE"
        $colon = strpos($line, ':');
        if ($colon === false) continue;
        $head  = substr($line, 0, $colon);
        $value = substr($line, $colon + 1);
        $semi  = strpos($head, ';');
        if ($semi !== false) {
            $key    = substr($head, 0, $semi);
            $params = substr($head, $semi + 1);
        } else {
            $key = $head; $params = '';
        }
        $cur[strtoupper($key)] = ['value' => $value, 'params' => $params];
    }

    // Sort by start ascending
    usort($events, function($a, $b){ return strcmp($a['start_date'], $b['start_date']); });
    return $events;
}

function gcal_unescape_text(string $s): string {
    // Order matters: \\ must be handled last so \n etc. aren't re-escaped.
    $s = str_replace(['\\N', '\\n'], "\n", $s);
    $s = str_replace(['\\,', '\\;'], [',', ';'], $s);
    $s = str_replace('\\\\', '\\', $s);
    return $s;
}

function gcal_parse_dt(string $value, string $params): ?string {
    // Trim TZID etc. from params; we just need YYYY-MM-DD.
    $v = trim($value);
    if ($v === '') return null;
    // YYYYMMDD (all-day)
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $v, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
    // YYYYMMDDTHHMMSS(Z)
    if (preg_match('/^(\d{4})(\d{2})(\d{2})T\d{6}Z?$/', $v, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
    return null;
}

function gcal_format_event(array $f): ?array {
    $summary = isset($f['SUMMARY']) ? gcal_unescape_text($f['SUMMARY']['value']) : '';
    $desc    = isset($f['DESCRIPTION']) ? gcal_unescape_text($f['DESCRIPTION']['value']) : '';
    $dtstart = isset($f['DTSTART']) ? gcal_parse_dt($f['DTSTART']['value'], $f['DTSTART']['params']) : null;
    $dtend   = isset($f['DTEND'])   ? gcal_parse_dt($f['DTEND']['value'],   $f['DTEND']['params'])   : null;
    $uid     = isset($f['UID']) ? $f['UID']['value'] : '';

    if (!$dtstart) return null;

    // iCal DTEND for all-day events is exclusive (day after last day).
    // Shift back by one day so it matches how the app stores check_out.
    if ($dtend) {
        $isAllDay = isset($f['DTSTART']['params']) && strpos($f['DTSTART']['params'], 'VALUE=DATE') !== false;
        if ($isAllDay) {
            $d = DateTime::createFromFormat('Y-m-d', $dtend);
            if ($d) { $d->modify('-1 day'); $dtend = $d->format('Y-m-d'); }
        }
    } else {
        $dtend = $dtstart;
    }

    return [
        'uid'         => $uid,
        'title'       => $summary !== '' ? $summary : '(untitled)',
        'description' => $desc,
        'start_date'  => $dtstart,
        'end_date'    => $dtend,
        'source'      => 'gcal',
    ];
}
