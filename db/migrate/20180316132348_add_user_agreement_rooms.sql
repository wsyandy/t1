ALTER table rooms add COLUMN user_agreement_num INTEGER DEFAULT 0;

CREATE index user_agreement_num_on_rooms on rooms(user_agreement_num);