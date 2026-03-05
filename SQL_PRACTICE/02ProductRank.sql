create database 02ProductRank;
use 02ProductRank;

create table products(
	product_id int primary key,
    category_id int,
    product_name varchar(128),
    revenue decimal(10,2)
);

INSERT INTO products VALUES
(1,1,'product a',5000),
(2,1,'product b',4000),
(3,1,'product c',4000),
(4,1,'product d',3000),
(5,2,'product e',7000),
(6,2,'product f',6500),
(7,2,'product g',6500),
(8,2,'product h',5000),
(9,1,'product i',2500),
(10,1,'product j',2000),
(11,1,'product k',1500),
(12,2,'product l',4500),
(13,2,'product m',3000),
(14,2,'product n',2000);

select * from products;

SELECT * FROM ( SELECT product_id, category_id, product_name, revenue,
        DENSE_RANK() OVER (
            PARTITION BY category_id 
            ORDER BY revenue DESC
        ) AS rank_in_category
    FROM products
) ranked_products
WHERE rank_in_category <= 3
ORDER BY category_id, rank_in_category;