CREATE TABLE word_visits (
    id serial PRIMARY KEY NOT NULL ,
    word VARCHAR (255),
    sem VARCHAR (255),
    visit_at INTEGER,
    visit_num INTEGER,
    down_num INTEGER,
    created_at INTEGER ,
    updated_at INTEGER
);



CREATE TABLE word_visit_histories(
  id serial PRIMARY KEY not NULL ,
  word_visit_id INTEGER ,
  ip VARCHAR (255),
  visit_num INTEGER ,
  down_num INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index ip_on_word_visit_histories on word_visit_histories(ip);
CREATE index word_visit_id_on_word_visit_histories on word_visit_histories(word_visit_id);