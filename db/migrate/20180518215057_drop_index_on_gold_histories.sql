ALTER TABLE gold_histories DROP COLUMN order_id;
ALTER TABLE gold_histories DROP COLUMN gift_order_id;
ALTER TABLE gold_histories DROP COLUMN hi_coin_history_id;
ALTER TABLE gold_histories DROP COLUMN country_id;
ALTER TABLE gold_histories DROP COLUMN activity_id;

create index fee_type_target_id_on_gold_histories on gold_histories(fee_type,target_id);
create index created_at_on_gold_histories on gold_histories(created_at);
