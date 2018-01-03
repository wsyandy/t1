CREATE TABLE gift_orders
(
  id serial PRIMARY KEY NOT NULL ,
  name VARCHAR (255),
  user_id INTEGER ,
  gift_id INTEGER ,
  status INTEGER ,
  remark VARCHAR (255),
  amount INTEGER ,
  pay_type VARCHAR (20),
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index user_id_on_gift_orders on gift_orders(user_id);
CREATE index gift_id_on_gift_orders on gift_orders(gift_id);