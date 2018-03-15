CREATE TABLE hot_room_histories (
  id serial PRIMARY KEY not NULL ,
  user_id  INTEGER ,
  union_id INTEGER ,
  introduce text ,
  start_at INTEGER ,
  end_at INTEGER ,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);