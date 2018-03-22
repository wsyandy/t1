ALTER TABLE gift_orders ADD COLUMN gift_type INTEGER ;
ALTER TABLE user_gifts ADD COLUMN gift_type INTEGER ;


CREATE index gift_type_on_gift_orders on gift_orders(gift_type);
CREATE index gift_type_on_user_gifts on user_gifts(gift_type);