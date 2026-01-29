<?php
require_once 'data.php';
session_start();

// AJAX Handler for Shipping Updates
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true' && isset($_POST['action']) && $_POST['action'] === 'update_shipping') {
    header('Content-Type: application/json');

    $cart = $_SESSION['cart'] ?? [];
    $subtotal = 0;

    foreach ($cart as $item) {
        if (isset($item['qty']) && isset($item['price'])) {
            $discount_percent = $item['qty'];
            if ($discount_percent > 50)
                $discount_percent = 50;

            $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
            $subtotal += ($discounted_price * $item['qty']);
        }
    }

    $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);

    // Calculate tax and total
    $taxable_amount = $subtotal + $shipping_cost;
    $tax_amount = $taxable_amount * 0.18;
    $total_amount = $taxable_amount + $tax_amount;

    echo json_encode([
        'success' => true,
        'subtotal' => $subtotal,
        'shipping' => $shipping_cost,
        'tax' => $tax_amount,
        'total' => $total_amount,
        'formatted' => [
            'subtotal' => '₹' . number_format($subtotal, 2),
            'shipping' => '₹' . number_format($shipping_cost, 2),
            'tax' => '₹' . number_format($tax_amount, 2),
            'total' => '₹' . number_format($total_amount, 2)
        ]
    ]);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    if (isset($item['qty']) && isset($item['price'])) {
        $discount_percent = $item['qty'];
        if ($discount_percent > 50)
            $discount_percent = 50;

        $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
        $subtotal += ($discounted_price * $item['qty']);
    }
}

// Calculate Shipping Costs based on Rules
// Standard: Flat 40
$shipping_std = 40;

// Express: Flat 80 OR 10% of subtotal (whichever is lower)
$shipping_express = min(80, $subtotal * 0.10);

// White Glove: Flat 150 OR 5% of subtotal (whichever is lower)
$shipping_white_glove = min(150, $subtotal * 0.05);

// Freight: 3% of subtotal, Minimum 200
$shipping_freight = max(200, $subtotal * 0.03);

// Default shipping cost (Standard)
$shipping_cost = $shipping_std;

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
            <a href="wishlist.php">Wishlist</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
        </nav>
        <?php if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true'): ?>
            <div class="user-info">
                <span><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_COOKIE['user_name']); ?></span>
                <a href="logout.php" class="logout-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        <?php else: ?>
            <a href="login.php" class="user-icon"><i class="fa-solid fa-user"></i></a>
        <?php endif; ?>
    </header>

    <!-- Main Content -->
    <main>
        <h2><i class="fa-solid fa-credit-card"></i> Checkout</h2>

        <div class="cart-container">
            <!-- Left Side: Shipping Options -->
            <section class="cart-items-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;"><i
                            class="fa-solid fa-user-circle"></i> Personal Details</h3>
                    <div id="auto-save-indicator"
                        style="display: none; align-items: center; gap: 6px; color: #10b981; font-size: 0.85rem; font-weight: 500;">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Auto-saved</span>
                    </div>
                </div>
                <div style="height: 2px; background: var(--bg-light); margin-bottom: 24px;"></div>
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
                <div class="shipping-options">
                    <!-- Standard Shipping -->
                    <label class="shipping-option active" for="standard">
                        <input type="radio" id="standard" name="shipping" value="<?php echo $shipping_std; ?>" checked
                            form="checkoutForm" onchange="updateShipping(this)">
                        <div class="shipping-details">
                            <div class="shipping-name">
                                <i class="fa-solid fa-truck"></i>
                                <strong>Standard Shipping</strong>
                            </div>
                            <div class="shipping-time">5-7 Business Days</div>
                            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹40</div>
                        </div>
                    </label>

                    <!-- Express Shipping -->
                    <label class="shipping-option" for="express">
                        <input type="radio" id="express" name="shipping" value="<?php echo $shipping_express; ?>"
                            form="checkoutForm" onchange="updateShipping(this)">
                        <div class="shipping-details">
                            <div class="shipping-name">
                                <i class="fa-solid fa-rocket"></i>
                                <strong>Express Shipping</strong>
                            </div>
                            <div class="shipping-time">1-2 Business Days</div>
                            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹80 OR 10% of
                                subtotal (whichever is lower)</div>
                        </div>
                    </label>

                    <!-- White Glove Delivery -->
                    <label class="shipping-option" for="white-glove">
                        <input type="radio" id="white-glove" name="shipping"
                            value="<?php echo $shipping_white_glove; ?>" form="checkoutForm"
                            onchange="updateShipping(this)">
                        <div class="shipping-details">
                            <div class="shipping-name">
                                <i class="fa-solid fa-hands-holding-circle"></i>
                                <strong>White Glove Delivery</strong>
                            </div>
                            <div class="shipping-time">Scheduled Appointment</div>
                            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹150 OR 5% of
                                subtotal (whichever is lower)</div>
                        </div>
                    </label>

                    <!-- Freight Shipping -->
                    <label class="shipping-option" for="freight">
                        <input type="radio" id="freight" name="shipping" value="<?php echo $shipping_freight; ?>"
                            form="checkoutForm" onchange="updateShipping(this)">
                        <div class="shipping-details">
                            <div class="shipping-name">
                                <i class="fa-solid fa-truck-moving"></i>
                                <strong>Freight Shipping</strong>
                            </div>
                            <div class="shipping-time">7-14 Business Days</div>
                            <div class="shipping-price" style="font-size: 0.8em; color: #666;">3% of subtotal or Minimum
                                ₹200</div>
                        </div>
                    </label>
                </div>


                <hr>

                <h3><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
                <div class="payment-options">
                    <label class="payment-option active" for="upi">
                        <input type="radio" id="upi" name="payment" value="upi" checked form="checkoutForm"
                            onchange="updatePayment(this)">
                        <div class="payment-details">
                            <div class="payment-name">
                                <i class="fa-brands fa-google-pay"></i>
                                <strong>UPI</strong>
                            </div>
                            <div class="payment-desc">Google Pay, PhonePe, Paytm & more</div>
                        </div>
                    </label>

                    <label class="payment-option" for="card">
                        <input type="radio" id="card" name="payment" value="card" form="checkoutForm"
                            onchange="updatePayment(this)">
                        <div class="payment-details">
                            <div class="payment-name">
                                <i class="fa-solid fa-credit-card"></i>
                                <strong>Credit / Debit Card</strong>
                            </div>
                            <div class="payment-desc">Pay securely with your card</div>
                        </div>
                    </label>

                    <label class="payment-option" for="netbanking">
                        <input type="radio" id="netbanking" name="payment" value="netbanking" form="checkoutForm"
                            onchange="updatePayment(this)">
                        <div class="payment-details">
                            <div class="payment-name">
                                <i class="fa-solid fa-building-columns"></i>
                                <strong>Net Banking</strong>
                            </div>
                            <div class="payment-desc">Pay via your bank account</div>
                        </div>
                    </label>
                    <label class="payment-option" for="cod">
                        <input type="radio" id="cod" name="payment" value="cod" form="checkoutForm"
                            onchange="updatePayment(this)">
                        <div class="payment-details">
                            <div class="payment-name">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <strong>Cash on Delivery</strong>
                            </div>
                            <div class="payment-desc">Pay when you receive</div>
                        </div>
                    </label>
                </div>

                <!-- Hidden Payment Fields -->
                <div class="payment-input-container">
                    <div id="upi-fields" class="payment-method-fields" style="display: block;">
                        <div class="form-group">
                            <label for="upi-id">UPI ID</label>
                            <input type="text" id="upi-id" name="upi_id" placeholder="example@upi" form="checkoutForm">
                            <small>Enter your VPA (Virtual Payment Address)</small>
                        </div>
                    </div>

                    <div id="card-fields" class="payment-method-fields" style="display: none;">
                        <div class="form-group">
                            <label for="card-number">Card Number</label>
                            <input type="text" id="card-number" name="card_number" placeholder="0000 0000 0000 0000"
                                maxlength="19" form="checkoutForm">
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1">
                                <label for="card-expiry">Expiry Date</label>
                                <input type="text" id="card-expiry" name="card_expiry" placeholder="MM/YY" maxlength="5"
                                    form="checkoutForm">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label for="card-cvv">CVV</label>
                                <input type="password" id="card-cvv" name="card_cvv" placeholder="123" maxlength="3"
                                    form="checkoutForm">
                            </div>
                        </div>
                    </div>
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
                                <?php
                                $d_percent = $item['qty'];
                                if ($d_percent > 50)
                                    $d_percent = 50;
                                $d_price = $item['price'] * (1 - ($d_percent / 100));
                                $line_total = $d_price * $item['qty'];
                                ?>
                                <div class="row-item unit-price">
                                    <span
                                        style="text-decoration: line-through; font-size: 0.8em; color: #999;">₹<?php echo number_format($item['price'], 2); ?></span><br>
                                    ₹<?php echo number_format($d_price, 2); ?>
                                </div>
                                <div class="row-item subtotal">
                                    ₹<?php echo number_format($line_total, 2); ?>
                                    <div style="font-size: 0.7em; color: #999;"><?php echo $d_percent; ?>% Off</div>
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
                    <span id="summary-subtotal"
                        data-subtotal="<?php echo $subtotal; ?>">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Charges</span>
                    <span id="summary-shipping">₹<?php echo number_format($shipping_cost, 2); ?></span>
                </div>
                <?php
                $tax_amount = ($subtotal + $shipping_cost) * 0.18;
                $total_amount = $subtotal + $shipping_cost + $tax_amount;
                ?>
                <div class="summary-row">
                    <span>GST (18%)</span>
                    <span id="summary-tax">₹<?php echo number_format($tax_amount, 2); ?></span>
                </div>

                <hr>

                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span id="summary-total">₹<?php echo number_format($total_amount, 2); ?></span>
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
                <p>Your one stop destination for all your shopping needs. Quality products, fast delivery, and excellent customer service.</p>
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
            <p>&copy; 2026 EasyCart. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms & Conditions</a></p>
        </div>
    </footer>

    <script src="js/checkout.js"></script>
</body>


</html>