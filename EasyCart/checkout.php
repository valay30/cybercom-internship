<?php
require_once 'data.php';
session_start();

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += ($item['price'] * $item['qty']);
}

// Shipping cost based on selection (defaulting to Standard)
$shipping_cost = 99;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Checkout</title>
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
        <a href="login.php" class="user-icon"><i class="fa-solid fa-user"></i></a>
    </header>

    <!-- Main Content -->
    <main>
        <h2><i class="fa-solid fa-credit-card"></i> Checkout</h2>

        <div class="cart-container">
            <!-- Left Side: Shipping Options -->
            <section class="cart-items-section">
                <h3><i class="fa-solid fa-user-circle"></i> Personal Details</h3>
                <div class="checkout-details-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name"
                            form="checkoutForm">
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" required placeholder="Enter 10-digit mobile number"
                            form="checkoutForm">
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email ID</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email address"
                            form="checkoutForm">
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address" required placeholder="Enter complete address with pincode"
                            rows="3" form="checkoutForm"></textarea>
                    </div>
                </div>

                <hr>

                <h3><i class="fa-solid fa-truck"></i> Shipping Options</h3>
                <div style="margin-bottom: 20px;">
                    <p>
                        <input type="radio" id="standard" name="shipping" value="99" checked form="checkoutForm">
                        <label for="standard">Standard Delivery (5-7 Business Days) - ₹99</label>
                    </p>
                    <p>
                        <input type="radio" id="express" name="shipping" value="199" form="checkoutForm">
                        <label for="express">Express Delivery (1-2 Business Days) - ₹199</label>
                    </p>
                </div>

                <hr>

                <h3><i class="fa-solid fa-list-check"></i> Review Items</h3>
                <div class="review-items-table">
                    <div class="table-header">
                        <div class="header-item">Image</div>
                        <div class="header-item">Product Name</div>
                        <div class="header-item">Quantity</div>
                        <div class="header-item">Unit Price</div>
                        <div class="header-item">Subtotal</div>
                    </div>
                    <?php if (empty($cart)): ?>
                        <div class="table-row empty-cart">
                            <div class="row-item" colspan="5" style="text-align:center;">Your cart is empty</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart as $id => $item): ?>
                            <div class="table-row">
                                <div class="row-item">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>"
                                        class="product-image">
                                </div>
                                <div class="row-item product-name"><?php echo $item['name']; ?></div>
                                <div class="row-item quantity"><?php echo $item['qty']; ?></div>
                                <div class="row-item unit-price">₹<?php echo number_format($item['price']); ?></div>
                                <div class="row-item subtotal">₹<?php echo number_format($item['price'] * $item['qty']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Right Side: Order Summary -->
            <section class="cart-summary-section">
                <h3><i class="fa-solid fa-receipt"></i> Payment Summary</h3>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Charges</span>
                    <span>₹<?php echo number_format($shipping_cost); ?></span>
                </div>

                <hr>

                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>₹<?php echo number_format($subtotal + $shipping_cost); ?></span>
                </div>

                <form id="checkoutForm" action="orders.php" method="POST"
                    style="border:none; box-shadow:none; padding:0; margin-top:20px; margin-bottom:20px;">
                    <button type="submit" class="checkout-btn">
                        <i class="fa-solid fa-bag-shopping"></i> Place Order
                    </button>
                </form>

                <a href="cart.php" class="continue-shopping">
                    <i class="fa-solid fa-arrow-left"></i> Edit Cart
                </a>
            </section>
        </div>
    </main>

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
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 EasyCart. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms &
                    Conditions</a></p>
        </div>
    </footer>
</body>

</html>