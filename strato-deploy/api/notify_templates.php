<?php
// ============================================================
// Built-in email bodies for the annual cycle.
//
// These are DEFAULTS. Anything stored in fargny_email_templates wins, so
// wording can be corrected from the admin page without a deploy.
//
// One message carries both languages, Dutch first, then English after a
// divider — the family has no per-member language preference, and one
// mail that everyone can read beats two that half of them ignore.
//
// Placeholders are {braced} and filled by notify_render().
// ============================================================

function notify_default_templates(): array {
    return [

    // ---- 1. The kickoff, 20 October --------------------------------
    'kickoff' => [
        'subject_nl' => 'Fargny {year}: zo gaat het boeken dit jaar',
        'subject_en' => 'Fargny {year}: how booking works this year',
        'body_nl' => '
<p style="font-size:15px;color:#2C1810;">Beste {first_name},</p>

<p style="font-size:15px;color:#2C1810;">
Vanaf dit jaar loopt het boeken van Fargny via het boekingssysteem in plaats van
de gedeelde agenda. Hieronder staat hoe het seizoen <strong>{year}</strong> verloopt,
zodat je weet wat er wanneer gebeurt.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">De drie soorten boekingen</h3>
<table style="width:100%;border-collapse:collapse;font-size:14px;color:#2C1810;">
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Stamboeking</strong></td>
      <td style="padding:6px 0;">Eén per stam per jaar, gemaakt door een aandeelhouder van die stam.</td></tr>
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Jokerboeking</strong></td>
      <td style="padding:6px 0;">Eén per persoon per jaar. Hiermee kun je buiten de normale
      termijn van drie maanden vooruit boeken.</td></tr>
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Gewone boeking</strong></td>
      <td style="padding:6px 0;">Alles binnen de regel van drie maanden vooruit. Zoveel als je wilt.</td></tr>
</table>

<p style="font-size:14px;color:#2C1810;margin-top:14px;">
<strong>Let op:</strong> wie de stamboeking op zijn of haar naam zet, gebruikt daarmee
de eigen jokerboeking. Die persoon kan dat jaar geen aparte joker meer inzetten.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Vormen</h3>
<p style="font-size:14px;color:#2C1810;">
Stam- en jokerboekingen hebben één van drie vormen:
<strong>vrijdag&ndash;vrijdag</strong>, <strong>maandag&ndash;vrijdag</strong> of
<strong>vrijdag&ndash;maandag</strong>. Een gewone boeking is vrij in te delen.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Wijzigen</h3>
<p style="font-size:14px;color:#2C1810;">
Je kunt je boeking verschuiven <strong>binnen dezelfde week</strong> waarin je hem hebt
gemaakt. Maximaal <strong>zeven nachten</strong> in totaal. Korter maken mag altijd.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Als twee boekingen botsen</h3>
<p style="font-size:14px;color:#2C1810;">
De stam- en jokerrondes zijn blind: je ziet niet wat anderen kiezen. Het kan dus
gebeuren dat twee stammen dezelfde week kiezen. Dat lossen jullie onderling op &mdash;
niemand wijst iets toe. <strong>Degene die een stap terug doet, past de eigen boeking aan
in het systeem.</strong> Komen jullie er niet uit, dan gaat het naar het bestuur.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Wat je van ons krijgt</h3>
<table style="width:100%;border-collapse:collapse;font-size:14px;color:#2C1810;">
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_clan_open}</strong></td>
      <td style="padding:5px 0;">De stamronde opent.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_clan_publish}</strong></td>
      <td style="padding:5px 0;">Alle stamboekingen worden zichtbaar; botsingen worden gemeld.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_priority_open}</strong></td>
      <td style="padding:5px 0;">De stamkalender ligt vast en de jokerronde opent.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_priority_publish}</strong></td>
      <td style="padding:5px 0;">De hele kalender wordt openbaar.</td></tr>
</table>

<p style="font-size:14px;color:#2C1810;margin-top:14px;">
De stamronde loopt van <strong>{d_clan_open}</strong> tot en met <strong>{d_clan_close}</strong>.
Botsingen tussen stamboekingen los je op vóór <strong>{d_clan_conflict_end}</strong>;
botsingen tussen jokerboekingen vóór <strong>{d_priority_conflict_end}</strong>.</p>',

        'body_en' => '
<p style="font-size:15px;color:#2C1810;">Dear {first_name},</p>

<p style="font-size:15px;color:#2C1810;">
From this year, Fargny is booked through the booking system rather than the shared
calendar. Here is how the <strong>{year}</strong> season runs, so you know what happens when.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">The three kinds of booking</h3>
<table style="width:100%;border-collapse:collapse;font-size:14px;color:#2C1810;">
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Clan booking</strong></td>
      <td style="padding:6px 0;">One per clan per year, made by a shareholder of that clan.</td></tr>
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Priority booking</strong></td>
      <td style="padding:6px 0;">One per person per year. It lets you book beyond the normal
      three-month horizon.</td></tr>
  <tr><td style="padding:6px 10px 6px 0;vertical-align:top;white-space:nowrap;"><strong>Regular booking</strong></td>
      <td style="padding:6px 0;">Anything within the three-month rule. As many as you like.</td></tr>
</table>

<p style="font-size:14px;color:#2C1810;margin-top:14px;">
<strong>Note:</strong> whoever puts the clan booking in their name uses up their own
priority booking by doing so. That person cannot place a separate priority booking
that year.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Formats</h3>
<p style="font-size:14px;color:#2C1810;">
Clan and priority bookings take one of three shapes:
<strong>Friday&ndash;Friday</strong>, <strong>Monday&ndash;Friday</strong> or
<strong>Friday&ndash;Monday</strong>. A regular booking can be any dates you like.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">Changing a booking</h3>
<p style="font-size:14px;color:#2C1810;">
You can move your stay <strong>within the same week</strong> you booked it in, up to
<strong>seven nights</strong> in total. Shortening is always allowed.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">When two bookings clash</h3>
<p style="font-size:14px;color:#2C1810;">
The clan and priority rounds are blind: you cannot see what anyone else has chosen.
So two clans may well pick the same week. You sort that out between yourselves &mdash;
nobody allocates. <strong>Whoever steps back edits their own booking in the system.</strong>
If you cannot agree, it goes to the board.</p>

<h3 style="font-size:15px;color:#2C1810;margin:22px 0 6px 0;">What you will receive</h3>
<table style="width:100%;border-collapse:collapse;font-size:14px;color:#2C1810;">
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_clan_open}</strong></td>
      <td style="padding:5px 0;">The clan round opens.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_clan_publish}</strong></td>
      <td style="padding:5px 0;">All clan bookings become visible; clashes are flagged.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_priority_open}</strong></td>
      <td style="padding:5px 0;">The clan calendar is final and the priority round opens.</td></tr>
  <tr><td style="padding:5px 10px 5px 0;white-space:nowrap;"><strong>{d_priority_publish}</strong></td>
      <td style="padding:5px 0;">The whole calendar becomes public.</td></tr>
</table>

<p style="font-size:14px;color:#2C1810;margin-top:14px;">
The clan round runs from <strong>{d_clan_open}</strong> to <strong>{d_clan_close}</strong>
inclusive. Clan clashes are resolved before <strong>{d_clan_conflict_end}</strong>;
priority clashes before <strong>{d_priority_conflict_end}</strong>.</p>',
    ],

    // ---- 2. The clan window opens, 15 November ----------------------
    'clan_open' => [
        'subject_nl' => 'De stamronde voor {year} is open tot en met {d_clan_close}',
        'subject_en' => 'The clan round for {year} is open until {d_clan_close}',
        'body_nl' => '
<p style="font-size:15px;color:#2C1810;">Beste {first_name},</p>

<p style="font-size:15px;color:#2C1810;">
De stamronde voor <strong>{year}</strong> is open. Je kunt boeken tot en met
<strong>{d_clan_close}</strong>.</p>

<p style="font-size:14px;color:#2C1810;">Kort samengevat:</p>
<ul style="font-size:14px;color:#2C1810;line-height:1.7;">
  <li>Eén stamboeking per stam per jaar.</li>
  <li>Vrijdag&ndash;vrijdag, maandag&ndash;vrijdag of vrijdag&ndash;maandag.</li>
  <li>De ronde is <strong>blind</strong>: tot {d_clan_publish} ziet niemand anders wat jij kiest,
      en jij niet wat zij kiezen. Kies dus de week die je echt wilt, niet de week waarvan
      je denkt dat hij vrij is.</li>
  <li>Wie de boeking op zijn of haar naam zet, gebruikt daarmee de eigen jokerboeking.</li>
</ul>

<p style="font-size:14px;color:#2C1810;">
Op <strong>{d_clan_publish}</strong> worden alle stamboekingen zichtbaar. Kiezen twee stammen
dezelfde week, dan krijgen jullie daar apart bericht over en lossen jullie het onderling
op vóór <strong>{d_clan_conflict_end}</strong>.</p>',

        'body_en' => '
<p style="font-size:15px;color:#2C1810;">Dear {first_name},</p>

<p style="font-size:15px;color:#2C1810;">
The clan round for <strong>{year}</strong> is open. You can book until and including
<strong>{d_clan_close}</strong>.</p>

<p style="font-size:14px;color:#2C1810;">In short:</p>
<ul style="font-size:14px;color:#2C1810;line-height:1.7;">
  <li>One clan booking per clan per year.</li>
  <li>Friday&ndash;Friday, Monday&ndash;Friday or Friday&ndash;Monday.</li>
  <li>The round is <strong>blind</strong>: until {d_clan_publish} nobody sees your choice and
      you do not see theirs. So book the week you actually want, not the one you think
      is still free.</li>
  <li>Whoever puts the booking in their name uses up their own priority booking.</li>
</ul>

<p style="font-size:14px;color:#2C1810;">
On <strong>{d_clan_publish}</strong> every clan booking becomes visible. If two clans picked
the same week you will each get a separate message about it, and you settle it between
yourselves before <strong>{d_clan_conflict_end}</strong>.</p>',
    ],

    ];
}

// Fill {placeholders}. Dates are rendered the way the family writes them
// ("15 november"), not as ISO strings — this is a letter, not a log line.
function notify_render(string $text, array $vars): string {
    $out = $text;
    foreach ($vars as $k => $v) $out = str_replace('{' . $k . '}', (string)$v, $out);
    return $out;
}

function notify_format_date(string $iso, string $lang): string {
    $monthsNl = [1=>'januari','februari','maart','april','mei','juni','juli',
                 'augustus','september','oktober','november','december'];
    $monthsEn = [1=>'January','February','March','April','May','June','July',
                 'August','September','October','November','December'];
    $t = strtotime($iso);
    if (!$t) return $iso;
    $d = (int)date('j', $t); $m = (int)date('n', $t);
    return $lang === 'nl' ? "$d {$monthsNl[$m]}" : "$d {$monthsEn[$m]}";
}

// Everything a template may refer to, for one recipient and one year.
function notify_vars(array $member, int $year, string $lang): array {
    $c = cycle_dates($year);
    $name  = trim((string)($member['display_name'] ?? ''));
    $first = $name !== '' ? preg_split('/\s+/', $name)[0] : ($lang === 'nl' ? 'familielid' : 'there');
    $vars  = ['year' => $year, 'first_name' => $first, 'full_name' => $name];
    foreach ($c as $k => $iso) {
        $vars['d_' . $k] = $iso ? notify_format_date($iso, $lang) : '';
        $vars['iso_' . $k] = $iso ?: '';
    }
    return $vars;
}

// Subject + body for one recipient: the stored template if one exists,
// otherwise the built-in default. Dutch and English in one message.
function notify_compose(string $key, array $member, int $year): array {
    $defaults = notify_default_templates();
    $stored   = template_get($key);
    $d        = $defaults[$key] ?? ['subject_nl'=>'','subject_en'=>'','body_nl'=>'','body_en'=>''];

    $pick = function (string $field) use ($stored, $d) {
        $v = $stored[$field] ?? '';
        return ($v !== null && $v !== '') ? $v : ($d[$field] ?? '');
    };

    $vNl = notify_vars($member, $year, 'nl');
    $vEn = notify_vars($member, $year, 'en');

    $subjectNl = notify_render($pick('subject_nl'), $vNl);
    $subjectEn = notify_render($pick('subject_en'), $vEn);
    // One subject line has to carry both; Dutch first, English after a dot.
    $subject   = $subjectNl !== '' && $subjectEn !== '' && $subjectNl !== $subjectEn
                 ? $subjectNl . ' · ' . $subjectEn
                 : ($subjectNl ?: $subjectEn);

    $body = notify_render($pick('body_nl'), $vNl)
          . notify_footer_html('nl')
          . notify_lang_divider()
          . notify_render($pick('body_en'), $vEn)
          . notify_footer_html('en');

    // No subtitle: the wrapper already shows "Fargny", and the body
    // carries its own headings in both languages.
    return [$subject, email_template('', $body)];
}
