ALTER TABLE users add COLUMN platform_version VARCHAR (63);
ALTER TABLE users add COLUMN version_name VARCHAR (127);
ALTER TABLE users add COLUMN manufacturer VARCHAR (127);
ALTER TABLE users add COLUMN device_no VARCHAR (256);
ALTER TABLE users add COLUMN client_status INTEGER ;

CREATE index device_no_on_users on users(device_no);