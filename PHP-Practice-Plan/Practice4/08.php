<?php

class Employee
{
    public $name;
    private $salary;

    // Constrcutor to initialize name and salary
    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->setSalary($salary); // Use setter for validation
    }

    // Getter for salary
    public function getSalary()
    {
        return $this->salary;
    }

    // Setter for salary with validation
    public function setSalary($amount)
    {
        if ($amount > 0) {
            $this->salary = $amount;
        } else {
            echo "Invalid salary amount.<br>";
        }
    }
}

// Create an object
$emp = new Employee("Valay", 50000);

// Access public property
echo "Employee: " . $emp->name . "<br>";

// Access private property using getter
echo "Current Salary: " . $emp->getSalary() . "<br>";

// Update salary using setter
$emp->setSalary(60000);
echo "New Salary: " . $emp->getSalary() . "<br>";

// Try setting invalid salary
$emp->setSalary(-1000);
echo "Salary after invalid update: " . $emp->getSalary() . "<br>";
?>