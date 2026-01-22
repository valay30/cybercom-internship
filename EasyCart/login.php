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
    <header>
        <h1>EasyCart</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="plp.php">Products</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
        </nav>
        <a href="login.php" class="user-icon" title="Login / Signup"><i class="fa-solid fa-user"></i></a>
    </header>

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
                <form>
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
                <form>
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
                        <input type="password" id="signup-confirm-password" name="confirm-password"
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
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3><i class="fa-solid fa-cart-shopping"></i> EasyCart</h3>
                <p>Your one-stop destination for all your shopping needs. Quality products, fast delivery, and excellent
                    customer service.</p>
                <div class="social-icons">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php"><i class="fa-solid fa-angle-right"></i> Home</a></li>
                    <li><a href="plp.php"><i class="fa-solid fa-angle-right"></i> Products</a></li>
                    <li><a href="cart.php"><i class="fa-solid fa-angle-right"></i> Cart</a></li>
                    <li><a href="orders.php"><i class="fa-solid fa-angle-right"></i> My Orders</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> Help Center</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> Track Order</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> Returns</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> Shipping Info</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul class="contact-info">
                    <li><i class="fa-solid fa-location-dot"></i> 123 Shopping Street, Mumbai, India</li>
                    <li><i class="fa-solid fa-phone"></i> +91 98765 43210</li>
                    <li><i class="fa-solid fa-envelope"></i> support@easycart.com</li>
                    <li><i class="fa-solid fa-clock"></i> Mon - Sat: 9AM - 9PM</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 EasyCart. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms &
                    Conditions</a></p>
        </div>
    </footer>

    <!-- JavaScript for Tab Switching -->
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>

</html>