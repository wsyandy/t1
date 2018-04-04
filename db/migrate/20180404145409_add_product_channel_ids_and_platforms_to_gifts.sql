ALTER TABLE gifts ADD COLUMN product_channel_ids text;
ALTER TABLE gifts ADD COLUMN platforms VARCHAR (255) DEFAULT '*';
