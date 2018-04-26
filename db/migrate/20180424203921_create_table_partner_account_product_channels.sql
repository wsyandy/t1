create table partner_account_product_channels(
    id serial PRIMARY KEY NOT NULL,
    product_channel_id INTEGER DEFAULT 0,
    partner_account_id INTEGER DEFAULT 0,
    partner_id INTEGER,
    created_at integer
);

create index partner_id_on_partner_account_product_channels on partner_account_product_channels(partner_id);
create index partner_account_id_on_partner_account_product_channels on partner_account_product_channels(partner_account_id);
create index product_channel_id_on_partner_account_product_channels on partner_account_product_channels(product_channel_id);
