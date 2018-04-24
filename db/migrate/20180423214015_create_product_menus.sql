CREATE TABLE product_menus (
  id serial PRIMARY key not null,
  name VARCHAR (255),
  type VARCHAR (255),
  rank INTEGER ,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);