create database 08TimeSeriesAggregation;
use 08TimeSeriesAggregation;


create table customers(
	customer_id int primary key auto_increment,
    name varchar(128)
);

create table transactions(
	transaction_id int primary key auto_increment,
    customer_id int,
    amount decimal(10,2),
    transaction_date date,
    
    foreign key (customer_id) references customers(customer_id)
);

insert into customers (name) values
('Valay'),('Meet'),('Soham'),('Abhi'),('Harsh');

insert into transactions (customer_id, amount, transaction_date) values
(1, 120.50, '2024-01-10'),
(2, 250.00, '2024-01-15'),
(3, 175.75, '2024-02-05'),
(1, 300.00, '2024-02-20'),
(4, 220.40, '2024-03-12'),
(2, 180.60, '2024-03-25'),
(5, 500.00, '2024-04-08'),
(3, 320.90, '2024-04-19'),
(1, 210.00, '2024-05-03'),
(4, 410.25, '2024-05-27'),
(2, 150.00, '2024-06-14'),
(5, 275.80, '2024-06-30'),

(1, 340.50, '2024-07-07'),
(3, 220.00, '2024-07-21'),
(4, 180.00, '2024-08-11'),
(2, 390.40, '2024-08-28'),
(5, 260.60, '2024-09-04'),
(1, 310.00, '2024-09-23'),
(3, 420.75, '2024-10-09'),
(2, 200.00, '2024-10-29'),
(4, 350.50, '2024-11-13'),
(5, 275.00, '2024-11-25'),
(1, 500.00, '2024-12-05'),
(3, 150.25, '2024-12-19'),

(2, 210.00, '2025-01-10'),
(4, 330.75, '2025-01-22'),
(5, 410.00, '2025-02-03'),
(1, 275.50, '2025-02-17'),
(3, 390.00, '2025-03-08'),
(2, 180.00, '2025-03-21');


select * from customers;
select * from transactions;

with monthly_revenue as (
	select date_format(transaction_date,'%Y-%m') as month,
    year(transaction_date) as year,
    month(transaction_date) as month_number,
    sum(amount) as revenue
    
    from transactions where transaction_date >= curdate() - interval 24 month
    group by date_format(transaction_date, '%Y-%m'), year(transaction_date), month(transaction_date)
)

select 
	month,
    revenue,
    sum(revenue) over(order by year, month_number) as running_total,
    sum(revenue) over(partition by year order by month_number) as ytd_revenue,
    lag(revenue,1) over(order by year, month_number) as previous_month_revenue
    
from monthly_revenue
order by year, month_number;
