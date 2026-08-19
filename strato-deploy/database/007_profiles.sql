-- ============================================================
-- 007_profiles.sql — family member profiles
-- ------------------------------------------------------------
-- Reference only. The application applies these columns itself
-- via ensure_profile_columns() in api/config.php, so a deploy is
-- never left half-migrated. Safe to run by hand in phpMyAdmin if
-- you would rather make the change explicitly; skip any ALTER
-- that reports the column already exists.
--
-- MySQL 5.7: no CHECK constraints, so bio length, phone format,
-- and the skill slug list are all enforced in PHP.
-- ============================================================

SET NAMES utf8mb4;

-- Profile content ------------------------------------------------------
ALTER TABLE `fargny_users`
  ADD COLUMN `photo_path`     VARCHAR(255) DEFAULT NULL COMMENT 'relative, e.g. uploads/u42.jpg',
  ADD COLUMN `bio`            VARCHAR(200) DEFAULT NULL COMMENT 'hard cap 200 chars, trimmed in PHP',
  ADD COLUMN `phone_e164`     VARCHAR(20)  DEFAULT NULL COMMENT 'E.164, e.g. +49157...',
  ADD COLUMN `pref_stay`      ENUM('week','midweek','weekend','none') NOT NULL DEFAULT 'none',
  ADD COLUMN `pref_season`    VARCHAR(40)  DEFAULT NULL,
  ADD COLUMN `home_town`      VARCHAR(80)  DEFAULT NULL,
  ADD COLUMN `languages`      VARCHAR(40)  DEFAULT NULL COMMENT 'comma-separated codes, e.g. nl,en,de',
  ADD COLUMN `household_size` TINYINT UNSIGNED DEFAULT NULL COMMENT 'usually travels with N people',
  ADD COLUMN `skills`         JSON DEFAULT NULL COMMENT 'slugs: garden, maintenance, cooking, pruning, cleaning';

-- Booking preference ---------------------------------------------------
ALTER TABLE `fargny_users`
  ADD COLUMN `open_to_share_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'pre-fills the booking dialog';

-- Visibility flags -----------------------------------------------------
-- Applied to logged-in family members only. Profile data never reaches
-- the public calendar regardless of these values.
ALTER TABLE `fargny_users`
  ADD COLUMN `vis_photo_bio` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `vis_phone`     TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `vis_town`      TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `vis_stays`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'stay history is private by default';
