CREATE TABLE red_packets(
  id serial PRIMARY key not null,
  user_id integer,
  room_id integer,
  status INTEGER,
  diamond INTEGER ,
  num INTEGER ,
  balance_diamond integer ,
  balance_num INTEGER ,
  red_packet_type VARCHAR (127),
  nearby_distance integer ,
  sex integer ,
  created_at INTEGER
);

create index created_at_on_red_packets on red_packets(created_at);
create index user_id_on_red_packets on red_packets(user_id);
create index room_id_on_red_packets on red_packets(room_id);