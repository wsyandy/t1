CREATE TABLE gift_resources (
  id serial PRIMARY KEY NOT NULL,
  status INTEGER DEFAULT 1,
  resource_file VARCHAR (255),
  remark text,
  created_at INTEGER ,
  updated_at INTEGER
);