ALTER table gift_orders add COLUMN room_union_id INTEGER DEFAULT 0;
ALTER table gift_orders add COLUMN room_union_type INTEGER DEFAULT 0;

CREATE index room_union_id_on_gift_orders on gift_orders(room_union_id);
CREATE index room_union_type_on_gift_orders on gift_orders(room_union_type);
