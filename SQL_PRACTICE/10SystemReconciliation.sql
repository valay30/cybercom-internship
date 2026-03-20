create database 10SystemReconciliation;
use 10SystemReconciliation;

create table system1_inventory(
	product_id int primary key auto_increment,
    product_name varchar(128),
    stock int
);

create table system2_inventory(
	product_id int primary key auto_increment,
    product_name varchar(128),
    stock int
);

insert into system1_inventory (product_id, product_name, stock) values
(1, 'Laptop', 50),
(2, 'Smartphone', 30),
(3, 'Mouse', 20),
(4, 'Keyboard', 15),
(5, 'Monitor', 10);

insert into system2_inventory (product_id, product_name, stock) values
(1, 'Laptop', 50),        
(2, 'Smartphone', 25),    
(3, 'Mouse', 20),         
(6, 'Tablet', 12),        
(5, 'Monitor', 8);

select 
	coalesce(s1.product_id, s2.product_id) as product_id,
    s1.stock as system1_stock,
    s2.stock as system2_stock,
    
    case 
		when s1.product_id is null then 'Missing in system1'
		when s2.product_id is null then 'Missing in system2'
        when s1.stock = s2.stock then 'match'
        else 'stock mismatch'
	end as status
    
from system1_inventory s1
left join system2_inventory s2
	on s1.product_id = s2.product_id
    
union

select 
	coalesce(s1.product_id, s2.product_id),
    s1.stock,
    s2.stock,
	
    case
		when s1.product_id is null then 'Missing in system1'
		when s2.product_id is null then 'Missing in system2'
        when s1.stock = s2.stock then 'match'
        else 'stock mismatch'
	end as status
    
from system1_inventory s1
right join system2_inventory s2
	on s1.product_id = s2.product_id;