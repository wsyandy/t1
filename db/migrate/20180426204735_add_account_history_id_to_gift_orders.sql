ALTER TABLE gift_orders add COLUMN target_id INTEGER;

CREATE index target_id_on_gift_orders on gift_orders(target_id);