create table product_groups(
    id serial PRIMARY KEY NOT NULL,
    fee_type varchar(255),
    icon varchar(255),
    remark varchar(255),
    status INTEGER,
    created_at integer,
    updated_at integer
);

create index fee_type_on_product_groups on product_groups(fee_type);
create index status_on_product_groups on product_groups(status);
create index created_at_on_product_groups on product_groups(created_at);