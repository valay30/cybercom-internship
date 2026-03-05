CREATE DATABASE 01OrganizationHierarchy;
use 01OrganizationHierarchy;

CREATE TABLE employees (
    employee_id INT PRIMARY KEY,
    employee_name VARCHAR(100) NOT NULL,
    designation VARCHAR(100),
    manager_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_manager FOREIGN KEY (manager_id) REFERENCES employees(employee_id) ON DELETE SET NULL
);

INSERT INTO employees VALUES
(1,'Valay Patel','CEO',NULL, NOW()),
(2,'Riya Patel','CTO',1, NOW()),
(3,'Karan Mehta','CFO',1, NOW()),
(4,'Neha Jain','Engineering Manager',2, NOW()),
(5,'Rahul Singh','Software Engineer',4, NOW()),
(6,'Priya Desai','Software Engineer',4, NOW()),
(7,'Ankit Verma','Finance Analyst',3, NOW());


WITH RECURSIVE org_tree AS (
    -- Root , base
    SELECT employee_id, employee_name, manager_id, 1 AS depth_level, employee_name AS path 
    FROM employees
    WHERE manager_id IS NULL

    UNION ALL
	-- Recursive part
    SELECT e.employee_id, e.employee_name, e.manager_id, ot.depth_level + 1, CONCAT(ot.path,' -> ',e.employee_name)
    FROM employees e
    JOIN org_tree ot
        ON e.manager_id = ot.employee_id
)

SELECT * FROM org_tree ORDER BY path;
select * from employees;


