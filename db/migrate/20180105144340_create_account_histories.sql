create table account_histories(
    id serial PRIMARY KEY NOT NULL,
    user_id integer,
    amount integer,
    balance INTEGER,
    order_id INTEGER,
    gift_order_id INTEGER,
    fee_type INTEGER,
    remark varchar(255),
    created_at INTEGER,
    updated_at integer
);

create index user_id_on_account_histories on account_histories(user_id);
create index order_id_on_account_histories on account_histories(order_id);
create index gift_order_id_on_account_histories on account_histories(gift_order_id);
create index fee_type_on_account_histories on account_histories(fee_type);
create index created_at_on_account_histories on account_histories(created_at);