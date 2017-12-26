create table users(
  id serial PRIMARY key not null,
  login_name VARCHAR (255),
  sex integer,
  nickname VARCHAR (255),
  avatar VARCHAR (255),
  product_channel_id integer,
  user_type integer,
  user_status integer,
  platform VARCHAR (255),
  version VARCHAR (255),
  province_id integer,
  city_id integer,
  ip VARCHAR (255),
  created_at integer,
  last_at integer,
  mobile VARCHAR (255),
  device_id integer,
  push_token VARCHAR (255),
  sid VARCHAR (255),
  version_code VARCHAR (255),
  openid VARCHAR (255),
  password VARCHAR (255),
  fr VARCHAR (255),
  partner_id integer,
  subscribe integer,
  event_at integer,
  latitude integer,
  longitude integer,
  geo_province_id integer,
  geo_city_id integer,
  ip_province_id INTEGER ,
  ip_city_id INTEGER ,
  mobile_register_num integer DEFAULT 0,
  register_at INTEGER ,
  mobile_operator integer,
  api_version VARCHAR (255)
);

create index product_channel_id_on_users on users(product_channel_id);
create index user_type_on_users on users(user_type);
create index device_id_on_users on users(device_id);
create index partner_id_on_users on users(partner_id);
