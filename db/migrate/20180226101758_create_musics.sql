create table musics(
  id serial PRIMARY key not null,
  user_id integer,
  name VARCHAR (255),
  singer_name VARCHAR (255),
  status integer,
  rank integer,
  hot INTEGER DEFAULT 0,
  type integer,
  file VARCHAR (255),
  file_size VARCHAR (255),
  expired_at integer,
  created_at integer
);

create index hot_on_musics on musics(hot);
create index user_id_on_musics on musics(user_id);
create index status_on_musics on musics(status);