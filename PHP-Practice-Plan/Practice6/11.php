<?php

setcookie("user_preference", "dark_mode", time() + 3600, "/");

?>
<!DOCTYPE html>
<html>

<head>
    <title>PHP-011 Cookies</title>
</head>

<body>
    <h2>Cookie Practice</h2>
    <?php
    // Check if the cookie is set
    if (isset($_COOKIE['user_preference'])) {
        echo "User Preference: " . $_COOKIE['user_preference'];
    } else {
        echo "User preference cookie not set.";
    }
    ?>
</body>

</html>