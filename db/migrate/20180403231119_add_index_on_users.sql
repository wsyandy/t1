alter table users add column uid bigint;
create unique index uid_on_users on users(uid);