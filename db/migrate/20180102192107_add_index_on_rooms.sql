create index user_id_on_rooms on rooms(user_id);
ALTER TABLE rooms DROP COLUMN rank;