CREATE TABLE wish_histories (
  id serial PRIMARY key not null,
  user_id INTEGER,
  wish_text text,
  product_channel_id INTEGER,
  created_at INTEGER
);

create index user_id_on_wish_histories on wish_histories(user_id);
create index product_channel_id_on_wish_histories on wish_histories(product_channel_id);