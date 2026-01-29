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

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            // Return info for immediate frontend update (e.g. dynamic discount on PDP)
            echo json_encode([
                'success' => true,
                'newQty' => $_SESSION['cart'][$pid]['qty'],
                'productPrice' => $products[$pid]['price'] // Original Price
            ]);
            exit;
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

    if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
        $subtotal = 0;
        $itemTotal = 0;
        foreach ($_SESSION['cart'] as $cartPid => $item) {
            if (isset($item['qty']) && isset($item['price'])) {
                $discount_percent = $item['qty'];
                if ($discount_percent > 50)
                    $discount_percent = 50;

                $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
                $line_price = $discounted_price * $item['qty'];
                $subtotal += $line_price;

                if ($cartPid === $pid) {
                    $itemTotal = $line_price;
                }
            }
        }

        echo json_encode(['success' => true, 'subtotal' => $subtotal, 'itemTotal' => $itemTotal]);
        exit;
    }

    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    if (isset($item['qty']) && isset($item['price'])) {
        $discount_percent = $item['qty'];
        if ($discount_percent > 50)
            $discount_percent = 50;

        $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
        $subtotal += ($discounted_price * $item['qty']);
    }
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
    <main>
        <h2>My Cart</h2>
        <div class="cart-container">
            <section class="cart-items-section">
                <?php if (empty($_SESSION['cart'])): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $id; ?>" data-price="<?php echo $item['price']; ?>">
                            <img src="<?php echo $item['image']; ?>" alt="">
                            <div class="cart-item-details">
                                <h4><?php echo $item['name']; ?></h4>

                                <?php
                                $d_percent = $item['qty'];
                                if ($d_percent > 50)
                                    $d_percent = 50;
                                $d_price = $item['price'] * (1 - ($d_percent / 100));
                                $line_total = $d_price * $item['qty'];
                                ?>

                                <p>
                                    <span
                                        style="text-decoration: line-through; color: #999; font-size: 0.9em;">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <span
                                        style="color: black; padding-left: 15px;">₹<?php echo number_format($d_price, 2); ?></span>
                                </p>
                                <!-- <span style="background: #fee2e2; color: #ef4444; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                    <?php echo $d_percent; ?>% OFF
                                </span> -->
                            </div>
                            <div class="quantity-controls">
                                <button class="qty-btn qty-decrease" onclick="updateQuantity('<?php echo $id; ?>', -1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" class="qty-input" value="<?php echo $item['qty']; ?>"
                                    data-product-id="<?php echo $id; ?>" readonly>
                                <button class="qty-btn qty-increase" onclick="updateQuantity('<?php echo $id; ?>', 1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-total" data-product-id="<?php echo $id; ?>">
                                ₹<?php echo number_format($line_total, 2); ?>
                            </div>
                            <button class="remove-btn" onclick="removeItem('<?php echo $id; ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <!-- Hidden form for server-side updates -->
                            <form action="cart.php" method="POST" class="hidden-update-form" style="display:none;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="qty" class="hidden-qty" value="<?php echo $item['qty']; ?>">
                            </form>
                            <form action="cart.php" method="POST" class="hidden-remove-form" style="display:none;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section class="cart-summary-section">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total</span><span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php
                if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true') {
                    $checkout_link = "checkout.php";
                } else {
                    $checkout_link = "login.php?redirect=checkout.php";
                }
                ?>
                <a href="<?php echo $checkout_link; ?>"><button class="checkout-btn">Proceed to Checkout</button></a>
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

    <script src="js/cart.js"></script>
</body>

</html>