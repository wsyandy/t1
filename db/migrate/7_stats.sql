CREATE TABLE stats
(
  id serial PRIMARY key not null,
  stat_at integer,
  time_type integer,
  province_id integer DEFAULT (-1),
  platform VARCHAR (255) DEFAULT (-1),
  version_code VARCHAR (255) DEFAULT (-1),
  product_channel_id integer DEFAULT (-1),
  partner_id integer DEFAULT (-1),
  sex integer DEFAULT (-1),
  data text,
  product_id integer DEFAULT (-1)
);

create index stat_at_on_stats on stats(stat_at);
create index product_channel_id_on_stats on stats(product_channel_id);
create index partner_id_on_stats on stats(partner_id);
create index product_id_on_stats on stats(product_id);

