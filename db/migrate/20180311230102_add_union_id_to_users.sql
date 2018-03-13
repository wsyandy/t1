ALTER TABLE users ADD COLUMN union_id INTEGER DEFAULT 0;
CREATE index union_id_on_users on users(union_id);