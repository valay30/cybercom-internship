<?php

class Employee
{
    public $name;
    public $salary;

    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->salary = $salary;
    }

    public function getDetails()
    {
        return "Name: " . $this->name . ", Salary: " . $this->salary;
    }
}

class Manager extends Employee
{
    public $department;

    public function __construct($name, $salary, $department)
    {
        parent::__construct($name, $salary);
        $this->department = $department;
    }

    public function getDetails()
    {
        // Overriding the method to include department
        return parent::getDetails() . ", Department: " . $this->department;
    }
}

// Create a Manager object
$manager = new Manager("Valay", 90000, "IT");

// Output the details
echo $manager->getDetails();

?>