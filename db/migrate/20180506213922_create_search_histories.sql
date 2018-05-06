CREATE TABLE search_histories(
  id serial PRIMARY key not null,
  word VARCHAR (255),
  type VARCHAR (255),
  num INTEGER ,
  created_at INTEGER
);

CREATE index word_on_search_histories on search_histories(word);