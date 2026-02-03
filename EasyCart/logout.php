<?php
session_start();
// Destroy session
session_destroy();

// Clear Cookies
setcookie("user_logged_in", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");

// Redirect
header("Location: index.php");
exit();
