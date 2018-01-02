CREATE TABLE rooms(
  id serial PRIMARY key not null,
  name VARCHAR (255),
  topic VARCHAR (255),
  chat boolean DEFAULT TRUE,
  status INTEGER,
  created_at INTEGER,
  update_at INTEGER,
  rank INTEGER
);