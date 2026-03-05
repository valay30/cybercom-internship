create database 04PriceVolatilityTracking;
use 04PriceVolatilityTracking;

create table products(
	product_id int primary key auto_increment,
    product_name varchar(128) not null,
    category varchar(128),
    created_at datetime default current_timestamp
);

create table product_price_history (
	price_id int primary key auto_increment,
    product_id int not null,
    price decimal(10,2) not null,
    price_date date not null,
    created_at datetime default current_timestamp,
    
    constraint fk_product foreign key (product_id) references products(product_id) on delete cascade
);

insert into products (product_name,category) values
('iPhone 17','Electronics'),
('Samsung TV','Electronics'),
('Nike Shoes','Footwear'),
('Sony Headphones','Electronics'),
('Jeans','Cloth'),
('Dell Laptop','Electronics');

insert into product_price_history (product_id,price,price_date) values
(1, 134900.00, '2025-10-01'),
(1, 129900.00, '2025-12-15'),
(1, 124900.00, '2026-02-20'),
(2, 85000.00,  '2025-09-01'),
(2, 79000.00,  '2025-11-20'),
(2, 72000.00,  '2026-01-10'),
(3, 12000.00,  '2025-08-01'),
(3, 13500.00,  '2025-10-10'),
(4, 29999.00,  '2025-10-05'),
(4, 27999.00,  '2025-12-25'),
(4, 25999.00,  '2026-02-15'),
(5, 4500.00,   '2025-09-15'),
(5, 4999.00,   '2026-01-20'),
(6, 95000.00,  '2025-07-01'),
(6, 89000.00,  '2025-11-01'),
(6, 82000.00,  '2026-02-28');


with price_with_neighbours as(
	-- 1: For every price record, look backwards and forwards
    select p.product_name, p.category, ph.price_date, ph.price as current_price,
    
	-- Previous price: the most recent price BEFORE this row
    lag(ph.price) over(
		partition by ph.product_id
		order by ph.price_date
        ) as prev_price,

	-- Next price: the upcoming price AFTER this row (if any)
    lead(ph.price) over(
		partition by ph.product_id
		order by ph.price_date
        ) as next_price,

	-- Previous date: useful for showing when last change happened
    lag(ph.price_date) over (
		partition by ph.product_id
		order by ph.price_date
        ) as prev_date
	
    from product_price_history ph
    join products p on ph.product_id = p.product_id
),

price_changes as (
    -- 2: Calculate % change and label direction ──
    select product_name, category, price_date, prev_date, prev_price, current_price, next_price,

        -- Percentage change from previous price
        ROUND( ((current_price - prev_price) / prev_price) * 100 , 2) as pct_change

    from price_with_neighbours
    where prev_price is not null   -- exclude the very first price entry (no "previous" exists)
)

-- 3: Filter for changes in last 90 days + present results ──
select product_name, category, prev_date as price_was_set_on, price_date as changed_on, prev_price, current_price, next_price, CONCAT(pct_change, '%') as pct_change
from price_changes
where price_date >= CURDATE() - INTERVAL 90 DAY
order by ABS(pct_change) desc, product_name, price_date;