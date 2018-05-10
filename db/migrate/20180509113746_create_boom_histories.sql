CREATE TABLE boom_histories(
  id serial PRIMARY key not null,
  user_id INTEGER ,
  target_id INTEGER ,
  type INTEGER DEFAULT 1,
  image VARCHAR (255),
  number INTEGER ,
  created_at INTEGER
);
