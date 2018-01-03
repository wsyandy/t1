ALTER table rooms add COLUMN room_seat_id INTEGER;
create index room_seat_id_on_rooms on rooms(room_seat_id);