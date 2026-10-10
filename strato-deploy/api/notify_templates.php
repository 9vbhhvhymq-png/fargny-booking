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
    // The family's own wording, used as written. Dates are literal rather
    // than {placeholders} because this is their letter, not generated
    // prose; the admin page can edit it if a boundary ever moves.
    // The standard footer is suppressed for this one — the text already
    // carries the first-year note and the contact line, and saying either
    // twice reads like a machine wrote it.
    'kickoff' => [
        'footer'     => 'calendar_only',
        'subject_nl' => 'Zo werkt het boeken van Fargny dit jaar – en wat je van ons kunt verwachten',
        'subject_en' => 'How booking Fargny works this year – and what to expect from us',
        'body_nl' => '
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Lieve familie,</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">Vanaf dit jaar verloopt het boeken van Fargny via het nieuwe boekingssysteem. Je maakt,
wijzigt en annuleert je boekingen voortaan zelf, en het systeem mailt je bij elke stap. In deze
mail leggen we uit hoe het werkt en wat je de komende maanden kunt verwachten.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">Allereerst veel dank aan Mari&euml;lle, die onze boekingen ruim vijftien jaar met de hand
heeft bijgehouden. Zij helpt nog met bestaande boekingen tot het einde van dit jaar. Vanaf
1 januari loopt alles via het systeem.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Drie soorten boekingen</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Stamboeking:</strong> elke stam kan &eacute;&eacute;n verblijf per jaar boeken:
  een week (vr&ndash;vr), een midweek (ma&ndash;vr) of een weekend (vr&ndash;ma). De boeking staat
  op naam van &eacute;&eacute;n aandeelhouder van die stam, die het hele verblijf aanwezig moet
  zijn. Niet elke stam hoeft er gebruik van te maken.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Jokerboeking:</strong> elke aandeelhouder van 21 jaar en ouder heeft
  &eacute;&eacute;n jokerboeking per jaar, met dezelfde drie vormen. Net als de stamboeking kan
  deze verder dan drie maanden vooruit worden gemaakt. <strong>Let op:</strong> degene op wiens
  naam de stamboeking staat, heeft daarmee de eigen jokerboeking van dat jaar gebruikt.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Gewone boeking:</strong> alle andere verblijven kun je tot drie maanden
  vooruit boeken, wie het eerst komt het eerst maalt.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">De kalender voor het komende jaar</h3>
<table style="width:100%;border-collapse:collapse;margin:8px 0 16px 0;">
  <tr><th style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"align="left">Wanneer</th><th style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;"align="left">Wat er gebeurt</th></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>15&ndash;29 november</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">De stamboekingen gaan open. Wat je invoert blijft verborgen tot het venster
      sluit, dus haasten is niet nodig.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>30 november</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">De stamboekingen verschijnen op de kalender. Controleer je eigen invoer. Hebben
      twee stammen dezelfde data gekozen, overleg dan samen; wie een stap terug doet, past de
      eigen boeking aan in het systeem <strong>v&oacute;&oacute;r 10 december</strong>.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>10 december</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">De stamkalender ligt vast en de jokerboekingen gaan open. Tot en met
      31 december is jouw jokerboeking alleen voor jou zichtbaar.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>1 januari</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">De volledige kalender wordt openbaar. Overlapt jouw jokerboeking met die van een
      ander, los het dan samen op <strong>v&oacute;&oacute;r 31 januari</strong>. Wie een stap
      terug doet, past de eigen boeking aan in het systeem. In januari kunnen alleen boekingen
      die overlappen worden gewijzigd.</td></tr>
</table>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">Je hoeft je jokerboeking niet in december te gebruiken. Je kunt hem later in het jaar op
elk moment inzetten, en hij staat dan meteen op de kalender.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">Komen twee partijen er samen niet uit, neem dan contact op met het bestuur.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">De mails die je krijgt</h3>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">We houden het bij een paar:</p>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>15 november:</strong> de stamboekingen zijn open.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>30 november:</strong> de stamboekingen zijn gepubliceerd, met een controle van je eigen invoer.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>10 december:</strong> de stamkalender ligt vast en de jokerboekingen zijn open.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>1 januari:</strong> de volledige kalender is openbaar, met een controle van je eigen invoer.</li>
</ul>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Je krijgt ook een mail als iemand een verblijf annuleert en de data vrijkomen.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Wijzigen of annuleren</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">Je kunt een stam- of jokerboeking <strong>wijzigen</strong> binnen de week waarin je
  hebt geboekt, zolang de dagen vrij zijn.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">Je mag je verblijf altijd <strong>korter</strong> maken.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">Een stam- of jokerverblijf kan nooit <strong>langer</strong> zijn dan &eacute;&eacute;n
  week in totaal.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">Annuleer je <strong>helemaal</strong>, dan krijg je je jokerboeking terug. De
  vrijgekomen data worden eerst een week lang aangeboden aan familieleden die hun jokerboeking
  nog niet hebben gebruikt. Daarna kan iedereen ze boeken.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Let op</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Fargny 100 jaar:</strong> het huis is gereserveerd voor de jubileumviering
  van maandag 16 augustus tot en met woensdag 25 augustus 2027.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Eerste jaar:</strong> dit is het eerste jaar dat we het zo doen, dus
  controleer je boekingen op de kalender.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Ontbrekende familieleden:</strong> ken je een aandeelhouder die deze mail niet
  heeft gekregen, vraag die dan om zich met een e-mailadres te registreren in het
  boekingssysteem. Anders ontvangt hij of zij deze updates niet.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Vragen of klopt er iets niet?</h3>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Ziet iets er verkeerd of onduidelijk uit, of loop je vast in het systeem, stuur me dan
gewoon een WhatsApp-bericht op
<a href="https://wa.me/4915788611151" style="color:#B85042;font-weight:600;">+49 157 88611151</a>.
Idee&euml;n voor verbetering zijn ook van harte welkom. We horen het liever vroeg dan laat.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">Tot in Fargny!</p>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Moritz</p>
',
        'body_en' => '
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Dear family,</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">This year, booking Fargny moves into the new booking system. From now on you make,
change and cancel your own bookings there, and the system emails you at each step. This
email explains how it works and what to expect over the coming months.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">First, a big thank you to Mari&euml;lle, who has managed our bookings by hand for more
than fifteen years. She will still help with existing bookings until the end of this year.
From 1 January, everything runs through the system.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Three kinds of booking</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Clan booking:</strong> Each clan can book one stay per year: a week
  (Fri&ndash;Fri), a midweek (Mon&ndash;Fri) or a weekend (Fri&ndash;Mon). It is made in the
  name of one shareholder of that clan, who must be there for the whole stay. Not every clan
  has to use it.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Priority booking:</strong> Every shareholder aged 21+ has one priority
  booking per year, with the same three formats. Like the clan booking, it can be made more
  than three months in advance. <strong>Note:</strong> the person whose name is on the clan
  booking has used their priority booking for that year.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Regular booking:</strong> All other stays can be booked up to three months
  in advance, first come, first served.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">The calendar for the coming year</h3>
<table style="width:100%;border-collapse:collapse;margin:8px 0 16px 0;">
  <tr><th style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"align="left">When</th><th style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;"align="left">What happens</th></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>15&ndash;29 November</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">Clan bookings open. What you submit stays hidden until the window closes, so
      there is no need to rush.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>30 November</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">Clan bookings appear on the calendar. Please check your entry. If two clans
      picked the same dates, talk to each other; whoever steps back changes their booking in
      the system <strong>before 10 December</strong>.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>10 December</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">The clan calendar is final and priority bookings open. Until 31 December,
      priority bookings are visible only to you.</td></tr>
  <tr><td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;white-space:nowrap;vertical-align:top;"><strong>1 January</strong></td>
      <td style="padding:8px 12px;border-bottom:1px solid #E0D8CF;font-size:14px;color:#2C1810;vertical-align:top;">The full calendar is public. If your priority booking overlaps with someone
      else&rsquo;s, sort it out together <strong>by 31 January</strong>. Whoever steps back
      changes their booking in the system. During January, only bookings that overlap can be
      changed.</td></tr>
</table>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">You don&rsquo;t have to use your priority booking in December. You can place it any time
later in the year, and it shows on the calendar straight away.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">If two parties can&rsquo;t agree on a clash, please contact the board.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">The emails you&rsquo;ll receive</h3>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">We&rsquo;ll keep it to a few:</p>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>15 November:</strong> clan bookings are open.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>30 November:</strong> clan bookings are published, with a check of your own entry.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>10 December:</strong> the clan calendar is final and priority bookings are open.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>1 January:</strong> the full calendar is public, with a check of your own entry.</li>
</ul>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">You&rsquo;ll also get an email if someone cancels a stay and the dates become free.</p>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Changing or cancelling</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">You can <strong>change</strong> a clan or priority booking within the week you
  booked, as long as the days are free.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">You can always <strong>shorten</strong> your stay.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">A clan or priority stay can never be <strong>longer</strong> than one week in total.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;">If you <strong>cancel</strong> completely, you get your priority booking back. The
  freed dates are first offered for one week to family members who haven&rsquo;t used their
  priority booking yet. After that, anyone can book them.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Please note</h3>
<ul style="padding-left:20px;margin:0;">
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Fargny 100 years:</strong> the house is reserved for the anniversary
  celebration from Monday 16 August to Wednesday 25 August 2027.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>First year:</strong> this is the first year we&rsquo;re doing it this way,
  so please check your bookings on the calendar.</li>
  <li style="font-size:15px;color:#2C1810;line-height:1.6;margin-bottom:8px;"><strong>Missing relatives:</strong> if you know a shareholder who didn&rsquo;t
  receive this email, ask them to register their email address in the booking system.
  Otherwise they won&rsquo;t get these updates.</li>
</ul>

<h3 style="font-size:15px;color:#2C1810;margin:24px 0 8px 0;">Questions or something not right?</h3>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">If something looks wrong or unclear, or you get stuck in the system, just send me a
WhatsApp message at <a href="https://wa.me/4915788611151" style="color:#B85042;font-weight:600;">+49 157 88611151</a>.
Ideas for improvement are very welcome too. We&rsquo;d much rather hear it early.</p>

<p style="font-size:15px;color:#2C1810;line-height:1.55;">See you in Fargny!</p>
<p style="font-size:15px;color:#2C1810;line-height:1.55;">Moritz</p>
',
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

    // A template may ask for a reduced footer. The kickoff does: its own
    // text already carries the first-year note and the contact details,
    // and repeating them reads like a machine stitched the mail together.
    $footerMode = $d['footer'] ?? 'full';
    $footNl = $footerMode === 'calendar_only' ? notify_calendar_link_html('nl') : notify_footer_html('nl');
    $footEn = $footerMode === 'calendar_only' ? notify_calendar_link_html('en') : notify_footer_html('en');

    $body = notify_render($pick('body_nl'), $vNl)
          . $footNl
          . notify_lang_divider()
          . notify_render($pick('body_en'), $vEn)
          . $footEn;

    // No subtitle: the wrapper already shows "Fargny", and the body
    // carries its own headings in both languages.
    return [$subject, email_template('', $body)];
}
