create table withdraw_histories(
    id serial PRIMARY KEY NOT NULL,
    user_name VARCHAR (127),
    user_id INTEGER ,
    alipay_account  VARCHAR (255),
    product_channel_id INTEGER ,
    amount NUMERIC (10,2),
    status INTEGER ,
    created_at INTEGER,
    updated_at integer
);