create database 09ApiResponseFormatting;
use 09ApiResponseFormatting;

create table customers(
	customer_id int primary key auto_increment,
    name varchar(64),
    email varchar(128)
);

create table products(
	product_id int primary key auto_increment,
    product_name varchar(128),
    price decimal(10,2)
);

create table orders(
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

insert into customers (name, email) values
('Valay Patel',  'valay@email.com'),
('Riya Sharma',  'riya@email.com'),
('Karan Mehta',  'karan@email.com'),
('Neha Jain',    'neha@email.com');   

insert into products (product_name, price) values
('iPhone 17',        134900),
('Phone Case',          999),
('Dell Laptop',       89000),
('Laptop Bag',         2999),
('Sony Headphones',   29999);

insert into orders (customer_id, order_date) values
(1, '2026-01-10'),    
(1, '2026-02-15'),    
(2, '2026-01-20'),    
(3, '2026-02-05');    

insert into order_items (order_id, product_id, quantity) values
(1, 1, 1),    
(1, 2, 2),    
(2, 3, 1),    
(2, 4, 1),    
(3, 5, 1),    
(3, 2, 1),    
(4, 1, 1);    

select c.customer_id,c.name,
	json_arrayagg(
		json_object(
			'order_id', o.order_id,
            'order_date', o.order_date,
            'items',
            (
					select json_arrayagg(
						json_object(
							'product_id', p.product_id,
							'product_name', p.product_name,
							'price', p.price,
							'quantity', oi.quantity
                        )
                    )
                    from order_items oi
                    join products p
                    on oi.product_id = p.product_id
                    where oi.order_id = o.order_id
			)
        )
	) as orders
    
    from customers c
    join orders o
		on c.customer_id = o.customer_id
	
    group by c.customer_id, c.name;