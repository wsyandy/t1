DROP TABLE unions;

CREATE TABLE unions (
  id serial PRIMARY KEY NOT NULL,
  type INTEGER ,
  name VARCHAR (255),
  notice VARCHAR (255),
  user_id INTEGER ,
  product_channel_id INTEGER ,
  status INTEGER DEFAULT 1,
  need_apply INTEGER DEFAULT 0,
  auth_status INTEGER DEFAULT 0,
  fame_value REAL DEFAULT 0,
  mobile VARCHAR (255),
  id_no VARCHAR (255),
  id_name VARCHAR (255),
  recommend INTEGER DEFAULT 0,
  alipay_account VARCHAR (255),
  avatar VARCHAR (255),
  avatar_status INTEGER DEFAULT 0,
  error_reason VARCHAR (255),
  password VARCHAR (255),
  amount DECIMAL (10, 2),
  settled_amount DECIMAL (10, 2),
  frozen_amount DECIMAL (10, 2),
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index user_id_on_unions on unions(user_id);
CREATE index product_channel_id_on_unions on unions(product_channel_id);
CREATE index type_on_unions on unions(type);
CREATE index status_on_unions on unions(status);
CREATE index auth_status_on_unions on unions(auth_status);
CREATE index recommend_on_unions on unions(recommend);