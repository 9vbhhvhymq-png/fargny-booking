-- ============================================================
-- 011 — A branch for people who are not family
-- ============================================================
-- Every user row needs a branch (NOT NULL, foreign key), and the nine
-- family branches are the nine siblings. Somebody who is neither a
-- shareholder nor a relative — a caretaker, a neighbour, a friend of the
-- house who is meant to see the calendar — belongs to none of them.
--
-- So there is one more branch, numbered well outside the family range so
-- the family can still add a tenth branch of their own one day without
-- colliding with it. Grey, because it is not one of the family colours.
--
-- These accounts are created with role = 'family_member', which is what
-- enforces view-only everywhere (require_shareholder()); the branch is
-- what says they are not kin, and keeps them out of the family's own
-- announcements (notify_recipients()).
--
-- api/config.php applies this at runtime in ensure_guest_branch(), so a
-- deploy needs nobody to run this file. It is here as the record.

INSERT INTO `fargny_branches` (`id`, `name`, `color`) VALUES
  (99, 'Gast', '#8B7D6B')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
