ALTER TABLE devices ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE users ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE rooms ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE room_seats ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE orders ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE payments ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE gift_orders ALTER COLUMN sender_country_id set DEFAULT 0 ;
ALTER TABLE gift_orders ALTER COLUMN receiver_country_id set DEFAULT 0 ;
ALTER TABLE gold_histories ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE account_histories ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE hi_coin_histories ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE unions ALTER COLUMN country_id set DEFAULT 0 ;
ALTER TABLE union_histories ALTER COLUMN country_id set DEFAULT 0 ;