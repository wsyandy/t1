CREATE TABLE backpacks(
  id serial PRIMARY key not null,
  user_id INTEGER ,
  target_id INTEGER ,
  type INTEGER ,
  status INTEGER ,
  number INTEGER ,
  image VARCHAR ,
  created_at INTEGER ,
  updated_at INTEGER
);