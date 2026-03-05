create database 03CustomerAnalysis;
use 03CustomerAnalysis;

create table customers(
	customer_id int primary key auto_increment,
    name  varchar(128)
);

create table orders(
	order_id int primary key auto_increment,
    customer_id int,
    order_date date,
    total_amount decimal(10,2),
    
    constraint fk_customer foreign key (customer_id) references customers(customer_id)
);

create table order_items(
	order_item_id int primary key auto_increment,
    order_id int,
    product_id int not null,
    quantity int,
    price decimal(10,2),
    
    constraint fk_order_id foreign key (order_id) references orders(order_id) on delete cascade
); 


insert into customers (name) values 
('Valay Patel'),
('Riya Sharma'),
('Karan Mehta'),
('Neha Jain'),
('Rahul Singh'),
('Priya Desai');

insert into orders (customer_id,order_date,total_amount) values
(1,'2026-02-10',1200),
(1,'2026-02-20',1800),
(2,'2026-02-12',500),
(2,'2026-02-25',700),
(3,'2026-02-15',2500),
(3,'2026-02-28',1500),
(4,'2026-02-18',400),
(5,'2026-02-22',900),
(6,'2026-02-26',3000),
(6,'2026-03-01',2000);

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 101, 2, 300),
(1, 102, 2, 300),
(2, 103, 3, 400),
(2, 104, 2, 300),
(3, 105, 1, 200),
(3, 106, 1, 300),
(4, 107, 2, 200),
(4, 108, 1, 300),
(5, 109, 3, 500),
(5, 110, 2, 500),
(6, 111, 3, 300),
(6, 112, 2, 300),
(7, 113, 2, 100),
(7, 114, 1, 200),
(8, 115, 3, 200),
(8, 116, 1, 300),
(9, 117, 2, 1000),
(9, 118, 2, 500),
(10, 119, 2, 600),
(10, 120, 2, 400);

select * from customers;
select * from orders;
select * from order_items;

with customer_spending as(
	select c.customer_id, c.name, count(distinct o.order_id) as purchase_count, sum(o.total_amount) as total_spending
    from customers c
    join orders o on c.customer_id = o.customer_id
    where o.order_date >= curdate() - interval 30 day
    group by c.customer_id, c.name
),

overall_avg as(
	select avg(total_spending) as avg_spending
    from customer_spending
)

select 
	cs.customer_id, cs.name, cs.purchase_count, cs.total_spending, 
    round(oa.avg_spending) as overall_average_spending, 
    round(cs.total_spending - oa.avg_spending, 2) as above_average_spend
    from customer_spending cs
    cross join overall_avg oa
    where cs.total_spending > oa.avg_spending
    order by above_average_spend desc;


