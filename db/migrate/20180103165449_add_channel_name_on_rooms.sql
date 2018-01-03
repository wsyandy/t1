ALTER table rooms add COLUMN channel_name VARCHAR (63);
create index channel_name_on_rooms on rooms(channel_name);