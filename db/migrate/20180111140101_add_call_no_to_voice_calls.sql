alter table voice_calls add call_no varchar(255);
create index call_no_on_voice_calls on voice_calls(call_no);