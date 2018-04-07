create TABLE activity_histories(
  id serial PRIMARY KEY NOT NULL ,
  activity_id INTEGER ,
  user_id INTEGER ,
  prize_type INTEGER ,
  gift_id INTEGER ,
  gold INTEGER ,
  good_number INTEGER ,
  auth_status INTEGER ,
  created_at INTEGER,
  updated_at INTEGER
);