ALTER TABLE gift_orders add COLUMN sender_id INTEGER ;
CREATE index sender_id_on_gift_orders on gift_orders(sender_id);