ALTER TABLE account_histories DROP COLUMN order_id;
ALTER TABLE account_histories DROP COLUMN gift_order_id;
ALTER TABLE account_histories DROP COLUMN hi_coin_history_id;
ALTER TABLE account_histories DROP COLUMN country_id;

DROP INDEX target_id_on_account_histories;
DROP INDEX union_type_on_account_histories;

create index fee_type_target_id_on_account_histories on account_histories(fee_type,target_id);