CREATE TABLE weixin_menus(
  id serial PRIMARY key not null,
  weixin_menu_template_id INTEGER ,
  name VARCHAR (255),
  type VARCHAR (255),
  url VARCHAR (512),
  created_at INTEGER,
  update_at INTEGER,
  rank INTEGER
);

CREATE TABLE weixin_menu_templates(
  id serial PRIMARY key not null,
  product_channel_ids text,
  name VARCHAR (255),
  created_at INTEGER,
  update_at INTEGER
);

CREATE TABLE weixin_sub_menus(
  id serial PRIMARY key not null,
  weixin_menu_id INTEGER ,
  name VARCHAR (255),
  type VARCHAR (255),
  url VARCHAR (512),
  created_at INTEGER,
  update_at INTEGER,
  rank INTEGER
);


CREATE TABLE weixin_kefu_messages(
  id serial PRIMARY  key not null,
  name VARCHAR (512),
  status INTEGER,
  send_status INTEGER,
  send_at INTEGER,
  remark text,
  product_channel_id INTEGER,
  push_message_ids VARCHAR (512),
  province_ids VARCHAR (512),
  created_at INTEGER,
  operator_id INTEGER
);

CREATE  index product_channel_id_on_weixin_kefu_messages on weixin_kefu_messages(product_channel_id);
CREATE  index operator_id_on_weixin_kefu_messages on weixin_kefu_messages(operator_id);

CREATE TABLE weixin_template_messages(
   id serial PRIMARY key not null,
   name VARCHAR (512),
   status INTEGER,
   send_status INTEGER,
   send_at INTEGER,
   remark text,
   product_channel_id INTEGER,
   province_ids VARCHAR (512),
   bind_mobile INTEGER,
   created_at INTEGER,
   push_message_id INTEGER,
   offline_day VARCHAR (63),
   operator_id INTEGER,
   need_filter_conditions INTEGER DEFAULT (0),
   platforms VARCHAR (255)  DEFAULT ''
);














