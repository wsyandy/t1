CREATE TABLE room_category_keywords (
   id serial PRIMARY KEY NOT NULL ,
   name VARCHAR (255),
   room_category_id INTEGER ,
   status INTEGER ,
   updated_at INTEGER ,
   created_at INTEGER
);

CREATE index room_category_id_on_room_category_keywords on room_category_keywords(room_category_id);
CREATE index status_on_room_category_keywords on room_category_keywords(status);