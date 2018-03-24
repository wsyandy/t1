CREATE TABLE banned_words (
id serial PRIMARY key not null,
word VARCHAR (255),
created_at INTEGER
);

CREATE index word_on_banned_words on banned_words(word);