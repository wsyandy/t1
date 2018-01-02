CREATE TABLE room_seats(
  id serial PRIMARY key not null,
  user_id INTEGER,
  room_id INTEGER,
  status INTEGER DEFAULT 1,
  microphone boolean DEFAULT TRUE,
  created_at INTEGER
);