ALTER TABLE activities add COLUMN type INTEGER DEFAULT 1;

CREATE index type_on_activities on activities(type);
CREATE index status_on_activities on activities(status);