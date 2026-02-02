<?php
class Employee
{
    public $name;
    private $salary;

    // Constrcutor to initialize name and salary
    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->salary = $salary;
    }
}

// Create an object
$emp1 = new Employee("Valay", 60000);

echo "Employee Name: " . $emp1->name . "<br>";
// echo "Salary: " . $emp1->salary; // error because salary is private

?>