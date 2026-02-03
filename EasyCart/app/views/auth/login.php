<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Login / Signup</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <!-- Header Section -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main>
        <section class="auth-container">
            <!-- Tab Navigation -->
            <div class="auth-tabs">
                <button class="tab-btn active" onclick="showTab('login')">Login</button>
                <button class="tab-btn" onclick="showTab('signup')">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div id="login-tab" class="tab-content active">
                <form action="login.php<?php echo $redirectUrl !== 'index.php' ? '?redirect=' . urlencode($redirectUrl) : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password"
                            required>
                    </div>

                    <div class="form-group">
                        <button type="submit">Login</button>
                    </div>
                </form>
            </div>

            <!-- Signup Form -->
            <div id="signup-tab" class="tab-content">
                <form action="login.php<?php echo $redirectUrl !== 'index.php' ? '?redirect=' . urlencode($redirectUrl) : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="signup">

                    <div class="form-group">
                        <label for="signup-fullname">Full Name</label>
                        <input type="text" id="signup-fullname" name="fullname" placeholder="Enter your full name"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" placeholder="Create a password"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="signup-confirm-password">Confirm Password</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password"
                            placeholder="Confirm your password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit">Sign Up</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/login.js"></script>
</body>

</html>