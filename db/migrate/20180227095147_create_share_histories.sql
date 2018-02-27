create table share_histories (
  id serial PRIMARY key not null,
  product_channel_id INTEGER ,
  user_id INTEGER ,
  type INTEGER ,
  share_source VARCHAR (255),
  status INTEGER ,
  view_num INTEGER ,
  data text,
  created_at integer,
  updated_at INTEGER
);