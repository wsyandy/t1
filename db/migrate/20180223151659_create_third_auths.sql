CREATE TABLE third_auths (
  id serial PRIMARY KEY not NULL,
  user_id INTEGER ,
  product_channel_id INTEGER ,
  third_id INTEGER ,
  third_token text,
  third_name VARCHAR (255),
  third_unionid VARCHAR (255),
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index user_id_on_third_auths on third_auths(user_id);
CREATE index product_channel_id_on_third_auths on third_auths(product_channel_id);
CREATE index third_id_on_third_auths on third_auths(third_id);
CREATE index third_name_on_third_auths on third_auths(third_name);
CREATE index third_unionid_on_third_auths on third_auths(third_unionid);