-- ============================================================
-- Migration 004: Ensure Moritz Fromageot is in fargny_shareholders
--                (branch 9 = Bertrand) and admin user is correct.
-- Safe to run multiple times (idempotent).
-- ============================================================

-- 1. Rename legacy 'Moritz (1992)' entry to his full name
UPDATE `fargny_shareholders`
SET `full_name` = 'Moritz Fromageot', `branch_id` = 9
WHERE `full_name` = 'Moritz (1992)';

-- 2. If 'Moritz Fromageot' still doesn't exist, insert him
INSERT INTO `fargny_shareholders` (`full_name`, `branch_id`)
SELECT 'Moritz Fromageot', 9
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `fargny_shareholders` WHERE `full_name` = 'Moritz Fromageot'
);

-- 3. Make sure branch_id is correct in case it existed with wrong branch
UPDATE `fargny_shareholders`
SET `branch_id` = 9
WHERE `full_name` = 'Moritz Fromageot' AND `branch_id` != 9;

-- 4. Update the admin user account to use moritz@fromageot.eu / Bertrand branch
--    Handles both the old admin@fargny.org seed and any existing Moritz account.
UPDATE `fargny_users`
SET `email`        = 'moritz@fromageot.eu',
    `display_name` = 'Moritz Fromageot',
    `branch_id`    = 9,
    `is_admin`     = 1
WHERE `email` IN ('admin@fargny.org', 'moritz@fromageot.eu')
   OR `display_name` LIKE '%Moritz%Fromageot%'
LIMIT 1;

-- 5. Link the shareholder row to the admin user (if not already linked)
UPDATE `fargny_shareholders` s
JOIN `fargny_users` u ON u.email = 'moritz@fromageot.eu'
SET s.`user_id` = u.`id`
WHERE s.`full_name` = 'Moritz Fromageot'
  AND s.`user_id` IS NULL;
