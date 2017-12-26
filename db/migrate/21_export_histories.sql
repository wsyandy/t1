create table export_histories(
  id serial PRIMARY key not null,
  operator_id integer,
  name VARCHAR (255),
  table_name VARCHAR (127),
  file VARCHAR (512),
  download_num integer,
  conditions text,
  created_at integer,
  updated_at integer
);
