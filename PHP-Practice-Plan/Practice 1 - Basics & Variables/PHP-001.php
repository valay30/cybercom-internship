<?php

$greeting = "Hello";       // String
$year = 2026;              // Integer
$isActive = true;          // Boolean
$bool2 = false;            // Boolean
$price = 99.99;            // Float
$items = ["Book", "Pen"];  // Array
$noValue = null;           // Null

class Student {            // Object
    public $name = "Valay";
}

$s = new Student();

echo $greeting . ", the year is " . $year ;
echo "<br>";

echo "Is Active: " . $isActive ;
echo "<br>";

echo "bool2: ". $bool2 ;
echo "<br>";

echo "Price: $" . $price ;
echo "<br>";

echo "Null Value: " . $noValue ;
echo "<br>";

echo "items: ". $items[0] ;
echo "<br>";
echo "items: ". $items[1] ;
echo "<br>";

echo $s->name;

?>