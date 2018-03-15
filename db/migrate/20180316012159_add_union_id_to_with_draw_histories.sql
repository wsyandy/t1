ALTER TABLE withdraw_histories add COLUMN union_id INTEGER ;
ALTER TABLE withdraw_histories add COLUMN type INTEGER DEFAULT 1;

CREATE index union_id_on_withdraw_histories on withdraw_histories(union_id);