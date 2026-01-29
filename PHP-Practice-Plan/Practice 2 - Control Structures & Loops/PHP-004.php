<?php

// Use for loop to print a multiplication table for 5.
echo "<h3>Multiplication Table for 5 (for loop):</h3>";
for ($i = 1; $i <= 10; $i++) {
    echo "5 x $i = " . (5 * $i) . "<br>";
}

// Multiplication Table of 5 using while loop
echo "<h3>Multiplication Table for 5 (while loop):</h3>";
$i = 1;
while ($i <= 10) {
    echo "5 x $i = " . (5 * $i) . "<br>";
    $i++;
}

// Use foreach to iterate through a student array and print 'Key: Value'.
$student = [
    "Name" => "Valay",
    "Age" => 23
];

echo "<h3>Student Details:</h3>";
foreach ($student as $key => $value) {
    echo "$key: $value<br>";
}

// do while loop
echo "<h3>do while loop</h3>";
$i = 10;

do {
    echo $i;
    $i++;
} while ($i < 5);


?>