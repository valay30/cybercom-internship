<?php

// Create a variable $marks. Write an if-elseif chain to assign a grade (A, B, C, Fail).
$marks = 85;
$grade = "";

if ($marks >= 90) {
    $grade = "A";
} elseif ($marks >= 75) {
    $grade = "B";
} elseif ($marks >= 50) {
    $grade = "C";
} else {
    $grade = "Fail";
}

echo "Marks: $marks, Grade: $grade ";
echo "<br>";


?>