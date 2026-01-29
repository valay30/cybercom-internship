<?php
// Enable strict typing
declare(strict_types=1);

// Global variable
$tax = 18;

function calculateTotal(float $price, int $qty): float
{
    // Local variable
    $total = $price * $qty;

    // Access global variable
    global $tax;

    $totalWithTax = $total + ($total * $tax / 100);

    return $totalWithTax;
}

$result = calculateTotal(500.50, 2);

echo "Total Amount : ₹" . $result;
?>
