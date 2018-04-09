alter table rooms add column uid bigint;
create unique index uid_on_rooms on rooms(uid);