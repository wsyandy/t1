CREATE TABLE sina_ad_configs(
id serial PRIMARY KEY NOT NULL ,
name VARCHAR (127),
group_id integer,
convid VARCHAR (127),
token VARCHAR (255),
platform VARCHAR (20),
operator_id integer,
updated_at integer,
created_at integer
);
create index group_id_on_sina_ad_configs on sina_ad_configs(group_id);
create index platform_on_sina_ad_configs on sina_ad_configs(platform);