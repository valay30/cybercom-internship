create database 05MarketBasketAnalysis;
use 05MarketBasketAnalysis;


create table products(
	product_id int primary key auto_increment,
    product_name varchar(128),
    category varchar(64)
);

create table orders(
	order_id int primary key auto_increment,
    customer_id int,
    order_date date
);

create table order_items(
	order_item_id int primary key auto_increment,
    order_id int,
    product_id int,
    
    constraint fk_orderItem_orderId foreign key (order_id) references orders(order_id),
    constraint fk_orderItem_productId foreign key (product_id) references products(product_id)
);

INSERT INTO products (product_name, category) VALUES
('iPhone 17',    'Electronics'),
('Phone Case',   'Accessories'),
('Charger',      'Accessories'),
('Headphones',   'Electronics'),
('Screen Guard', 'Accessories'),
('Dell Laptop',  'Electronics'),
('Laptop Bag',   'Accessories'),
('Mouse',        'Accessories'),
('Keyboard',     'Accessories'),
('Monitor',      'Electronics');

INSERT INTO orders (customer_id, order_date) VALUES 
(1,'2026-01-01'),(2,'2026-01-02'),(3,'2026-01-03'),(4,'2026-01-04'),
(5,'2026-01-05'),(6,'2026-01-06'),(7,'2026-01-07'),(8,'2026-01-08'),
(9,'2026-01-09'),(10,'2026-01-10'),(11,'2026-01-11'),(12,'2026-01-12'),
(13,'2026-01-13'),(14,'2026-01-14'),(15,'2026-01-15'),(16,'2026-01-16'),
(17,'2026-01-17'),(18,'2026-01-18'),(19,'2026-01-19'),(20,'2026-01-20');

INSERT INTO order_items (order_id, product_id) VALUES
(1,1),(1,2),(1,3),
(2,1),(2,2),(2,3),
(3,1),(3,2),(3,3),
(4,1),(4,2),(4,3),
(5,1),(5,2),(5,3),
(6,1),(6,2),(6,3),
(7,1),(7,2),(7,3),
(8,1),(8,2),(8,3),
(9,1),(9,2),(9,3),
(10,1),(10,2),(10,3),
(11,1),(11,2),(11,3),
(12,1),(12,2),
(13,6),(13,7),(13,8),
(14,6),(14,7),(14,8),
(15,6),(15,7),(15,9),
(16,6),(16,7),(16,9),
(17,6),(17,8),(17,9),
(18,6),(18,7),(18,10),
(19,4),(19,5),
(20,4),(20,5);

select 
p1.product_name as product_1,
p2.product_name as product_2,
count(oi1.order_id) as time_bought_together,
round(count(oi1.order_id) * 100 / (select count(distinct order_id) from orders),2) as percentage_of_total_orders

from order_items oi1
join order_items oi2
on oi1.order_id = oi2.order_id
and oi1.product_id < oi2.product_id

join products p1 on oi1.product_id = p1.product_id
join products p2 on oi2.product_id = p2.product_id

group by 
	oi1.product_id,
    oi2.product_id,
    p1.product_name,
    p2.product_name

having count(oi1.order_id) > 10
order by time_bought_together desc;




