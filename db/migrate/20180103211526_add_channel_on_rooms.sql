ALTER table rooms add COLUMN product_channel_id INTEGER;
create index product_channel_id_on_rooms on rooms(product_channel_id);