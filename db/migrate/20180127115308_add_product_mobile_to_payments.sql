ALTER TABLE orders ADD COLUMN mobile VARCHAR (255);
CREATE index mobile_on_orders on orders(mobile);