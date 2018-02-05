alter table rooms  rename  type to  user_type ;
alter table rooms ADD COLUMN theme_type INTEGER DEFAULT 0;