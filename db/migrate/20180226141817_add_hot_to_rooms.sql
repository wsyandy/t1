ALTER TABLE rooms ADD COLUMN hot INTEGER DEFAULT 0;

CREATE index hot_on_rooms on rooms (hot);