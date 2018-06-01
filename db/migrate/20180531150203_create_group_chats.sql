CREATE TABLE group_chats(
  id serial PRIMARY key not null,
  uid INTEGER ,
  user_id INTEGER ,
  name VARCHAR (127),
  introduce  text ,
  chat boolean DEFAULT TRUE,
  avatar_file VARCHAR (255),
  avatar_status INTEGER ,
  num INTEGER ,
  join_type VARCHAR (127),
  status INTEGER ,
  created_at INTEGER ,
  last_at INTEGER
);

create unique index uid_on_group_chats on group_chats(uid);