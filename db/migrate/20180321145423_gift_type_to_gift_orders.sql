ALTER TABLE gift_ordrs ADD COLUMN gift_type INTEGER ;
ALTER TABLE user_gifts ADD COLUMN gift_type INTEGER ;


CREATE index gift_type_on_gift_ordrs on gift_ordrs(gift_type);
CREATE index gift_type_on_user_gifts on user_gifts(gift_type);