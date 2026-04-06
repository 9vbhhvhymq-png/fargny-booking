-- ============================================================
-- Fargny Booking System — Database Schema
-- Target: MySQL 5.7 on Strato.nl shared hosting
-- All tables prefixed with fargny_ to avoid WordPress conflicts
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- -----------------------------------------------------------
-- 1. BRANCHES
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_branches` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `color` VARCHAR(7) NOT NULL DEFAULT '#888888',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fargny_branches` (`id`, `name`, `color`) VALUES
  (1, 'Henk',      '#B85042'),
  (2, 'Daan',      '#4A7C59'),
  (3, 'Remy',      '#3B6B9E'),
  (4, 'Frans',     '#C4853B'),
  (5, 'Marianne',  '#7B5EA7'),
  (6, 'Tom',       '#2E8B8B'),
  (7, 'Lino',      '#C75B7A'),
  (8, 'Flop',      '#5B7553'),
  (9, 'Bertrand',  '#8B6F47')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `color`=VALUES(`color`);

-- -----------------------------------------------------------
-- 2. USERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `display_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `fk_users_branch` (`branch_id`),
  CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `fargny_branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin seed: email admin@fargny.org, password "admin"
INSERT INTO `fargny_users` (`id`, `display_name`, `email`, `password_hash`, `branch_id`, `is_admin`)
VALUES (1, 'Admin', 'admin@fargny.org', '$2y$10$8KzQ1Z5z5z5z5z5z5z5z5eY2J9QxW3Y5Z5z5z5z5z5z5z5z5z5z5.', 1, 1)
ON DUPLICATE KEY UPDATE `id`=`id`;
-- NOTE: Replace the hash above after first deploy by running:
--   UPDATE fargny_users SET password_hash = '$2y$10$...' WHERE id = 1;
-- Or let the install script generate it. The API config.php seeds this properly.

-- -----------------------------------------------------------
-- 3. SHAREHOLDERS (pre-seeded list of eligible registrants)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_shareholders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_shareholders_branch` (`branch_id`),
  KEY `fk_shareholders_user` (`user_id`),
  CONSTRAINT `fk_shareholders_branch` FOREIGN KEY (`branch_id`) REFERENCES `fargny_branches` (`id`),
  CONSTRAINT `fk_shareholders_user` FOREIGN KEY (`user_id`) REFERENCES `fargny_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fargny_shareholders` (`full_name`, `branch_id`) VALUES
  ('Moritz Fromageot', 1),
  ('Sophie Fromageot', 1),
  ('Fritz Fromageot', 1),
  ('Laurens van der Grinten', 2),
  ('Marielle van der Grinten', 2),
  ('Jan Henk', 1),
  ('Pieter Henk', 1),
  ('Anna Daan', 2),
  ('Willem Daan', 2),
  ('Lotte Remy', 3),
  ('Hugo Remy', 3),
  ('Clara Frans', 4),
  ('Thomas Frans', 4),
  ('Eva Marianne', 5),
  ('Marc Tom', 6),
  ('Julie Tom', 6),
  ('Stefan Lino', 7),
  ('Rosa Lino', 7),
  ('Paul Flop', 8),
  ('Marie Bertrand', 9)
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);

-- -----------------------------------------------------------
-- 4. SESSIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `fk_sessions_user` (`user_id`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `fargny_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 5. BOOKINGS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `week_id` VARCHAR(20) NOT NULL COMMENT 'e.g. 2026-W01',
  `year` SMALLINT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `phase` ENUM('clan','priority','regular') NOT NULL,
  `check_in_date` DATE DEFAULT NULL,
  `check_out_date` DATE DEFAULT NULL,
  `open_to_share` TINYINT(1) NOT NULL DEFAULT 0,
  `remarks` TEXT,
  `linked_user_ids` JSON DEFAULT NULL,
  `admin_booked` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_user_id` INT UNSIGNED DEFAULT NULL,
  `cancellation_status` ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  `booked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bookings_year` (`year`),
  KEY `idx_bookings_week` (`week_id`),
  KEY `fk_bookings_user` (`user_id`),
  KEY `fk_bookings_branch` (`branch_id`),
  KEY `fk_bookings_admin` (`admin_user_id`),
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `fargny_users` (`id`),
  CONSTRAINT `fk_bookings_branch` FOREIGN KEY (`branch_id`) REFERENCES `fargny_branches` (`id`),
  CONSTRAINT `fk_bookings_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `fargny_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 6. PHASE CONFIG
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_phase_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` SMALLINT UNSIGNED NOT NULL,
  `clan_start` DATE NOT NULL,
  `clan_end` DATE NOT NULL,
  `clan_reveal` DATE NOT NULL,
  `priority_start` DATE NOT NULL,
  `priority_end` DATE NOT NULL,
  `priority_reveal` DATE NOT NULL,
  `regular_start` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fargny_phase_config` (`year`, `clan_start`, `clan_end`, `clan_reveal`, `priority_start`, `priority_end`, `priority_reveal`, `regular_start`) VALUES
  (2026, '2025-11-01', '2025-12-15', '2025-12-15', '2025-12-15', '2025-12-31', '2025-12-31', '2026-01-01'),
  (2027, '2026-11-01', '2026-12-15', '2026-12-15', '2026-12-15', '2026-12-31', '2026-12-31', '2027-01-01')
ON DUPLICATE KEY UPDATE `clan_start`=VALUES(`clan_start`);

-- -----------------------------------------------------------
-- 7. BOARD EVENTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_board_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `description` TEXT,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_board_events_creator` (`created_by`),
  CONSTRAINT `fk_board_events_creator` FOREIGN KEY (`created_by`) REFERENCES `fargny_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 8. BOARD SIGNUPS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_board_signups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `signed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_user` (`event_id`, `user_id`),
  KEY `fk_signups_user` (`user_id`),
  CONSTRAINT `fk_signups_event` FOREIGN KEY (`event_id`) REFERENCES `fargny_board_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_signups_user` FOREIGN KEY (`user_id`) REFERENCES `fargny_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 9. PAYMENTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `guest_data` JSON DEFAULT NULL COMMENT 'Per-night grid of guest counts by age group',
  `house_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `person_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `cleaning_fee` DECIMAL(10,2) NOT NULL DEFAULT 70.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('not_paid','invoice_sent','paid') NOT NULL DEFAULT 'not_paid',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_booking` (`booking_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `fargny_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 10. FEEDBACK
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fargny_feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `responses` JSON DEFAULT NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_feedback_user` (`user_id`),
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `fargny_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
