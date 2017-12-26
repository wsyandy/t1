CREATE TABLE wap_visit_histories(
  id serial PRIMARY KEY not NULL ,
  wap_visit_id INTEGER ,
  ip VARCHAR (255),
  visit_num INTEGER ,
  down_num INTEGER ,
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE index ip_on_wap_visit_histories on wap_visit_histories(ip);
CREATE index wap_visit_id_on_wap_visit_histories on wap_visit_histories(wap_visit_id);


CREATE TABLE wap_visits(
  id serial PRIMARY KEY not NULL ,
  uri VARCHAR (255),
  visit_at INTEGER ,
  down_num INTEGER ,
  visit_num INTEGER,
  sem VARCHAR (255),
  created_at INTEGER ,
  updated_at INTEGER
);

CREATE  index uri_on_wap_visits on wap_visits(uri);
CREATE  index visit_at_on_wap_visits on wap_visits(visit_at);
CREATE  index  down_num_on_wap_visits on wap_visits(down_num);
