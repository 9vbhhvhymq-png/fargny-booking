-- ============================================================
-- 009_booking_number.sql — the family's booking number
-- ------------------------------------------------------------
-- Reference only. The application adds the column, the index and the
-- backfill itself via ensure_booking_number_column() in api/config.php,
-- so a deploy is never left half-migrated. Safe to run by hand instead.
--
-- The number is the one the family already used on the old calendar:
-- two-digit year, then the booking's position in that year, written
-- after the member's name — "Jaap de Vries (26-31)".
--
-- It is STORED, not derived. Deriving it from row order would renumber
-- every later booking as soon as an earlier one is cancelled, and by
-- then the number has already gone out in a confirmation email.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `fargny_bookings`
  ADD COLUMN `booking_seq` SMALLINT UNSIGNED DEFAULT NULL
  COMMENT 'position within the year; shown as YY-N';

-- One number per position per year: two members booking in the same
-- instant cannot end up sharing a number.
ALTER TABLE `fargny_bookings`
  ADD UNIQUE KEY `uq_booking_seq` (`year`, `booking_seq`);

-- Existing bookings are numbered by the application, in the order they
-- were made: backfill_booking_numbers() in api/config.php runs on the
-- next page load and fills every row where booking_seq IS NULL. There is
-- no hand-written backfill here on purpose — MySQL 5.7 has no window
-- functions, and the variable-counter version is easy to get subtly
-- wrong in a way that misnumbers real bookings.
--
-- To check it worked:
--   SELECT year, COUNT(*) AS bookings, MAX(booking_seq) AS highest,
--          SUM(booking_seq IS NULL) AS unnumbered
--   FROM fargny_bookings GROUP BY year;
-- `unnumbered` should be 0, and `highest` should equal `bookings`
-- unless bookings have been deleted (numbers are never reused).
