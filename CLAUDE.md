# CLAUDE.md

## What this is

A booking system for one shared family holiday house (Fargny), used by the
shareholders who co-own it and their relatives. Everything that ships is in
`strato-deploy/`: a single-file React frontend plus a plain-PHP/MySQL API on
Strato shared hosting.

## Commands

- `npm test` — jest, `__tests__/prototype.test.js` (72 tests); `npm install`
  pulls jest only, express/mongoose in package.json are unused.
- `php -l <file>` — the only PHP check. No linter, typecheck or build step
  (JSX is transpiled in the browser by Babel standalone).
- **No local run**: no dev server; `strato-deploy/` needs PHP + MySQL, and
  `npm start` (`node index.js`) is dead — that file does not exist.
- **Deploy = push to `main`**: `deploy.yml` mirrors `strato-deploy/` via
  `lftp mirror --reverse` (no `--delete`), excluding `.env`, `wipe-*.php`.
- **Verifying the frontend**: a parse proves nothing about runtime — two
  white-page outages shipped past parse-only checks. Render in jsdom (React 18
  UMD, `preset-react` `runtime:'classic'`; the browser does **not** use
  automatic), logged out and in, with weeks and a booking, clicking into the
  dialog. Empty data reaches almost no code.

## Architecture

- `strato-deploy/index.html` (~2450 lines) — the whole app: components,
  translations, date helpers, rules. `App()` holds nearly all state.
- `api/index.php` routes on the first path segment to
  `handle_<resource>($action, $id, $method)`, one file per resource;
  `api/config.php` (PDO, auth, JSON, roles, weeks, migrations) loads always.
- **Ignore `prototype/`, `backend/`, root `index.html`/`public-calendar.html`**
  — legacy. `__tests__/` tests a reimplementation of `prototype/`, not this.

## Data model (`database/001_schema.sql`, tables prefixed `fargny_`)

- `users` — display_name, email (unique), password_hash, branch_id, is_admin,
  plus `role` and ~15 profile columns added at **runtime**, not in 001. No
  `updated_at` column.
- `bookings` — week_id (`2026-W42`), year, user_id, branch_id, phase
  (clan|priority|regular), check_in_date/check_out_date (both nullable),
  open_to_share, remarks, linked_user_ids (JSON), cancellation_status.
- `payments` — one per booking; `guest_data` = 7 nights of `{child04,
  child59, adult}`. Also `shareholders`, `board_events`/`board_signups`.

### Invariants

1. **No two stays share a night.** Ranges are half-open `[check_in,
   check_out)`; touching on a changeover day is intended.
2. **A week runs Friday → Friday**, 7 nights (`WEEK_START_DOW = 5`, both sides).
3. **A regular stay is one of three shapes** from the week's Friday: week
   `+0/7n`, midweek `+3/4n`, weekend `+0/3n`. NULL dates = the whole week.
4. **Regular bookings open exactly 3 months before arrival**, rolling, all year.
5. **Clan = one week per branch per year; priority = one per user**, blind
   until reveal.
6. **Family members never create or change anything** — enforced server-side
   by `require_shareholder()`, not just hidden in the UI.
7. **No profile data in `bookings/public-calendar`**; hidden fields dropped in
   PHP.

## Conventions

- PHP `snake_case`; `json_success()`/`json_error()` then exit; every query a
  prepared statement. JS `camelCase`, inline styles, `COLORS`. Strings go in
  **both** `T.en` and `T.nl`.
- Dates are `YYYY-MM-DD` strings compared as strings. `parseISO()` builds a
  **local-midnight** Date; never `new Date(iso)`.

## Gotchas

- **`backfill_week_dates()` runs on every request** (`config.php:416`),
  rewriting *any* NULL-date booking using the **old Saturday** grid — a
  one-off migration that never stopped. See AUDIT.md #1.
- Change a week rule → change **both** `config.php` and `index.html`. Same for
  stay shapes (`stay_shapes()`/`STAY_SHAPES`) and rates (`RATES`/`HOUSE_FEE` vs
  literals in `payments.php`).
- `week.end` is the **last night**; departure is `end + 1`. The UI shows
  departure; some code still shows `end`. The cost grid is hardcoded to 7
  columns labelled Sat–Fri, from when weeks started on Saturday. Anonymised
  blind bookings have `user_id: 0` and no name but keep `open_to_share`.

## Before you finish

- [ ] `npm test` passes; `php -l` clean on every changed PHP file. Frontend
      changed? Rendered in jsdom, logged out **and** in, with weeks and a
      booking, clicked into the dialog.
- [ ] New string in **both** `T.en`/`T.nl`; new column via `ensure_*_columns()`
      **and** a `database/*.sql`; rule changed in **both** PHP and JS.
- [ ] Nothing personal added to `bookings/public-calendar`. Deploy green **and**
      the site loads — green only means files were copied.

## Open questions

- `package.json` declares express/mongoose and `main: index.js`; none exist.
  Is a Node backend still intended?
- `__tests__/` tests reimplemented `prototype/` logic, so it passes while
  `strato-deploy/` is broken. Retarget, or keep as a pure-logic suite?
- No rule for bookings predating a rule change (e.g. non-conforming stays).
- "Joker" bookings are referred to by members but exist nowhere in code.
