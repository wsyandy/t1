CREATE TABLE boom_configs(
  id serial PRIMARY key not null,
  name VARCHAR (255),
  start_value INTEGER ,
  total_value INTEGER ,
  svga_image VARCHAR (255),
  status INTEGER,
  rank INTEGER ,
  created_at INTEGER
);