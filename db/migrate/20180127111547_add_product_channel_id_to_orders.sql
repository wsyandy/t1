ALTER TABLE orders ADD COLUMN product_channel_id INTEGER ;
ALTER TABLE orders ADD COLUMN partner_id INTEGER ;
ALTER TABLE orders ADD COLUMN platform VARCHAR (255);
ALTER TABLE orders ADD COLUMN province_id INTEGER ;

CREATE index product_channel_id_on_orders on orders(product_channel_id);
CREATE index partner_id_on_orders on orders(partner_id);
CREATE index province_id_on_orders on orders(province_id);