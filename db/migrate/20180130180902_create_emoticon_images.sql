create table emoticon_images(
  id serial PRIMARY key not null,
  name VARCHAR (127),
  image VARCHAR (255),
  dynamic_image VARCHAR (255),
  rank  integer ,
  status INTEGER ,
  code varchar(255),
  duration INTEGER ,
  created_at integer,
  updated_at INTEGER
);