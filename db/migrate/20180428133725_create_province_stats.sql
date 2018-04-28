CREATE TABLE province_stats(
  id serial PRIMARY KEY NOT NULL ,
  data   text,
  stat_at INTEGER ,
  time_type INTEGER ,
  province_id INTEGER ,
  province_name VARCHAR (255),
  partner_id INTEGER ,
  product_channel_id INTEGER ,
  platform VARCHAR (255)
);

CREATE index province_id_index_on_province_stats ON  province_stats(province_id);
CREATE index product_channel_id_on_province_stats on province_stats(province_id);
CREATE index platform_on_province_stats on province_stats(province_id);