create table payments(
    id serial PRIMARY KEY NOT NULL,
    payment_no varchar(255),
    order_id integer,
    user_id integer,
    payment_channel_id integer,
    payment_type varchar(255),
    amount decimal(10, 2),
    paid_amount decimal(10, 2),
    trade_no varchar(255),
    paid_at integer,
    result_data TEXT,
    temp_data TEXT,
    created_at INTEGER,
    updated_at integer
);

create index payment_no_on_payments on payments(payment_no);
create index user_id_on_payments on payments(user_id);
create index order_id_on_payments on payments(order_id);
create index payment_channel_id_on_payments on payments(payment_channel_id);
create index trade_no_on_payments on payments(trade_no);
create index paid_at_on_payments on payments(paid_at);
create index created_at_on_payments on payments(created_at);