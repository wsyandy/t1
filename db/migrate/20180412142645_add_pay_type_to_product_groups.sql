ALTER TABLE product_groups add COLUMN pay_type INTEGER DEFAULT 0;
CREATE index pay_type_on_product_groups on product_groups(pay_type);