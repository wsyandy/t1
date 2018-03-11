ALTER TABLE gift_orders ADD COLUMN union_id INTEGER DEFAULT 0;
CREATE index union_id_on_gift_orders on gift_orders(union_id);