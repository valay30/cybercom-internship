<?php
class Student
{
    private $first_name;
    private $last_name;

    private function setName($fname, $lname)
    {
        $this->first_name = $fname;
        $this->last_name = $lname;
    }
    // public function getName()
    // {
    //     return $this->first_name . " " . $this->last_name;
    // }

    public function __call($method, $args)
    {
        echo "This is private or non existing method : $method <br>";

        echo "<pre>";
        print_r($args);

        // if (method_exists($this, $method)) {
        //     call_user_func_array([$this, $method], $args);
        // } else {
        //     echo "Method $method not found!";
        // }


        // if (substr($method, 0, 3) == "set") {
        //     $property = strtolower(substr($method, 3));
        //     $this->$property = $args[0];
        //     return;
        // }

        // if (substr($method, 0, 3) == "get") {
        //     $property = strtolower(substr($method, 3));
        //     return $this->$property ?? null;
        // }

        // echo "Method $method not found!";
    }
}

$test = new Student();
$test -> setName("Valay", "Patel");
// echo $test->getName();
// $test->personal();

// echo "<pre>";
// print_r ($test);

// $test->setName("Valay");
// echo $test->getName();
