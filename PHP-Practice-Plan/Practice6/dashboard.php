<?php
session_start();

// Protect page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Session Management</h2>

<p>
    Welcome <strong><?php echo $_SESSION['username']; ?></strong>
</p>

<p>
    Session ID: <?php echo session_id(); ?>
</p>

<!-- Logout Button -->
<form method="post" action="logout.php">
    <button type="submit">Logout</button>
</form>

</body>
</html>
