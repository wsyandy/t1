CREATE TABLE orders
(
    id serial PRIMARY KEY NOT NULL ,
    name VARCHAR (255),
    user_id INTEGER ,
    product_id INTEGER ,
    payment_id INTEGER ,
    status INTEGER ,
    remark VARCHAR (255),
    amount DECIMAL (10, 2),
    created_at INTEGER ,
    updated_at INTEGER
);

CREATE index user_id_on_orders on orders(user_id);
CREATE index product_id_on_orders on orders(product_id);
CREATE index payment_id_on_orders on orders(payment_id);