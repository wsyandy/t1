create table audios(
    id serial PRIMARY KEY NOT NULL,
    name VARCHAR (127),
    audio_type INTEGER,
    rank INTEGER ,
    status INTEGER ,
    created_at INTEGER,
    updated_at integer
);