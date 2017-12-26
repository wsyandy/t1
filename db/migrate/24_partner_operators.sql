CREATE TABLE partner_operators
(
  id serial PRIMARY key not null,
  partner_id integer,
  operator_id integer,
  created_at integer,
  updated_at integer
);

create index operator_id_on_partner_operators on partner_operators(operator_id);
create index partner_id_on_partner_operators on partner_operators(partner_id);

