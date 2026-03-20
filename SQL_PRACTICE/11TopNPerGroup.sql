create table customers (
    customer_id SERIAL primary key,
    name varchar(100)
);

create table orders (
    order_id SERIAL primary key,
    customer_id int,
    order_date date,
    amount numeric(10,2),
    foreign key (customer_id) references customers(customer_id)
);

INSERT INTO customers (name) VALUES
('Valay'),
('Shrey'),
('Abhi'),
('Het');

INSERT INTO orders (customer_id, order_date, amount) VALUES
(1, '2025-01-05', 120.50),
(1, '2025-01-20', 200.00),
(1, '2025-02-02', 75.25),
(1, '2025-02-18', 310.40),
(1, '2025-03-01', 150.00),
(1, '2025-03-10', 450.00),
(1, '2025-03-15', 80.00),

(2, '2025-01-10', 90.00),
(2, '2025-01-25', 220.00),
(2, '2025-02-14', 140.00),
(2, '2025-03-03', 330.00),
(2, '2025-03-12', 75.00),
(2, '2025-03-18', 190.00),

(3, '2025-01-08', 50.00),
(3, '2025-02-11', 65.00),
(3, '2025-02-20', 110.00),
(3, '2025-03-04', 95.00),

(4, '2025-01-02', 210.00),
(4, '2025-01-19', 180.00),
(4, '2025-02-22', 75.00),
(4, '2025-03-07', 160.00),
(4, '2025-03-16', 220.00),
(4, '2025-03-20', 300.00);

select 
    c.customer_id,
    c.name,
    o.order_id,
    o.order_date,
    o.amount,
    row_number() over (
        partition by c.customer_id 
        order by o.order_date desc
    ) as row_num

from customers c

join lateral (
    select *
    from orders o
    where o.customer_id = c.customer_id
    order by o.order_date desc
    limit 5
) o on true;