create database 07CorrelatedSubquery;
use 07CorrelatedSubquery;

create table customers(
	customer_id int primary key auto_increment,
    customer_name varchar(64) not null
);

create table products(
	product_id int primary key auto_increment,
    product_name varchar(64) not null,
    category_id int
);

create table purchases(
	purchase_id int primary key auto_increment,
    customer_id int,
    product_id int,
    
    foreign key (customer_id) references customers(customer_id),
    foreign key (product_id) references products(product_id)
    
);

insert into customers (customer_name) values
('Valay Patel'),     
('Riya Sharma'),     
('Karan Mehta'),     
('Neha Jain'),      
('Rahul Singh'); 

insert into products (product_name, category_id) values
('iPhone 17', 1),
('Dell Laptop', 1),
('Phone Case', 2),
('Laptop Bag', 2),
('Nike Shoes', 3),
('Adidas Shoes', 3),
('Levi Jeans', 4),
('Cotton T-Shirt', 4);

insert into purchases (customer_id, product_id) values
(1, 1),   
(1, 3),   
(1, 5),   
(1, 7),  

(2, 2),   
(2, 4),   
(2, 6),   
(2, 8),   

(3, 1),   
(3, 3), 
(3, 7),   

(4, 2),  
(4, 4),   
(4, 5),  

(5, 1);   

select c.customer_id, c.customer_name from customers c 
where not exists (select distinct p.category_id from products p
					where not exists (select 1 from purchases pu
										join products pr on pu.product_id = pr.product_id
                                        where pu.customer_id = c.customer_id
                                        and pr.category_id = p.category_id
                                        )
				);
