DROP INDEX status_on_activities;
DROP INDEX type_on_activities;

create index start_at_on_activities on activities(start_at);
create index end_at_on_activities on activities(end_at);


create index user_id_on_backpacks on backpacks(user_id);
create index target_id_on_backpacks on backpacks(target_id);
create index number_on_backpacks on backpacks(number);

DROP INDEX music_id_on_complaints;
DROP INDEX respondent_id_on_complaints;
DROP INDEX room_id_on_complaints;

DROP INDEX receiver_union_type_on_gift_orders;
DROP INDEX receiver_user_type_on_gift_orders;
DROP INDEX room_union_type_on_gift_orders;
DROP INDEX sender_union_type_on_gift_orders;
DROP INDEX sender_user_type_on_gift_orders;

create index user_id_on_id_card_auths on id_card_auths(user_id);
create index account_bank_id_on_id_card_auths on id_card_auths(account_bank_id);

DROP INDEX hot_on_musics;
DROP INDEX status_on_musics;

DROP INDEX union_type_on_orders;

create unique index fr_on_partners on partners(fr);

DROP INDEX pay_status_on_payments;

DROP INDEX status_on_room_category_keywords;
DROP INDEX union_type_on_rooms;
DROP INDEX user_agreement_num_on_rooms;

DROP INDEX third_unionid_on_third_auths;

DROP INDEX auth_status_on_unions;
DROP INDEX status_on_unions;
DROP INDEX type_on_unions;

DROP INDEX union_type_on_users;