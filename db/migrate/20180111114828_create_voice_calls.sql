create table voice_calls(
    id serial PRIMARY KEY NOT NULL,
    sender_id integer,
    receiver_id integer,
    call_status integer,
    duration integer,
    created_at INTEGER,
    updated_at integer
);

create index sender_id_on_voice_calls on voice_calls(sender_id);
create index receiver_id_on_voice_calls on voice_calls(receiver_id);
create index created_at_on_voice_calls on voice_calls(created_at);