CREATE TABLE activities (
  id serial PRIMARY key not null,
  title VARCHAR(255),
  image VARCHAR(255),
  platforms VARCHAR (255) DEFAULT '*',
  product_channel_ids text,
  start_at INTEGER ,
  end_at INTEGER,
  rank INTEGER ,
  operator_id INTEGER,
  status INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);