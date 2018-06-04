CREATE TABLE turntable_draw_histories(
  id serial PRIMARY KEY NOT NULL ,
  user_id INTEGER ,
  product_channel_id INTEGER ,
  type VARCHAR (127),
  number INTEGER ,
  gift_id INTEGER ,
  pay_type VARCHAR (63),
  pay_amount DECIMAL (12, 4),
  total_pay_amount DECIMAL (12, 4),
  gift_type INTEGER ,
  total_number INTEGER ,
  gift_num INTEGER ,
  total_gift_num INTEGER ,
  total_gold INTEGER ,
  total_diamond INTEGER ,
  total_gift_diamond INTEGER ,
  created_at INTEGER
);

CREATE index user_id_on_turntable_draw_histories on turntable_draw_histories(user_id);
CREATE index created_at_on_turntable_draw_histories on turntable_draw_histories(created_at);
CREATE index number_on_turntable_draw_histories on turntable_draw_histories(number);