-- ============================================================
-- add-family-member-role.sql — adds the Family Member role
-- ------------------------------------------------------------
-- Run once in phpMyAdmin. The application also applies this
-- automatically on first request, so this file is a manual
-- backup / audit trail. Safe to run more than once.
-- ============================================================

SET NAMES utf8mb4;

-- 1. Role column. Existing rows default to 'shareholder'.
ALTER TABLE `fargny_users`
  ADD COLUMN `role` ENUM('admin','shareholder','family_member')
  NOT NULL DEFAULT 'shareholder' AFTER `is_admin`;

-- 2. Which shareholder a family member belongs to (NULL for everyone else).
ALTER TABLE `fargny_users`
  ADD COLUMN `connected_shareholder_id` INT UNSIGNED DEFAULT NULL AFTER `role`;

ALTER TABLE `fargny_users`
  ADD KEY `fk_users_connected_sh` (`connected_shareholder_id`);

ALTER TABLE `fargny_users`
  ADD CONSTRAINT `fk_users_connected_sh`
  FOREIGN KEY (`connected_shareholder_id`)
  REFERENCES `fargny_shareholders` (`id`) ON DELETE SET NULL;

-- 3. Existing admins keep admin; everyone else is a shareholder.
UPDATE `fargny_users` SET `role` = 'admin'       WHERE `is_admin` = 1;
UPDATE `fargny_users` SET `role` = 'shareholder' WHERE `is_admin` = 0;
