create database 13AntiJoinPerformanceComparison;
use 13AntiJoinPerformanceComparison;

create table regions (
    region_id int primary key auto_increment,
    region_name varchar(100)
);
create table customers (
    customer_id int primary key auto_increment,
    name varchar(100),
    region_id int,
    foreign key (region_id) references regions(region_id)
);
create table products (
    product_id int primary key auto_increment,
    product_name varchar(100)
);
create table orders (
    order_id int primary key auto_increment,
    customer_id int,
    order_date date,
    foreign key (customer_id) references customers(customer_id)
);
create table order_items (
    order_item_id int primary key auto_increment,
    order_id int,
    product_id int,
    quantity int,
    foreign key (order_id) references orders(order_id),
    foreign key (product_id) references products(product_id)
);


insert into regions (region_name) values
('north america'),
('europe'),
('asia');

insert into customers (name, region_id) values
('alice',1),
('bob',1),
('charlie',2),
('david',3);

insert into products (product_name) values
('laptop'),
('phone'),
('headphones'),
('keyboard'),
('mouse');

insert into orders (customer_id, order_date) values
(1,'2025-01-10'),
(3,'2025-01-12'),
(4,'2025-01-15');

insert into order_items (order_id, product_id, quantity) values
(1,1,1),
(1,2,2),
(2,3,1),
(3,4,1);

-- using not exists
select p.product_id, p.product_name
from products p
where not exists (
    select 1
    from order_items oi
    join orders o
        on oi.order_id = o.order_id
    join customers c
        on o.customer_id = c.customer_id
    join regions r
        on c.region_id = r.region_id
    where oi.product_id = p.product_id
      and r.region_name = 'north america'
);


-- using left join
select distinct p.product_id, p.product_name
from products p
left join order_items oi
    on p.product_id = oi.product_id
left join orders o
    on oi.order_id = o.order_id
left join customers c
    on o.customer_id = c.customer_id
left join regions r
    on c.region_id = r.region_id
       and r.region_name = 'north america'
where r.region_id is null;






