CREATE TABLE gold_histories (
id serial PRIMARY key not null,
user_id INTEGER ,
product_channel_id INTEGER ,
amount INTEGER ,
balance INTEGER ,
order_id INTEGER ,
gift_order_id INTEGER ,
fee_type INTEGER ,
remark VARCHAR (255),
created_at INTEGER ,
updated_at INTEGER
);

CREATE index user_id_on_gold_histories on gold_histories(user_id);
CREATE index order_id_on_gold_histories on gold_histories(order_id);
CREATE index gift_order_id_on_gold_histories on gold_histories(gift_order_id);
CREATE index fee_type_on_gold_histories on gold_histories(fee_type);
