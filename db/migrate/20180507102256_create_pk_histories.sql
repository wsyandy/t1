create table pk_histories (
  id serial PRIMARY KEY not NULL ,
  room_id INTEGER ,
  user_id INTEGER,
  player_a_id INTEGER,
  player_b_id INTEGER,
  pk_type VARCHAR (255),
  status INTEGER,
  expire_at INTEGER ,
  updated_at INTEGER ,
  created_at INTEGER
);

CREATE index room_id_on_pk_histories on pk_histories(room_id);
CREATE index user_id_on_pk_histories on pk_histories(user_id);
CREATE index player_a_id_on_pk_histories on pk_histories(player_a_id);
CREATE index player_b_id_on_pk_histories on pk_histories(player_b_id);