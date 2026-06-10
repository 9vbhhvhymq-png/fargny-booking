-- ============================================================
-- add-moritz.sql — Standalone phpMyAdmin migration
-- Ensures Moritz Fromageot is in fargny_shareholders (branch_id=9)
-- and that his admin account exists with the correct identity.
-- Safe to run multiple times (fully idempotent).
-- ============================================================

SET NAMES utf8mb4;

-- 1. Rename legacy entry if it exists
UPDATE `fargny_shareholders`
SET `full_name` = 'Moritz Fromageot',
    `branch_id` = 9
WHERE `full_name` = 'Moritz (1992)';

-- 2. Insert if still missing
INSERT INTO `fargny_shareholders` (`full_name`, `branch_id`)
SELECT 'Moritz Fromageot', 9
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `fargny_shareholders`
    WHERE `full_name` = 'Moritz Fromageot'
);

-- 3. Fix branch if wrong
UPDATE `fargny_shareholders`
SET `branch_id` = 9
WHERE `full_name` = 'Moritz Fromageot'
  AND `branch_id` != 9;

-- 4. Migrate old admin@fargny.org to Moritz's real identity
UPDATE `fargny_users`
SET `display_name` = 'Moritz Fromageot',
    `email`        = 'moritz@fromageot.eu',
    `branch_id`    = 9,
    `is_admin`     = 1
WHERE `email` = 'admin@fargny.org';

-- 5. Ensure admin account exists for moritz@fromageot.eu
--    Password hash below = bcrypt('admin'). Change via the app after first login.
INSERT INTO `fargny_users` (`display_name`, `email`, `password_hash`, `branch_id`, `is_admin`)
SELECT 'Moritz Fromageot',
       'moritz@fromageot.eu',
       '$2y$12$ffYjNYegUThZ1Hnm89wptu26Z78thp3oUrak4kjXG3bumHxqqe1Cy',
       9,
       1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `fargny_users`
    WHERE `email` = 'moritz@fromageot.eu'
);

-- 6. Ensure is_admin = 1
UPDATE `fargny_users`
SET `is_admin` = 1
WHERE `email` = 'moritz@fromageot.eu';

-- 7. Link shareholder row to user account
UPDATE `fargny_shareholders` s
JOIN `fargny_users` u ON u.`email` = 'moritz@fromageot.eu'
SET s.`user_id` = u.`id`
WHERE s.`full_name` = 'Moritz Fromageot'
  AND (s.`user_id` IS NULL OR s.`user_id` != u.`id`);
