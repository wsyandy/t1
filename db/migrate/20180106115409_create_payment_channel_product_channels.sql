create table payment_channel_product_channels(
    id serial PRIMARY KEY NOT NULL,
    payment_channel_id integer,
    product_channel_id integer,
    created_at integer
);

create index payment_channel_id_on_payment_channel_product_channels on payment_channel_product_channels(payment_channel_id);
create index product_channel_id_on_payment_channel_product_channels on payment_channel_product_channels(product_channel_id);