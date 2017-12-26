create table operators(
 id serial PRIMARY key not null,
 status integer,
 username VARCHAR (255),
 password VARCHAR (255),
 role VARCHAR (255),
 updated_at integer,
 ip VARCHAR (255),
 active_at INTEGER ,
 created_at integer
);

create index username_on_operators on operators(username);
