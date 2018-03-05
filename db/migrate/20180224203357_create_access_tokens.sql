create table access_tokens(
  id serial PRIMARY key not null,
  user_id integer DEFAULT 0,
  status integer,
  expired_at integer,
  created_at integer
);

create index expired_at_on_access_tokens on access_tokens(expired_at);
create index user_id_on_access_tokens on access_tokens(user_id);