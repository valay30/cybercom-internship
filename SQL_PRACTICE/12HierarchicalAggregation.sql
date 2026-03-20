create database 12HierarchicalAggregation;  
use 12HierarchicalAggregation;

create table categories (
    category_id int primary key auto_increment,
    category_name varchar(100)
);

create table regions (
    region_id int primary key auto_increment,
    region_name varchar(100)
);

create table sales (
    sale_id int primary key auto_increment,
    category_id int,
    region_id int,
    amount decimal(10,2),
    sale_date date,

    foreign key (category_id) references categories(category_id),
    foreign key (region_id) references regions(region_id)
);

INSERT INTO categories (category_name) VALUES
('Electronics'),
('Furniture'),
('Clothing');

INSERT INTO regions (region_name) VALUES
('North'),
('South'),
('East'),
('West');

INSERT INTO sales (category_id, region_id, amount, sale_date) VALUES
(1,1,1200,'2025-01-05'),
(1,2,900,'2025-01-10'),
(1,3,700,'2025-01-12'),
(2,1,500,'2025-01-14'),
(2,2,650,'2025-01-15'),
(2,4,400,'2025-01-18'),
(3,1,300,'2025-01-20'),
(3,3,450,'2025-01-22'),
(3,4,350,'2025-01-25');

select
    coalesce(c.category_name, 'ALL Categories') as category,
    coalesce(r.region_name, 'ALL Regions') as region,
    sum(s.amount) as total_sales

from sales s
join categories c
    on s.category_id = c.category_id
join regions r
    on s.region_id = r.region_id

group by c.category_name, r.region_name with rollup;