create table banners(
  id serial PRIMARY key not null,
  name VARCHAR (255) DEFAULT '',
  image VARCHAR (255),
  url VARCHAR (255),
  new INTEGER DEFAULT 0,
  hot INTEGER DEFAULT 0,
  created_at integer,
  updated_at INTEGER,
  status integer,
  rank INTEGER ,
  platforms VARCHAR (255),
  operator_id INTEGER ,
  material_type INTEGER ,
  material_ids text
);

CREATE index new_on_banners on banners(new);
CREATE index hot_on_banners on banners(hot);