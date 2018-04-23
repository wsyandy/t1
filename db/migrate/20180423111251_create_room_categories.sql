CREATE TABLE room_categories (
  id serial PRIMARY key not null,
  name VARCHAR (255),
  type VARCHAR (255),
  parent_id INTEGER ,
  rank INTEGER ,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);