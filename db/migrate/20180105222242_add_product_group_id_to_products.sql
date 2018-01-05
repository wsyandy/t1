alter table products add product_group_id integer;
create index product_group_id_on_products on products(product_group_id);