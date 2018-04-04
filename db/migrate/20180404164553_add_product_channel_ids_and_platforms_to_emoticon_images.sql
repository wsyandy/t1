ALTER TABLE emoticon_images ADD COLUMN product_channel_ids text;
ALTER TABLE emoticon_images ADD COLUMN platforms VARCHAR (255) DEFAULT '*';