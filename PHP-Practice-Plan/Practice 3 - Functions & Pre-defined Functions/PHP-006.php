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

echo "<br>";

//More built-in functions
$greeting = "Hello World";

echo "Length of string: " . strlen($greeting) . "<br>";
echo "Position of 'World': " . strpos($greeting, "World") . "<br>";
echo "Uppercase: " . strtoupper($greeting) . "<br>";
echo "Lowercase: " . strtolower($greeting) . "<br>";

// Math Functions
echo "<h3>Math Functions</h3>";
$num = -5.7;
echo "Original number: $num<br>";
echo "Absolute value: " . abs($num) . "<br>";
echo "Round: " . round($num) . "<br>";
echo "Ceil: " . ceil($num) . "<br>";
echo "Floor: " . floor($num) . "<br>";
echo "Random number (1-100): " . rand(1, 100) . "<br>";
echo "Min value (1, 5, 9): " . min(1, 5, 9) . "<br>";
echo "Max value (1, 5, 9): " . max(1, 5, 9) . "<br>";

// More Array Functions
echo "<h3>More Array Functions</h3>";
$fruits = ["Apple", "Banana", "Cherry", "Orange"];
echo "Original Fruits: " . implode(", ", $fruits) . "<br>";

echo "Count: " . count($fruits) . "<br>";

$popped = array_pop($fruits); // Remove last
echo "Remaining after pop: " . implode(", ", $fruits) . "<br>";

array_unshift($fruits, "Mango"); // Add to beginning
echo "After unshift : " . implode(", ", $fruits) . "<br>";

$reversed = array_reverse($fruits);
echo "Reversed: " . implode(", ", $reversed) . "<br>";

// JSON Functions
echo "<h3>JSON Functions</h3>";
$assocArray = ["name" => "John", "age" => 30];
$jsonString = json_encode($assocArray);
echo "Encoded JSON: " . $jsonString . "<br>";
$decodedArray = json_decode($jsonString, true);
echo "Decoded Name: " . $decodedArray['name'] . "<br>";

?>