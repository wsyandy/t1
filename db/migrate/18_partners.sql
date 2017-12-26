CREATE TABLE partners (
    id serial PRIMARY KEY NOT NULL,
    name VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    fr VARCHAR(255),
    product_channel_ids VARCHAR(255),
    created_at INTEGER,
    status INTEGER,
    notify_callback VARCHAR(255),
    level INTEGER DEFAULT 0,
    group_type INTEGER,
    product_ids text
);

create index  username_on_partners on partners(username);