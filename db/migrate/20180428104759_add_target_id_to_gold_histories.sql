ALTER TABLE gold_histories add COLUMN target_id INTEGER ;
create index target_id_on_gold_histories on gold_histories(target_id);