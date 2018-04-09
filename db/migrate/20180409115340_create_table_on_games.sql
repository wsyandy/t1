CREATE TABLE games
(
  id serial PRIMARY KEY NOT NULL ,
  name VARCHAR (255),
  url VARCHAR (255),
  icon VARCHAR (255),
  status INTEGER
);