<?php
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

// Handle Login (Simulated)
if (isset($_POST['login'])) {
    $_SESSION['username'] = 'InternName';

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login Page</h2>

<form method="post">
    <button type="submit" name="login">Start Session (Login)</button>
</form>

</body>
</html>
