create table product_channels (
 id serial PRIMARY key not null,
 name VARCHAR (255),
 code varchar(255),
 company_name VARCHAR (255),
 domain VARCHAR (255),
 service_phone VARCHAR (255),
 avatar VARCHAR (255),
 sms_sign VARCHAR (255),
 status INTEGER,
 icp VARCHAR (255),
 weixin_domain VARCHAR (255),
 weixin_appid VARCHAR (255),
 weixin_secret VARCHAR (255),
 weixin_token VARCHAR (255),
 weixin_name VARCHAR (255),
 weixin_white_list text,
 weixin_type VARCHAR (255),
 created_at integer,
 updated_at integer,
 agreement_company_name                VARCHAR (255),
 agreement_company_shortname           VARCHAR (255),
 ios_app_id                            VARCHAR (255),
 ios_app_key                           VARCHAR (255),
 ios_app_secret                        VARCHAR (255),
 ios_app_master_secret                 VARCHAR (255),
 android_app_id                        VARCHAR (255),
 android_app_key                       VARCHAR (255),
 android_app_secret                    VARCHAR (255),
 android_app_master_secret             VARCHAR (255),
 ckey                                  VARCHAR (255),
 apple_stable_version                  integer,
 android_stable_version                integer,
 ios_client_theme_stable_version       integer,
 android_client_theme_stable_version   integer,
 ios_client_theme_test_version         integer default 0,
 ios_client_theme_foreign_version_code integer default 0,
 cooperation_weixin                    VARCHAR (255),
 cooperation_email                     VARCHAR (255),
 cooperation_phone_number              VARCHAR (255),
 official_website                      VARCHAR (255),
 weixin_theme                          VARCHAR (255),
 weixin_menu_template_id               integer,
 weixin_fr                             VARCHAR (255),
 weixin_no                             VARCHAR (255),
 weixin_qrcode                         VARCHAR (255),
 touch_name                            VARCHAR (255),
 touch_domain                          VARCHAR (255),
 touch_theme                           VARCHAR (255),
 touch_fr                              VARCHAR (255),
 web_domain                            VARCHAR (255),
 web_name                              VARCHAR (255),
 web_fr                                VARCHAR (255),
 web_theme                             VARCHAR (255),
 weixin_welcome                        VARCHAR (255)
);
create index code_on_product_channels on product_channels(code);
create index weixin_domain_on_product_channels on product_channels(weixin_domain);