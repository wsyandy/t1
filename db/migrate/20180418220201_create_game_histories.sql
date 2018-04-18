CREATE TABLE game_histories(
id serial PRIMARY KEY NOT NULL ,
game_id integer,
user_id integer,
room_id integer,
start_data text,
end_data text,
updated_at integer,
created_at integer
);

create index game_id_on_game_histories on game_histories(game_id);
create index user_id_on_game_histories on game_histories(user_id);
create index room_id_on_game_histories on game_histories(room_id);