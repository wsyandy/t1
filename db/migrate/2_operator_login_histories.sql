CREATE TABLE operator_login_histories(
  id serial PRIMARY KEY NOT NULL ,
  operator_id INTEGER ,
  ip VARCHAR (255),
  login_at INTEGER ,
  created_at INTEGER,
  updated_at INTEGER,
  location VARCHAR (255)
);