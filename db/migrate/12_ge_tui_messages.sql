CREATE TABLE ge_tui_messages(
  id serial PRIMARY KEY NOT NULL ,
  name VARCHAR (512),
  status INTEGER ,
  send_status INTEGER ,
  send_at INTEGER ,
  remark text ,
  product_channel_id INTEGER ,
  province_ids VARCHAR (512),
  bind_mobile INTEGER ,
  created_at INTEGER ,
  push_message_id INTEGER ,
  offline_day VARCHAR (63),
  operator_id INTEGER
);