create table gdt_configs(
  id serial PRIMARY KEY not NULL ,
  name VARCHAR (255),
  advertiser_id integer,
  sign_key VARCHAR (255),
  encrypt_key VARCHAR (255),
  operator_id integer,
  remark VARCHAR (255),
  created_at integer ,
  updated_at integer
);