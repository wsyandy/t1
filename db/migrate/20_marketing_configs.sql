create table marketing_configs(
  id serial PRIMARY KEY NOT NULL ,
  name VARCHAR (127),
  client_id integer,
  client_secret VARCHAR (256),
  gdt_account_id integer,
  android_app_id integer,
  ios_app_id integer,
  android_user_action_set_id integer,
  ios_user_action_set_id integer,
  refresh_token VARCHAR (512),
  refresh_token_expire_at integer,
  operator_id integer,
  updated_at integer,
  created_at integer,
  redirect_uri VARCHAR (512),
  access_token VARCHAR (255),
  access_token_expire_at integer
);

create index client_id_on_marketing_configs on marketing_configs(client_id);
create index gdt_account_id_on_marketing_configs on marketing_configs(gdt_account_id);