<?php
require_once 'data.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = $_POST['id'] ?? '';

    if ($action === 'add' && isset($products[$pid])) {
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty']++;
        } else {
            $_SESSION['cart'][$pid] = [
                'name' => $products[$pid]['name'],
                'price' => $products[$pid]['price'],
                'image' => $products[$pid]['image'],
                'qty' => 1
            ];
        }
    } elseif ($action === 'update') {
        $qty = (int) $_POST['qty'];
        if ($qty > 0) {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        } else {
            unset($_SESSION['cart'][$pid]);
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$pid]);
    }

    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += ($item['price'] * $item['qty']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - My Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
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
    <main>
        <h2>My Cart</h2>
        <div class="cart-container">
            <section class="cart-items-section">
                <?php if (empty($_SESSION['cart'])): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['image']; ?>" alt="">
                            <div class="cart-item-details">
                                <h4><?php echo $item['name']; ?></h4>
                                <p>₹<?php echo number_format($item['price']); ?></p>
                            </div>
                            <form action="cart.php" method="POST"
                                style="display:flex; border:none; box-shadow:none; padding:0;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="number" name="qty" value="<?php echo $item['qty']; ?>"
                                    style="width:60px; margin-right:5px;">
                                <button type="submit">Update</button>
                            </form>
                            <div class="cart-item-total">₹<?php echo number_format($item['price'] * $item['qty']); ?></div>
                            <form action="cart.php" method="POST" style="border:none; box-shadow:none; padding:0;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <button type="submit" class="remove-btn"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section class="cart-summary-section">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total</span><span>₹<?php echo number_format($subtotal + 99); ?></span>
                </div>
                <a href="checkout.php"><button class="checkout-btn">Proceed to Checkout</button></a>
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