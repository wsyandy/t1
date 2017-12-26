CREATE TABLE push_messages (
    id serial PRIMARY KEY NOT NULL,
    title VARCHAR(255),
    description VARCHAR(512),
    image VARCHAR(255),
    url VARCHAR(255),
    rank INTEGER,
    status INTEGER,
    created_at INTEGER,
    tracker_no VARCHAR(255),
    text_content text,
    offline_time INTEGER,
    platforms   VARCHAR(255),
    product_channel_ids text,
    product_id  INTEGER
);
create index tracker_no_on_push_messages on push_messages(tracker_no);
