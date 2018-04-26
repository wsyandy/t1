create table partner_datas(
    id serial PRIMARY KEY NOT NULL,
    partner_id INTEGER,
    product_channel_id INTEGER DEFAULT 0,
    stat_at INTEGER ,
    time_type INTEGER ,
    register_ratio INTEGER,
    rank INTEGER ,
    created_at integer,
    updated_at integer,
    data  text
);

create index partner_id_on_partner_datas on partner_datas(partner_id);
create index stat_at_on_partner_datas on partner_datas(stat_at);