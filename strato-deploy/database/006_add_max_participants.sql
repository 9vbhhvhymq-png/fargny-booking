-- Add max_participants column to special events
ALTER TABLE fargny_board_events
  ADD COLUMN max_participants INT NULL AFTER description;
