ALTER TABLE room_themes ADD COLUMN product_channel_ids text;
ALTER TABLE room_themes ADD COLUMN platforms VARCHAR (255) DEFAULT '*';