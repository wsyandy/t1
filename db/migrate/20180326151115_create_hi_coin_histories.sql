CREATE TABLE hi_coin_histories(
  id serial PRIMARY KEY NOT NULL ,
  user_id INTEGER ,
  product_channel_id INTEGER ,
  gift_order_id INTEGER ,
  remark VARCHAR (255),
  balance DECIMAL (10, 2),
  hi_coins DECIMAL (10, 2),
  fee_type INTEGER ,
  union_id INTEGER ,
  union_type INTEGER ,
  reward_at INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);