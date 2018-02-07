ALTER TABLE gift_orders add COLUMN receiver_user_type INTEGER ;
ALTER TABLE gift_orders add COLUMN sender_user_type INTEGER ;

CREATE index receiver_user_type_on_gift_orders on gift_orders(receiver_user_type);
CREATE index sender_user_type_on_gift_orders on gift_orders(sender_user_type);