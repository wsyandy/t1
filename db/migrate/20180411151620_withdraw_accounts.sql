CREATE TABLE withdraw_accounts (
    id serial PRIMARY KEY NOT NULL ,
    user_id INTEGER ,
    account_bank_id INTEGER ,
    account VARCHAR (255) ,
    mobile VARCHAR (255),
    type INTEGER,
    status INTEGER ,
    created_at INTEGER ,
    updated_at INTEGER
);

CREATE index user_id_on_withdraw_accounts on withdraw_accounts (user_id);
CREATE index mobile_on_withdraw_accounts on withdraw_accounts (mobile);
CREATE index status_on_withdraw_accounts on withdraw_accounts (status);