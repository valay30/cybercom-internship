<?php

// Associative array describing a person (Name, Age, Skills).
$person = [
    "Name" => "Valay",
    "Age" => 23,
    "Skills" => ["PHP", "HTML", "CSS","JS","CLOUD"]
];

// print_r to show the raw structure.
echo "<h3>print_r output:</h3>";
echo "<pre>";
print_r($person);
echo "</pre>";

// var_dump to show data types and lengths.
echo "<h3>var_dump output:</h3>";
echo "<pre>";
var_dump($person);
echo "</pre>";

?>