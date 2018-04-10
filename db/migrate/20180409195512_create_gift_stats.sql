CREATE TABLE gift_stats(
  id serial PRIMARY KEY NOT NULL ,
  stat_at INTEGER ,
  product_channel_id INTEGER ,
  gift_id  INTEGER ,
  data   text
);