CREATE TABLE product_channel_banners(
id serial PRIMARY key not null,
banner_id INTEGER ,
product_channel_id INTEGER ,
created_at INTEGER
);

create index banner_id_on_product_channel_banners ON product_channel_banners(banner_id);