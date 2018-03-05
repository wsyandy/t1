ALTER TABLE users add COLUMN level INTEGER ;
ALTER TABLE users add COLUMN experience REAL ;

create index level_on_users on users(level);