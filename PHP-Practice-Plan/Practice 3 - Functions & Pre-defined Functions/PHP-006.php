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

?>