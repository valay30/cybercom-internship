<?php

// String: Create a string '  Hello World  '. Trim it, convert to lowercase, and replace 'World' with 'PHP'.

$str = "  Hello World  ";
echo "Original String: '" . $str . "'<br>";

$trimmed = trim($str);
echo "Trimmed: '" . $trimmed . "'<br>";

$lowerCase = strtolower($trimmed);
echo "Lowercase: '" . $lowerCase . "'<br>";

$final = str_replace("world", "php", $lowerCase);
echo "Replaced: '" . $final . "'<br>";


// Array: Create an array of numbers. Check if '5' exists, add a number, and merge.

$numbers = [1, 3, 5, 7];
echo "<br>Original Array: " . implode(", ", $numbers) . "<br>";

// Check if '5' exists (in_array)
if (in_array(5, $numbers)) {
    echo "Number 5 is found in the array.<br>";
} else {
    echo "Number 5 is not found.<br>";
}

// Add a number (array_push)
array_push($numbers, 9);
echo "After push: " . implode(", ", $numbers) . "<br>";

// Merge with another array (array_merge)
$moreNumbers = [10, 12];
$mergedArray = array_merge($numbers, $moreNumbers);

echo "Merged Array: " . implode(", ", $mergedArray) . "<br>";

?>