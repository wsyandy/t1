ALTER TABLE users add third_unionid VARCHAR (255);
ALTER TABLE users add login_type VARCHAR (255);
ALTER TABLE users add third_name VARCHAR (255);

CREATE index third_unionid_on_users on users(third_unionid);
CREATE index third_name_on_users on users(third_name);