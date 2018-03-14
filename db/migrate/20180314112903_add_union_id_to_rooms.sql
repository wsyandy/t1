ALTER TABLE rooms add COLUMN union_id INTEGER;
ALTER TABLE rooms add COLUMN union_type INTEGER;

create index union_id_on_rooms on rooms(union_id);
create index union_type_on_rooms on rooms(union_type);