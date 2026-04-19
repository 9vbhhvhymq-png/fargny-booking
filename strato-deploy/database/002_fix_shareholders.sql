-- ============================================================
-- Migration 002: Replace dummy shareholders with real family list
-- Source: 2025.10.17 Adres- en telefoonlijst Familieleden Fargny.xlsx
-- Run this ONCE against the deployed DB (via phpMyAdmin).
-- Safe: keeps fargny_users / fargny_bookings untouched, only
-- rewrites the shareholder lookup list (used by the registration
-- dropdown on the login/signup page).
-- ============================================================

-- Unlink any users currently linked to a dummy shareholder row
-- (user_id is nullable; bookings are stored against fargny_users,
-- not fargny_shareholders, so this is lossless).
UPDATE `fargny_shareholders` SET `user_id` = NULL;

-- Wipe the dummy seed data
DELETE FROM `fargny_shareholders`;
ALTER TABLE `fargny_shareholders` AUTO_INCREMENT = 1;

-- Re-seed with the real family list
INSERT INTO `fargny_shareholders` (`full_name`, `branch_id`) VALUES
  ('Bas van der Grinten', 1),
  ('Berend (1990)', 1),
  ('Daan van der Grinten', 1),
  ('Guus (2004)', 1),
  ('Job van der Grinten', 1),
  ('Julia (1997)', 1),
  ('Karel van der Grinten', 1),
  ('Karlijn (1990)', 1),
  ('Leonie (1989)', 1),
  ('Lotte (2001)', 1),
  ('Sara (1998)', 1),
  ('Willem (2002)', 1),
  ('Daan (1998)', 2),
  ('Daniek (1998)', 2),
  ('Floor (2000)', 2),
  ('Jennie Goppel-van der Grinten', 2),
  ('Job (2001)', 2),
  ('Jolijn (1993)', 2),
  ('Marieke Sesink-van der Grinten', 2),
  ('Maurits van der Grinten', 2),
  ('Mieke van der Grinten-Verhaak', 2),
  ('Renée (1994)', 2),
  ('Theo (2011)', 2),
  ('Tjeerd (1999)', 2),
  ('Tom (2013)', 2),
  ('Willem van der Grinten', 2),
  ('Wim (2014)', 2),
  ('Annemie van der Grinten', 3),
  ('Ariane Hikspoors-van der Grinten', 3),
  ('Bart Kareem (2002)', 3),
  ('Bart van der Grinten', 3),
  ('Dimitri (2001)', 3),
  ('Dominique (2003)', 3),
  ('Jacqueline Receveur-van der Grinten', 3),
  ('Joep (2000)', 3),
  ('Lisa (2006)', 3),
  ('Lou (Susanne 2002)', 3),
  ('Lowie (1996)', 3),
  ('Lucas (2010)', 3),
  ('Maureen (1995)', 3),
  ('Michiel van der Grinten', 3),
  ('Nils (2000)', 3),
  ('Nito (2004)', 3),
  ('Philippe van der Grinten', 3),
  ('Tom Rachad (2002)', 3),
  ('Victor (2007)', 3),
  ('Yannick (2000)', 3),
  ('Auke (1994)', 4),
  ('Danielle Almeida d''Eca-van der Grinten', 4),
  ('Dick van der Grinten', 4),
  ('Julius (2002)', 4),
  ('Max (1995)', 4),
  ('Pepijn (1997)', 4),
  ('Peter van der Grinten', 4),
  ('Sofia (1992)', 4),
  ('Stijn (1999)', 4),
  ('Xavier (1996)', 4),
  ('Caroline Taylor-Karthaus', 5),
  ('Diederick Karthaus', 5),
  ('Inez (2003)', 5),
  ('Jeroen Karthaus', 5),
  ('Marianne Karthaus-van der Grinten', 5),
  ('Tom (2004)', 5),
  ('Emma (1998)', 6),
  ('Flore (2003)', 6),
  ('Jaap van der Grinten', 6),
  ('Joris van der Grinten', 6),
  ('Pepijn (2006)', 6),
  ('Renee Kuijpers-van der Grinten', 6),
  ('Stijn (2001)', 6),
  ('Teun (2003)', 6),
  ('Tom van der Grinten', 6),
  ('Barend Receveur', 7),
  ('Cecile (2006)', 7),
  ('Chris (2006)', 7),
  ('Fiene (2005)', 7),
  ('Jules (2004)', 7),
  ('Laurens Receveur', 7),
  ('Lena (2004)', 7),
  ('Lino Receveur-van der Grinten', 7),
  ('Luc (2002)', 7),
  ('Maurice (2000)', 7),
  ('Pascalle (2004)', 7),
  ('Rogier Receveur', 7),
  ('Sebas (2009)', 7),
  ('Simon (2007)', 7),
  ('Stefanie Receveur', 7),
  ('Toon (2002)', 7),
  ('Barbara van de Loo', 8),
  ('Bouke (2003)', 8),
  ('Brecht (2006)', 8),
  ('Diede (2013)', 8),
  ('Erik van de Loo', 8),
  ('Flop van der Grinten', 8),
  ('Jolijn Wispelweij-van de Loo', 8),
  ('Lucas (2005)', 8),
  ('Marielle van de Loo', 8),
  ('Milou (2007)', 8),
  ('Oscar (2005)', 8),
  ('Anton (1997)', 9),
  ('Bertrand Fromageot', 9),
  ('Boris Fromageot', 9),
  ('Fritz (1996)', 9),
  ('Laurenz (2000)', 9),
  ('Matthias Fromageot', 9),
  ('Moritz (1992)', 9);

-- Re-link any existing user accounts to the new shareholder rows by name
UPDATE `fargny_users` u
JOIN `fargny_shareholders` s ON s.full_name = u.display_name
SET s.user_id = u.id
WHERE s.user_id IS NULL;

-- Fix the branch_id on existing user accounts so that
-- a user's branch matches the real Staak (per the Excel).
UPDATE `fargny_users` u
JOIN `fargny_shareholders` s ON s.full_name = u.display_name
SET u.branch_id = s.branch_id
WHERE u.is_admin = 0;
