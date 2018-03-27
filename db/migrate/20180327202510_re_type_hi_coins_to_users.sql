alter TABLE hi_coin_histories ALTER COLUMN hi_coins type DECIMAL (20, 4);
alter TABLE hi_coin_histories ALTER COLUMN balance type DECIMAL (20, 4);

alter TABLE unions ALTER COLUMN settled_amount type DECIMAL (20, 4);
alter TABLE unions ALTER COLUMN frozen_amount type DECIMAL (20, 4);
alter TABLE unions ALTER COLUMN amount type DECIMAL (20, 4);


alter TABLE unions ALTER COLUMN fame_value type bigint;


alter TABLE users ALTER COLUMN charm_value type bigint;
alter TABLE users ALTER COLUMN wealth_value type bigint;
alter TABLE users ALTER COLUMN union_charm_value type bigint;
alter TABLE users ALTER COLUMN union_wealth_value type bigint;
alter TABLE users ALTER COLUMN hi_coins type DECIMAL (20, 4);
alter TABLE users ALTER COLUMN experience type DECIMAL (20, 4);

alter TABLE user_gifts ALTER COLUMN total_amount type bigint;