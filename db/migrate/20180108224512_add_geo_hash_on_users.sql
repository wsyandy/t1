alter table users add COLUMN geo_hash VARCHAR (127);
create index geo_hash_on_users on users(geo_hash);