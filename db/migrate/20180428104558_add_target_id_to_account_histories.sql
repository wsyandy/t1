ALTER TABLE account_histories add COLUMN target_id INTEGER ;
create index target_id_on_account_histories on account_histories(target_id);