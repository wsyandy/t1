create table user_gifts (
  id serial PRIMARY KEY NOT null,
  name VARCHAR (255),
  gift_id INTEGER ,
  user_id INTEGER ,
  num INTEGER ,
  amount INTEGER ,
  total_amount  INTEGER ,
  pay_type VARCHAR (20),
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index user_id_on_user_gifts on user_gifts(user_id);
CREATE index gift_id_on_user_gifts on user_gifts(gift_id);