-- ============================================================
-- Migration 003: Grant admin rights to Moritz Fromageot
-- Run this ONCE against the deployed DB (via phpMyAdmin).
-- ============================================================

UPDATE `fargny_users`
SET `is_admin` = 1
WHERE `display_name` LIKE '%Moritz%Fromageot%'
   OR `email` LIKE 'moritz.fromageot%'
LIMIT 5;
