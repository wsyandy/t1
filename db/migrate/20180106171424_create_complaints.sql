CREATE TABLE complaints(
  id serial PRIMARY KEY NOT NULL ,
  complainer_id INTEGER ,
  respondent_id INTEGER,
  room_id INTEGER ,
  complaint_type INTEGER ,
  content text,
  type INTEGER ,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);