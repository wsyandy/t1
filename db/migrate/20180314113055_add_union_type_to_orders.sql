ALTER TABLE orders add COLUMN union_type INTEGER;
ALTER TABLE gift_orders add COLUMN sender_union_type INTEGER;
ALTER TABLE gift_orders add COLUMN receiver_union_type INTEGER;
ALTER TABLE users add COLUMN union_type INTEGER;
ALTER TABLE union_histories add COLUMN union_type INTEGER;
ALTER TABLE account_histories add COLUMN union_type INTEGER;


create index union_type_on_orders on orders(union_type);
create index union_type_on_union_histories on union_histories(union_type);
create index union_type_on_account_histories on account_histories(union_type);
create index union_type_on_users on users(union_type);
create index sender_union_type_on_gift_orders on gift_orders(sender_union_type);
create index receiver_union_type_on_gift_orders on gift_orders(receiver_union_type);