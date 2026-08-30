-- ============================================================
-- 008_gate.sql — registration gate (shared family question)
-- ------------------------------------------------------------
-- Reference only. The application creates these tables itself via
-- ensure_gate_tables() in api/gate.php, so a deploy is never left
-- half-migrated. Safe to run by hand in phpMyAdmin instead.
--
-- The gate stays OFF until fargny_settings.gate_answer holds a
-- non-empty value, so applying this file alone changes nothing.
-- Set the answer from the admin panel (Admin → Registration gate).
-- ============================================================

SET NAMES utf8mb4;

-- Generic key/value settings, so later toggles need no new migration.
-- Keys used by the gate:
--   gate_question_en  the question shown in English
--   gate_question_nl  the question shown in Dutch
--   gate_answer       accepted answers, comma-separated; empty = gate off
CREATE TABLE IF NOT EXISTS `fargny_settings` (
  `name`       VARCHAR(64) NOT NULL,
  `value`      TEXT,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A pass handed out for one hour once the question is answered. Presented
-- as the X-Fargny-Gate header by the registration screen.
CREATE TABLE IF NOT EXISTS `fargny_gate_tokens` (
  `token`      CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wrong answers, for rate limiting: 10 per IP per 15 minutes. Rows older
-- than a day are deleted as new attempts come in.
CREATE TABLE IF NOT EXISTS `fargny_gate_attempts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`         VARCHAR(45) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The starting question. The answer is deliberately not in this file:
-- set it in the admin panel so it is not committed to a public repo.
INSERT INTO `fargny_settings` (`name`, `value`) VALUES
  ('gate_question_en', 'What was the name of the Donkey?'),
  ('gate_question_nl', 'Hoe heette de ezel?')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
