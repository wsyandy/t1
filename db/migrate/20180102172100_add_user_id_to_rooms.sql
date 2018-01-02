alter table rooms add column user_id INTEGER ;
alter table rooms add column lock boolean DEFAULT FALSE ;
alter table rooms add column password VARCHAR (255) ;
alter table rooms add column last_at integer ;