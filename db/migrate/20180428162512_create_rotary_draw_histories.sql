create table rotary_draw_histories(
  id serial PRIMARY key not null,
  product_channel_id integer,
  user_id integer,
  type VARCHAR (127),
  number INTEGER ,
  gift_id INTEGER ,
  created_at integer
);