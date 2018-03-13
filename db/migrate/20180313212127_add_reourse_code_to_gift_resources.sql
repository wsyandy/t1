ALTER TABLE gift_resources ADD COLUMN resource_code INTEGER DEFAULT 0;
CREATE index resource_code_on_gift_resources on gift_resources(resource_code);