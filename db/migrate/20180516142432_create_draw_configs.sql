create table draw_configs(
    id serial PRIMARY KEY NOT NULL,
    type varchar(255),
    name varchar(255),
    number integer,
    rate decimal(10,4),
    day_limit_num integer,
    rank integer,
    gift_id integer,
    gift_num integer,
    created_at INTEGER ,
    updated_at INTEGER
);