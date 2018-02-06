create table audio_chapters(
    id serial PRIMARY KEY NOT NULL,
    name VARCHAR (127),
    audio_id INTEGER ,
    rank INTEGER ,
    file VARCHAR (255) ,
    status INTEGER ,
    created_at INTEGER,
    updated_at integer
);