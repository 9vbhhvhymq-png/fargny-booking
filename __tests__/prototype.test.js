/**
 * Tests for prototype/fargny-booking.jsx
 *
 * The prototype is a standalone browser JSX file. These tests cover the
 * pure utility functions and booking business logic by reimplementing them
 * directly from source — no bundler needed.
 */

// ─── Utility functions (copied verbatim from prototype) ─────────────────────

const MONTHS = {
  en: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
  nl: ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
};

function toISO(date) {
  return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
}

function generateWeeks(year) {
  const weeks = [];
  let d = new Date(year, 0, 1);
  while (d.getDay() !== 6) d.setDate(d.getDate() + 1);
  let weekNum = 1;
  while (d.getFullYear() <= year) {
    const start = new Date(d);
    const end = new Date(d); end.setDate(end.getDate() + 6);
    if (start.getFullYear() !== year) break;
    weeks.push({ id: `${year}-W${String(weekNum).padStart(2,'0')}`, weekNum, start: toISO(start), end: toISO(end), month: start.getMonth() });
    d.setDate(d.getDate() + 7); weekNum++;
  }
  return weeks;
}

function formatDate(iso, lang) {
  if (!iso) return '';
  const p = iso.split('-');
  return `${parseInt(p[2])} ${MONTHS[lang][parseInt(p[1])-1]}`;
}

function formatFullDate(iso, lang) {
  if (!iso) return '';
  const p = iso.split('-');
  return `${parseInt(p[2])} ${MONTHS[lang][parseInt(p[1])-1]} ${p[0]}`;
}

function phaseColor(phase) {
  return phase === 'clan' ? '#B85042' : phase === 'priority' ? '#C4853B' : '#4A7C59';
}

function isPhaseActive(phaseId, phaseConfig) {
  if (!phaseConfig) return true;
  const today = new Date().toISOString().split('T')[0];
  if (phaseId === 'clan') return today >= phaseConfig.clan_start && today <= phaseConfig.clan_end;
  if (phaseId === 'priority') return today >= phaseConfig.priority_start && today <= phaseConfig.priority_end;
  return today >= phaseConfig.regular_start;
}

// ─── Booking rule helpers (extracted from WeekRow render logic in prototype) ─

/**
 * Determines if a user can book a given week in a given phase.
 * Returns null if allowed, or a string reason key if blocked.
 */
function getBookingBlockReason(week, user, bookings, phase) {
  const weekBookings = bookings.filter(b => b.week_id === week.id);

  // Already booked this week
  if (weekBookings.some(b => b.user_id === user.id)) return 'alreadyBookedWeek';

  if (phase === 'clan') {
    // Only one booking per branch in clan phase
    const branchClanBooking = bookings.find(b => b.phase === 'clan' && b.branch_id === user.branch_id);
    if (branchClanBooking) {
      if (branchClanBooking.user_id === user.id) return 'youAlreadyBooked';
      return 'branchAlreadyBooked';
    }
  }

  if (phase === 'priority') {
    // Only one booking per user in priority phase
    if (bookings.some(b => b.phase === 'priority' && b.user_id === user.id)) return 'youBookedPhase';
    // Only one booking per branch in priority phase
    if (bookings.some(b => b.phase === 'priority' && b.branch_id === user.branch_id)) return 'branchBookedPhase';
  }

  // Regular: unlimited
  return null;
}

// ─── Test data ───────────────────────────────────────────────────────────────

const MOCK_PHASE_CONFIG = {
  year: 2026,
  clan_start: '2025-11-01', clan_end: '2025-12-11', clan_reveal: '2025-12-12',
  priority_start: '2025-12-12', priority_end: '2025-12-30', priority_reveal: '2025-12-31',
  regular_start: '2026-01-01',
};

const FUTURE_PHASE_CONFIG = {
  year: 2027,
  clan_start: '2099-11-01', clan_end: '2099-12-11', clan_reveal: '2099-12-12',
  priority_start: '2099-12-12', priority_end: '2099-12-30', priority_reveal: '2099-12-31',
  regular_start: '2099-01-01',
};

const userSophie = { id: 3, name: 'Sophie Van der Grinten', branch_id: 2, is_admin: false };
const userThomas = { id: 4, name: 'Thomas Van der Grinten', branch_id: 2, is_admin: false };
const userAnna   = { id: 5, name: 'Anna Stam3', branch_id: 3, is_admin: false };

const week12 = { id: '2026-W12', weekNum: 12, start: '2026-03-21', end: '2026-03-27', month: 2 };
const week13 = { id: '2026-W13', weekNum: 13, start: '2026-03-28', end: '2026-04-03', month: 2 };
const week28 = { id: '2026-W28', weekNum: 28, start: '2026-07-11', end: '2026-07-17', month: 6 };

// ─── toISO ───────────────────────────────────────────────────────────────────

describe('toISO', () => {
  test('formats a date as YYYY-MM-DD', () => {
    expect(toISO(new Date(2026, 0, 1))).toBe('2026-01-01');
    expect(toISO(new Date(2026, 11, 31))).toBe('2026-12-31');
  });

  test('pads single-digit months and days', () => {
    expect(toISO(new Date(2026, 2, 5))).toBe('2026-03-05');
  });
});

// ─── generateWeeks ───────────────────────────────────────────────────────────

describe('generateWeeks', () => {
  const weeks2026 = generateWeeks(2026);

  test('first week starts on a Saturday', () => {
    const firstStart = new Date(weeks2026[0].start);
    expect(firstStart.getDay()).toBe(6); // 6 = Saturday
  });

  test('each week is 7 days long', () => {
    weeks2026.forEach(w => {
      const start = new Date(w.start);
      const end   = new Date(w.end);
      const diff  = (end - start) / (1000 * 60 * 60 * 24);
      expect(diff).toBe(6);
    });
  });

  test('week IDs are sequential and zero-padded', () => {
    expect(weeks2026[0].id).toBe('2026-W01');
    expect(weeks2026[9].id).toBe('2026-W10');
    expect(weeks2026[11].id).toBe('2026-W12');
  });

  test('all weeks belong to the requested year', () => {
    weeks2026.forEach(w => {
      expect(w.start.startsWith('2026')).toBe(true);
    });
  });

  test('generates a reasonable number of weeks (48–53)', () => {
    expect(weeks2026.length).toBeGreaterThanOrEqual(48);
    expect(weeks2026.length).toBeLessThanOrEqual(53);
  });

  test('week numbers are consecutive starting at 1', () => {
    weeks2026.forEach((w, i) => {
      expect(w.weekNum).toBe(i + 1);
    });
  });
});

// ─── formatDate ──────────────────────────────────────────────────────────────

describe('formatDate', () => {
  test('returns empty string for falsy input', () => {
    expect(formatDate('', 'en')).toBe('');
    expect(formatDate(null, 'en')).toBe('');
  });

  test('formats in English', () => {
    expect(formatDate('2026-03-21', 'en')).toBe('21 Mar');
    expect(formatDate('2026-07-01', 'en')).toBe('1 Jul');
  });

  test('formats in Dutch', () => {
    expect(formatDate('2026-03-21', 'nl')).toBe('21 Mrt');
    expect(formatDate('2026-05-01', 'nl')).toBe('1 Mei');
  });

  test('strips leading zeros from day', () => {
    expect(formatDate('2026-01-05', 'en')).toBe('5 Jan');
  });
});

// ─── formatFullDate ───────────────────────────────────────────────────────────

describe('formatFullDate', () => {
  test('includes the year', () => {
    expect(formatFullDate('2026-03-21', 'en')).toBe('21 Mar 2026');
  });

  test('returns empty string for falsy input', () => {
    expect(formatFullDate('', 'en')).toBe('');
  });

  test('uses Dutch month names', () => {
    expect(formatFullDate('2026-05-10', 'nl')).toBe('10 Mei 2026');
  });
});

// ─── phaseColor ──────────────────────────────────────────────────────────────

describe('phaseColor', () => {
  test('returns distinct colours for each phase', () => {
    const clan     = phaseColor('clan');
    const priority = phaseColor('priority');
    const regular  = phaseColor('regular');
    expect(clan).not.toBe(priority);
    expect(clan).not.toBe(regular);
    expect(priority).not.toBe(regular);
  });

  test('returns valid hex colour strings', () => {
    ['clan', 'priority', 'regular'].forEach(p => {
      expect(phaseColor(p)).toMatch(/^#[0-9A-Fa-f]{6}$/);
    });
  });
});

// ─── isPhaseActive ────────────────────────────────────────────────────────────

describe('isPhaseActive', () => {
  test('returns true when phaseConfig is null (no restriction)', () => {
    expect(isPhaseActive('clan', null)).toBe(true);
    expect(isPhaseActive('priority', null)).toBe(true);
    expect(isPhaseActive('regular', null)).toBe(true);
  });

  test('clan phase is closed (dates in the past)', () => {
    // MOCK_PHASE_CONFIG.clan_end is 2025-12-11, today is 2026-04-04
    expect(isPhaseActive('clan', MOCK_PHASE_CONFIG)).toBe(false);
  });

  test('priority phase is closed (dates in the past)', () => {
    expect(isPhaseActive('priority', MOCK_PHASE_CONFIG)).toBe(false);
  });

  test('regular phase is open (regular_start is 2026-01-01, today is after)', () => {
    expect(isPhaseActive('regular', MOCK_PHASE_CONFIG)).toBe(true);
  });

  test('all phases inactive when dates are in the far future', () => {
    expect(isPhaseActive('clan', FUTURE_PHASE_CONFIG)).toBe(false);
    expect(isPhaseActive('priority', FUTURE_PHASE_CONFIG)).toBe(false);
    expect(isPhaseActive('regular', FUTURE_PHASE_CONFIG)).toBe(false);
  });
});

// ─── Booking rules ────────────────────────────────────────────────────────────

describe('Booking rules — clan phase', () => {
  test('user can book a free week', () => {
    expect(getBookingBlockReason(week12, userSophie, [], 'clan')).toBeNull();
  });

  test('user cannot book the same week twice', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: userSophie.branch_id, phase: 'clan' },
    ];
    expect(getBookingBlockReason(week12, userSophie, bookings, 'clan')).toBe('alreadyBookedWeek');
  });

  test('user cannot book a second clan week (youAlreadyBooked)', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: userSophie.branch_id, phase: 'clan' },
    ];
    expect(getBookingBlockReason(week28, userSophie, bookings, 'clan')).toBe('youAlreadyBooked');
  });

  test('branch-mate cannot book clan week when branch already has one (branchAlreadyBooked)', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'clan' },
    ];
    // Thomas is in branch 2 (same as Sophie)
    expect(getBookingBlockReason(week28, userThomas, bookings, 'clan')).toBe('branchAlreadyBooked');
  });

  test('user from different branch can still book', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'clan' },
    ];
    expect(getBookingBlockReason(week28, userAnna, bookings, 'clan')).toBeNull();
  });
});

describe('Booking rules — priority phase', () => {
  test('user can book a free week', () => {
    expect(getBookingBlockReason(week12, userSophie, [], 'priority')).toBeNull();
  });

  test('user cannot book a second priority week (youBookedPhase)', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'priority' },
    ];
    expect(getBookingBlockReason(week28, userSophie, bookings, 'priority')).toBe('youBookedPhase');
  });

  test('branch-mate blocked when branch already has a priority week (branchBookedPhase)', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'priority' },
    ];
    expect(getBookingBlockReason(week28, userThomas, bookings, 'priority')).toBe('branchBookedPhase');
  });

  test('user from different branch is not blocked', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'priority' },
    ];
    expect(getBookingBlockReason(week28, userAnna, bookings, 'priority')).toBeNull();
  });
});

describe('Booking rules — regular phase', () => {
  test('user can book multiple weeks', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'regular' },
    ];
    expect(getBookingBlockReason(week28, userSophie, bookings, 'regular')).toBeNull();
  });

  test('user cannot double-book the same week', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'regular' },
    ];
    expect(getBookingBlockReason(week12, userSophie, bookings, 'regular')).toBe('alreadyBookedWeek');
  });

  test('multiple users can book the same regular week', () => {
    const bookings = [
      { id: 1, week_id: week12.id, user_id: userSophie.id, branch_id: 2, phase: 'regular' },
    ];
    expect(getBookingBlockReason(week12, userAnna, bookings, 'regular')).toBeNull();
  });
});

// ─── generateWeeks snapshot spot-checks ──────────────────────────────────────

describe('generateWeeks — specific week spot-checks for 2026', () => {
  const weeks = generateWeeks(2026);

  test('week 12 start date matches expected', () => {
    const w12 = weeks.find(w => w.id === '2026-W12');
    expect(w12).toBeDefined();
    expect(w12.start).toBe('2026-03-21');
    expect(w12.end).toBe('2026-03-27');
  });

  test('week 28 start date matches expected', () => {
    const w28 = weeks.find(w => w.id === '2026-W28');
    expect(w28).toBeDefined();
    expect(w28.start).toBe('2026-07-11');
  });
});

// ─── Profile helpers (reimplemented from strato-deploy) ─────────────────────
//
// PHP side: profile_normalise_phone / profile_trim_bio / profile_shape_visible
// in strato-deploy/api/profile.php.
// JS side:  waLink in strato-deploy/index.html.

const SKILL_SLUGS = ['garden','maintenance','cooking','pruning','cleaning'];

// Mirrors profile_normalise_phone(): store '+' followed by digits only.
function normalisePhone(raw) {
  const s = String(raw == null ? '' : raw).trim();
  if (s === '') return null;
  const hadPlus = s.indexOf('+') === 0;
  let digits = s.replace(/\D+/g, '');
  if (digits === '') return null;
  if (!hadPlus) {
    if (digits.indexOf('00') === 0) digits = digits.slice(2);
    else digits = digits.replace(/^0+/, '');
  }
  if (digits === '') return null;
  return '+' + digits.slice(0, 19);
}

// Mirrors waLink(): wa.me takes bare digits, no '+' or separators.
function waLink(phone) {
  const digits = String(phone || '').replace(/\D+/g, '');
  return digits ? 'https://wa.me/' + digits : null;
}

// Mirrors profile_trim_bio(): hard cap at 200 characters.
function trimBio(raw) {
  const bio = String(raw == null ? '' : raw).trim();
  if (bio === '') return null;
  return Array.from(bio).slice(0, 200).join('');
}

function nightsBetween(checkIn, checkOut) {
  return Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000);
}

// Mirrors the totals row of StaysList: past stays only.
function stayTotals(stays) {
  const past = stays.filter(s => !s.upcoming);
  const sorted = [...past].sort((a, b) => a.check_in_date < b.check_in_date ? -1 : 1);
  return {
    stays: past.length,
    nights: past.reduce((sum, s) => sum + nightsBetween(s.check_in_date, s.check_out_date), 0),
    first_visit: sorted.length ? sorted[0].check_in_date : null,
  };
}

// Mirrors profile_shape_visible(): hidden fields are dropped, never sent.
function shapeVisible(u) {
  const out = {
    user_id: u.id,
    display_name: u.display_name,
    branch_id: u.branch_id,
    pref_stay: u.pref_stay || 'none',
    skills: u.skills || [],
    stays_visible: !!u.vis_stays,
    is_self: false,
  };
  if (u.vis_photo_bio) { out.photo_path = u.photo_path || null; out.bio = u.bio || null; }
  if (u.vis_phone)     { out.phone_e164 = u.phone_e164 || null; out.email = u.email; }
  if (u.vis_town)      { out.home_town = u.home_town || null; }
  return out;
}

describe('phone normalisation to E.164', () => {
  test('keeps an already-international number', () => {
    expect(normalisePhone('+31612345678')).toBe('+31612345678');
  });

  test('strips spaces, dashes and brackets', () => {
    expect(normalisePhone('+49 (157) 8861-1151')).toBe('+4915788611151');
  });

  test('converts a 00 international prefix', () => {
    expect(normalisePhone('0049 157 88611151')).toBe('+4915788611151');
  });

  test('drops a national trunk zero rather than producing "+0..."', () => {
    expect(normalisePhone('06 12 34 56 78')).toBe('+612345678');
    expect(normalisePhone('06 12 34 56 78').startsWith('+0')).toBe(false);
  });

  test('returns null for empty or non-numeric input', () => {
    expect(normalisePhone('')).toBeNull();
    expect(normalisePhone('   ')).toBeNull();
    expect(normalisePhone('geen nummer')).toBeNull();
    expect(normalisePhone(null)).toBeNull();
  });

  test('never exceeds the 20-char column', () => {
    expect(normalisePhone('+' + '9'.repeat(40)).length).toBeLessThanOrEqual(20);
  });
});

describe('wa.me link builder', () => {
  test('drops the plus and all separators', () => {
    expect(waLink('+49 157 88611151')).toBe('https://wa.me/4915788611151');
  });

  test('returns null when there is no number', () => {
    expect(waLink(null)).toBeNull();
    expect(waLink('')).toBeNull();
    expect(waLink('+')).toBeNull();
  });

  test('round-trips a normalised number', () => {
    expect(waLink(normalisePhone('0049-157-88611151'))).toBe('https://wa.me/4915788611151');
  });
});

describe('bio truncation', () => {
  test('caps at 200 characters', () => {
    expect(trimBio('a'.repeat(250))).toHaveLength(200);
  });

  test('leaves a short bio untouched', () => {
    expect(trimBio('  Loves the garden  ')).toBe('Loves the garden');
  });

  test('empty input becomes null, not an empty string', () => {
    expect(trimBio('   ')).toBeNull();
    expect(trimBio(undefined)).toBeNull();
  });

  test('counts characters, not bytes, for accented text', () => {
    expect(Array.from(trimBio('é'.repeat(250))).length).toBe(200);
  });
});

describe('stay nights and totals', () => {
  test('counts nights between arrival and departure', () => {
    expect(nightsBetween('2026-10-16', '2026-10-23')).toBe(7);
    expect(nightsBetween('2026-11-13', '2026-11-16')).toBe(3);
  });

  test('a Friday-to-Friday week is seven nights', () => {
    expect(nightsBetween('2026-10-16', '2026-10-23')).toBe(7);
  });

  const stays = [
    { check_in_date: '2027-01-08', check_out_date: '2027-01-15', upcoming: true },
    { check_in_date: '2026-10-16', check_out_date: '2026-10-23', upcoming: false },
    { check_in_date: '2025-07-04', check_out_date: '2025-07-08', upcoming: false },
  ];

  test('totals cover past stays only', () => {
    expect(stayTotals(stays).stays).toBe(2);
  });

  test('nights sum across past stays', () => {
    expect(stayTotals(stays).nights).toBe(11);
  });

  test('first visit is the earliest past arrival', () => {
    expect(stayTotals(stays).first_visit).toBe('2025-07-04');
  });

  test('no stays yields zeroes and no first visit', () => {
    expect(stayTotals([])).toEqual({ stays: 0, nights: 0, first_visit: null });
  });

  test('an upcoming-only history has no first visit yet', () => {
    expect(stayTotals([stays[0]]).first_visit).toBeNull();
  });
});

describe('visibility filter for a non-self viewer', () => {
  const full = {
    id: 42, display_name: 'Mariëlle', branch_id: 3, email: 'm@example.org',
    phone_e164: '+31612345678', bio: 'Loves the garden', photo_path: 'uploads/u42.jpg',
    home_town: 'Utrecht', pref_stay: 'week', skills: ['garden'],
    vis_photo_bio: 1, vis_phone: 1, vis_town: 1, vis_stays: 0,
  };

  test('everything visible when all flags are on', () => {
    const v = shapeVisible(full);
    expect(v.bio).toBe('Loves the garden');
    expect(v.photo_path).toBe('uploads/u42.jpg');
    expect(v.phone_e164).toBe('+31612345678');
    expect(v.email).toBe('m@example.org');
    expect(v.home_town).toBe('Utrecht');
  });

  test('contact details are absent, not blank, when vis_phone is off', () => {
    const v = shapeVisible({ ...full, vis_phone: 0 });
    expect('phone_e164' in v).toBe(false);
    expect('email' in v).toBe(false);
  });

  test('photo and bio are absent when vis_photo_bio is off', () => {
    const v = shapeVisible({ ...full, vis_photo_bio: 0 });
    expect('bio' in v).toBe(false);
    expect('photo_path' in v).toBe(false);
  });

  test('home town is absent when vis_town is off', () => {
    expect('home_town' in shapeVisible({ ...full, vis_town: 0 })).toBe(false);
  });

  test('name, branch and stay preference are always shared', () => {
    const v = shapeVisible({ ...full, vis_photo_bio: 0, vis_phone: 0, vis_town: 0 });
    expect(v.display_name).toBe('Mariëlle');
    expect(v.branch_id).toBe(3);
    expect(v.pref_stay).toBe('week');
  });

  test('stay history stays private by default', () => {
    expect(shapeVisible(full).stays_visible).toBe(false);
    expect(shapeVisible({ ...full, vis_stays: 1 }).stays_visible).toBe(true);
  });

  test('a member who filled nothing in still yields a usable card', () => {
    const blank = { id: 7, display_name: 'Nieuw Lid', branch_id: 1,
                    vis_photo_bio: 1, vis_phone: 1, vis_town: 1, vis_stays: 0 };
    const v = shapeVisible(blank);
    expect(v.display_name).toBe('Nieuw Lid');
    expect(v.pref_stay).toBe('none');
    expect(v.skills).toEqual([]);
    expect(v.bio).toBeNull();
  });

  test('the viewer never receives a visibility flag itself', () => {
    const v = shapeVisible(full);
    expect('vis_phone' in v).toBe(false);
    expect('vis_photo_bio' in v).toBe(false);
  });
});

describe('skill slugs', () => {
  test('unknown slugs are rejected', () => {
    const clean = (list) => list.filter(s => SKILL_SLUGS.includes(s));
    expect(clean(['garden', 'hacking', 'cooking'])).toEqual(['garden', 'cooking']);
  });
});
