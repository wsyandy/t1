ALTER TABLE complaints add COLUMN music_id INTEGER ;

CREATE  index complainer_id_on_complaints on complaints(complainer_id);
CREATE  index respondent_id_on_complaints on complaints(respondent_id);
CREATE  index room_id_on_complaints on complaints(room_id);
CREATE  index music_id_on_complaints on complaints(music_id);
CREATE index complaint_type_on_complaints on complaints(complaint_type);