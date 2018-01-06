create table payment_channels(
    id serial PRIMARY KEY NOT NULL,
    name varchar(255),
    mer_no varchar(255),
    mer_name varchar(255),
    app_id varchar(255),
    app_key varchar(255),
    app_password varchar(255),
    clazz varchar(255),
    gateway_url varchar(255),
    payment_type varchar(255),
    fee decimal(10, 5),
    status integer,
    rank integer
);

create index payment_type_on_payment_channels on payment_channels(payment_type);