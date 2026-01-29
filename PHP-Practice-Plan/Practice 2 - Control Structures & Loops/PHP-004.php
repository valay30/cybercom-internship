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

?>