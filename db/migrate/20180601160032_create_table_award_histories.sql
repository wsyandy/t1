CREATE TABLE award_histories (
id serial PRIMARY key not null,
user_id INTEGER ,
product_channel_id INTEGER ,
type VARCHAR (255),
amount INTEGER ,
status INTEGER ,
auth_status INTEGER ,
created_at INTEGER ,
updated_at INTEGER
);

CREATE index user_id_on_award_histories on award_histories(user_id);
CREATE index product_channel_id_on_award_histories on award_histories(product_channel_id);

