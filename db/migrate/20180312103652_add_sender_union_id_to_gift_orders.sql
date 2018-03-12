ALTER TABLE gift_orders ADD COLUMN sender_union_id INTEGER DEFAULT 0;
ALTER TABLE gift_orders ADD COLUMN receiver_union_id INTEGER DEFAULT 0;
CREATE index sender_union_id_on_gift_orders on gift_orders(sender_union_id);
CREATE index receiver_union_id_on_gift_orders on gift_orders(receiver_union_id);