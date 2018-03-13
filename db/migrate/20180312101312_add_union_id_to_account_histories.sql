ALTER TABLE account_histories ADD COLUMN union_id INTEGER DEFAULT 0;
CREATE index union_id_on_account_histories on account_histories(union_id);