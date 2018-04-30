ALTER TABLE activities ADD COLUMN last_activity_id INTEGER ;

CREATE index last_activity_id_on_activities on activities(last_activity_id);