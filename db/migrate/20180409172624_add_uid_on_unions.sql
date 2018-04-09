alter table unions add column uid bigint;
create unique index uid_on_unions on unions(uid);