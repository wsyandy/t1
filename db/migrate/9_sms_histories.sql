CREATE TABLE sms_histories (
    id serial PRIMARY KEY NOT NULL,
    sms_channel_id INTEGER,
    mobile VARCHAR(255),
    sms_type VARCHAR(255),
    content VARCHAR(255),
    auth_status INTEGER,
    send_status INTEGER,
    expired_at INTEGER,
    created_at INTEGER,
    product_channel_id INTEGER,
    device_id INTEGER,
    sms_token VARCHAR(255),
    context text,
    updated_at INTEGER
);

create index  sms_channel_id_on_sms_histories on sms_histories(sms_channel_id);
create index  product_channel_id_on_sms_histories on sms_histories(product_channel_id);
create index  device_id_on_sms_histories on sms_histories(device_id);
create index  mobile_on_sms_histories on sms_histories(mobile);