CREATE TABLE gifts
(
  id serial PRIMARY key not null,
  name VARCHAR (127),
  gold integer,
  diamond integer,
  image VARCHAR (255),
  dynamic_image VARCHAR (255),
  rank integer,
  status INTEGER ,
  created_at INTEGER
);