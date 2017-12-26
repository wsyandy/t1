CREATE TABLE operating_records (
    id serial PRIMARY KEY NOT NULL,
    operator_id INTEGER,
    table_name VARCHAR(255),
    target_id INTEGER,
    action_type VARCHAR(255),
    data text,
    updated_at INTEGER,
    created_at INTEGER
);
create index  operator_id_on_operating_records on operating_records(operator_id);
create index  target_id_on_operating_records on operating_records(target_id);