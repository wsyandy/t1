CREATE TABLE albums
(
  id serial PRIMARY key not null,
  user_id integer,
  auth_status integer DEFAULT 0,
  image VARCHAR (255),
  created_at INTEGER
);