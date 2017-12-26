create table partner_urls(
  id serial PRIMARY KEY not NULL ,
  name VARCHAR (255),
  url  VARCHAR (255),
  type VARCHAR (255),
  operator_id integer,
  platform VARCHAR (127),
  domain VARCHAR (255),
  created_at integer
);
