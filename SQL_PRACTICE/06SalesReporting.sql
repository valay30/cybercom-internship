create database 06SalesReporting;
use 06SalesReporting;

create table orders(
	order_id int primary key auto_increment,
    customer_id int not null,
    order_date date not null,
    status enum('completed','pending','cancelled') not null
);

create table categories(
	category_id int primary key auto_increment,
    category_name varchar(128) not null
);

create table products(
	product_id int primary key auto_increment,
    product_name varchar(128) not null,
    category_id int not null,
    price decimal(10,2) not null,
    
    constraint fk_product_category foreign key (category_id) references categories(category_id)
);

create table order_items(
	order_item_id int primary key auto_increment,
    order_id int not null,
    product_id int not null,
    quantity int not null,
    unit_price decimal(10,2) not null,
    
    constraint fk_oi_order foreign key (order_id) references orders(order_id),
    constraint fk_oi_product foreign key (product_id) references products(product_id)
);

insert into categories (category_name) values
('Electronics'),
('Accessories'),
('Footwear'),
('Apparel');

insert into products (product_name, category_id, price) values
('iPhone 17', 1, 134900),
('Dell Laptop', 1, 89000),
('Sony Headphones', 1, 29999),
('Phone Case', 2, 999),
('Laptop Bag', 2, 2999),
('Screen Guard', 2, 499),
('Nike Shoes', 3,  12000),
('Adidas Runners', 3, 8999),
('Levi Jeans', 4, 4500),
('Cotton T-Shirt', 4, 999);

insert into orders (customer_id, order_date, status) values
(1, '2026-01-05', 'completed'),
(2, '2026-01-12', 'completed'),
(3, '2026-02-03', 'pending'),
(4, '2026-02-18', 'cancelled'),
(5, '2026-03-07', 'completed'),
(6, '2026-03-22', 'pending'),
-- Q2 (Apr-Jun)
(7, '2026-04-10', 'completed'),
(8, '2026-04-25', 'cancelled'),
(9, '2026-05-14', 'completed'),
(10,'2026-05-30', 'pending'),
(11,'2026-06-08', 'completed'),
(12,'2026-06-19', 'cancelled'),
-- Q3 (Jul-Sep)
(13,'2026-07-03', 'completed'),
(14,'2026-07-21', 'pending'),
(15,'2026-08-11', 'completed'),
(16,'2026-08-27', 'cancelled'),
(17,'2026-09-05', 'completed'),
(18,'2026-09-18', 'pending'),
-- Q4 (Oct-Dec)
(19,'2026-10-02', 'completed'),
(20,'2026-10-17', 'pending'),
(21,'2026-11-06', 'completed'),
(22,'2026-11-22', 'cancelled'),
(23,'2026-12-01', 'completed'),
(24,'2026-12-15', 'pending');


insert into order_items (order_id, product_id, quantity, unit_price) values
-- Q1 orders
(1,  1, 1, 134900), (1,  4, 2, 999),
(2,  2, 1,  89000), (2,  5, 1, 2999),
(3,  3, 1,  29999), (3,  6, 2,  499),
(4,  7, 2,  12000), (4,  9, 1,  4500),
(5,  1, 1, 134900), (5,  4, 1,   999),
(6,  8, 1,   8999), (6, 10, 3,   999),
-- Q2 orders
(7,  2, 1,  89000), (7,  5, 2,  2999),
(8,  1, 1, 134900), (8,  6, 3,   499),
(9,  3, 2,  29999), (9,  4, 2,   999),
(10, 7, 1,  12000), (10, 9, 2,  4500),
(11, 1, 1, 134900), (11, 5, 1,  2999),
(12, 8, 2,   8999), (12,10, 2,   999),
-- Q3 orders
(13, 2, 1,  89000), (13, 4, 3,   999),
(14, 3, 1,  29999), (14, 6, 2,   499),
(15, 1, 1, 134900), (15, 5, 1,  2999),
(16, 7, 2,  12000), (16, 9, 1,  4500),
(17, 2, 1,  89000), (17,10, 2,   999),
(18, 8, 1,   8999), (18, 4, 2,   999),
-- Q4 orders
(19, 1, 1, 134900), (19, 5, 2,  2999),
(20, 3, 1,  29999), (20, 6, 3,   499),
(21, 2, 1,  89000), (21, 4, 2,   999),
(22, 7, 2,  12000), (22, 9, 2,  4500),
(23, 1, 1, 134900), (23, 5, 1,  2999),
(24, 8, 1,   8999), (24,10, 3,   999);



select * from order_items;

WITH

base AS (
    SELECT
        c.category_name,
        YEAR(o.order_date)                              AS yr,
        QUARTER(o.order_date)                           AS qtr,
        o.status,
        o.order_id,
        oi.quantity * oi.unit_price                     AS line_revenue
    FROM orders o
    JOIN order_items oi ON o.order_id   = oi.order_id
    JOIN products    p  ON oi.product_id = p.product_id
    JOIN categories  c  ON p.category_id = c.category_id
),

aggregated AS (
    SELECT
        category_name,
        CONCAT('Q', qtr, ' ', yr)                      AS quarter,
        yr,
        qtr,

        -- Completed
        COUNT(DISTINCT CASE
            WHEN status = 'completed' THEN order_id
        END)                                            AS completed_orders,

        COALESCE(SUM(CASE
            WHEN status = 'completed' THEN line_revenue
        END), 0)                                        AS completed_amount,

        -- Pending
        COUNT(DISTINCT CASE
            WHEN status = 'pending'   THEN order_id
        END)                                            AS pending_orders,

        COALESCE(SUM(CASE
            WHEN status = 'pending'   THEN line_revenue
        END), 0)                                        AS pending_amount,

        -- Cancelled 
        COUNT(DISTINCT CASE
            WHEN status = 'cancelled' THEN order_id
        END)                                            AS cancelled_orders,

        COALESCE(SUM(CASE
            WHEN status = 'cancelled' THEN line_revenue
        END), 0)                                        AS cancelled_amount,

        -- ── Totals 
        COUNT(DISTINCT order_id)                        AS total_orders,
        SUM(line_revenue)                               AS total_revenue

    FROM base
    GROUP BY category_name, yr, qtr
)

SELECT
    category_name                                       AS category,
    quarter,
    completed_orders,
    completed_amount,
    pending_orders,
    pending_amount,
    cancelled_orders,
    cancelled_amount,
    total_orders,
    total_revenue

FROM aggregated
ORDER BY category_name, yr, qtr;