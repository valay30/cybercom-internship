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


// Create a variable $day. Use switch to print 'Weekend' for Sat/Sun and 'Weekday' for Mon-Fri.
$day = "Monday";

echo $day . " - ";

switch ($day) {

    case "Saturday":
        echo "Weekend";
        break;

    case "Sunday":
        echo "Weekend";
        break;

    case "Monday":
        echo "Weekday";
        break;

    case "Tuesday":
        echo "Weekday";
        break;

    case "Wednesday":
        echo "Weekday";
        break;

    case "Thursday":
        echo "Weekday";
        break;

    case "Friday":
        echo "Weekday";
        break;

    default:
        echo "Invalid Day";
}

?>