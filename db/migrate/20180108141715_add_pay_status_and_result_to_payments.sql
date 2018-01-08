alter table payments add pay_status integer;
create index pay_status_on_payments on payments(pay_status);
alter table payments add result integer;