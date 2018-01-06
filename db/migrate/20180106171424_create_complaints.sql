CREATE TABLE complaints(
  id serial PRIMARY KEY NOT NULL ,
  type INTEGER ,
  complainer_id INTEGER ,
  respondent_id INTEGER,
  room_id INTEGER ,
  content text,
  type INTEGER ,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);