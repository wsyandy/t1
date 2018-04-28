CREATE TABLE sms_distribute_histories
(
  id serial PRIMARY key not null,
  share_history_id INTEGER ,
  share_user_id integer,
  product_channel_id integer,
  mobile VARCHAR (127),
  user_id integer,
  status integer,
  fr VARCHAR (127),
  partner_id integer,
  soft_version_id integer,
  created_at integer,
  updated_at integer
);

create index share_history_id_on_sms_distribute_histories on sms_distribute_histories(share_history_id);
create index share_user_id_on_sms_distribute_histories on sms_distribute_histories(share_user_id);
create index user_id_on_sms_distribute_histories on sms_distribute_histories(user_id);
create index mobile_on_sms_distribute_histories on sms_distribute_histories(mobile);
create index product_channel_id_on_sms_distribute_histories on sms_distribute_histories(product_channel_id);