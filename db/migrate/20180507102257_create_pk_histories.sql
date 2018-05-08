DROP TABLE if EXISTS pk_histories;

create table pk_histories (
  id serial PRIMARY KEY not NULL ,
  room_id INTEGER ,
  user_id INTEGER,
  left_pk_user_id INTEGER,
  right_pk_user_id INTEGER,
  left_pk_user_score bigint,
  right_pk_user_score bigint,
  pk_type VARCHAR (255),
  status INTEGER,
  expire_at INTEGER ,
  updated_at INTEGER ,
  created_at INTEGER
);

CREATE index room_id_on_pk_histories on pk_histories(room_id);
CREATE index user_id_on_pk_histories on pk_histories(user_id);
CREATE index left_pk_user_id_on_pk_histories on pk_histories(left_pk_user_id);
CREATE index right_pk_user_id_on_pk_histories on pk_histories(right_pk_user_id);