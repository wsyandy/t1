create table voice_call_histories(
    id serial PRIMARY KEY NOT NULL,
    sid integer,
    rid integer,
    call_status integer,
    duration integer,
    created_at INTEGER,
    updated_at integer
);

create index sid_on_voice_call_histories on voice_call_histories(sid);
create index rid_on_voice_call_histories on voice_call_histories(rid);
create index created_at_on_voice_call_histories on voice_call_histories(created_at);