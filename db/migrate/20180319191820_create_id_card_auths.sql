CREATE TABLE id_card_auths (
  id serial PRIMARY KEY NOT NULL,
  auth_status INTEGER,
  product_channel_id INTEGER ,
  user_id INTEGER ,
  id_no VARCHAR (255),
  id_name VARCHAR (255),
  bank_account VARCHAR (255),
  auth_at INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);