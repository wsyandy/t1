CREATE TABLE i_gold_histories(
  id serial PRIMARY KEY NOT NULL ,
  user_id INTEGER DEFAULT 0,
  amount INTEGER ,
  balance INTEGER ,
  order_id INTEGER DEFAULT 0,
  gift_order_id INTEGER DEFAULT 0,
  fee_type INTEGER ,
  remark VARCHAR (255),
  created_at INTEGER,
  updated_at INTEGER,
  operator_id INTEGER DEFAULT 0,
  country_id INTEGER DEFAULT 0
);

create index user_id_on_i_gold_histories on i_gold_histories(user_id);
create index order_id_on_i_gold_histories on i_gold_histories(order_id);
create index gift_order_id_on_i_gold_histories on i_gold_histories(gift_order_id);
create index fee_type_on_i_gold_histories on i_gold_histories(fee_type);