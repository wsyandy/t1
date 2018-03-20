CREATE TABLE account_banks(
  id serial PRIMARY key not null,
  code character varying(255),
  name character varying(255),
  rank integer,
  status integer,
  icon VARCHAR (255),
  created_at integer
);