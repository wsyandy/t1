create table partner_accounts(
    id serial PRIMARY KEY NOT NULL,
    status INTEGER,
    username varchar(255),
    password varchar(255),
    ip varchar(255),
    created_at integer,
    updated_at integer
);

create index username_on_partner_accounts on partner_accounts(username);