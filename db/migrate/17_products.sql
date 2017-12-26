CREATE TABLE products (
    id serial PRIMARY KEY NOT NULL ,
    name VARCHAR (255),
    icon VARCHAR (255),
    status INTEGER,
    rank INTEGER,
    created_at INTEGER,
    updated_at INTEGER
);