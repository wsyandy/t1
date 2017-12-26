create table sms_channels (
 id serial PRIMARY key not null,
 name     varchar(255),
 username varchar(255),
 password varchar(255),
 url      varchar(255),
 clazz    varchar(255),
 status      integer,
 rank        integer ,
 signature varchar(255),
 created_at  integer,
 sms_type   varchar(255),
 template   varchar(255),
 company_no varchar(127),
 mobile_operator integer,
 product_channel_ids text
);