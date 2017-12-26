CREATE TABLE client_themes (
    id serial PRIMARY KEY NOT NULL,
    product_channel_id INTEGER,
    file VARCHAR(255),
    version_name VARCHAR(255),
    version_code INTEGER,
    status INTEGER,
    created_at INTEGER,
    ios_version_code INTEGER,
    android_version_code INTEGER,
    remark text
);
create index  product_channel_id_on_client_themes on client_themes(product_channel_id);