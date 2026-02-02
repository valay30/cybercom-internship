<?php
require_once "Database/FileA.php";
require_once "API/FileB.php";

// Method 1: Fully Qualified Names
$dbConnection = new \Library\Database\Connection();
$apiConnection = new \Library\API\Connection();

echo $dbConnection->connect() . "<br>";
echo $apiConnection->connect() . "<br>";

// Method 2: Using aliases (recommended)
use Library\Database\Connection as DbConnection;
use Library\API\Connection as ApiConnection;

$db = new DbConnection();
$api = new ApiConnection();

echo $db->connect() . "<br>";
echo $api->connect() . "<br>";