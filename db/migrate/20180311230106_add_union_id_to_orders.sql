ALTER TABLE orders ADD COLUMN union_id INTEGER DEFAULT 0;
CREATE index union_id_on_orders on orders(union_id);