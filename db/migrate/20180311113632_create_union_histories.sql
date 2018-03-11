CREATE TABLE union_histories (
  id serial PRIMARY KEY NOT NULL,
  user_id INTEGER ,
  status INTEGER DEFAULT 1,
  union_id INTEGER ,
  join_at INTEGER ,
  exit_at INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index user_id_on_union_histories on union_histories(user_id);
CREATE index union_id_on_union_histories on union_histories(union_id);
CREATE index status_on_union_histories on union_histories(status);