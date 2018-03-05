create table room_themes(
  id serial PRIMARY key not null,
  name VARCHAR (127),
  icon VARCHAR (255),
  image VARCHAR (255),
  rank  integer ,
  status INTEGER ,
  created_at integer,
  updated_at INTEGER
);

