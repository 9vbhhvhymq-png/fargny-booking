-- ============================================================
-- 010_priority_releases.sql — priority bookings that were given up
-- ------------------------------------------------------------
-- Reference only. The application creates this table itself via
-- ensure_priority_release_table() in api/config.php, so a deploy is
-- never left half-migrated. Safe to run by hand instead.
--
-- A member has one priority booking per year. Giving it up hands the
-- priority back — they may use it again on other dates — but the nights
-- it held are closed to them as an ordinary booking. Without that,
-- priority becomes a way to hold a prime week outside the 3-month
-- window, drop it, take the same week as a regular booking, and still
-- have the priority in hand.
--
-- A row is written when a priority booking actually disappears: an
-- approved cancellation, or an admin deletion. Moving a booking to new
-- dates is not a release — the member still holds their priority stay.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `fargny_priority_releases` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `year`           SMALLINT UNSIGNED NOT NULL,
  `check_in_date`  DATE NOT NULL COMMENT 'arrival of the released stay',
  `check_out_date` DATE NOT NULL COMMENT 'departure, exclusive',
  `released_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_year` (`user_id`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- To let a member back onto dates they released (they asked, you agreed),
-- delete their row:
--   DELETE FROM fargny_priority_releases WHERE user_id = ? AND check_in_date = ?;
-- An admin booking on their behalf bypasses the rule anyway.
