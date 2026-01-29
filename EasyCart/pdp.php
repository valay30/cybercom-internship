<?php
require_once 'data.php';
session_start();

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$id = $_GET['id'] ?? 'p1';
$product = $products[$id] ?? $products['p1'];

// Check if product is in cart
$is_in_cart = false;
$discount_applied = false;
$final_price = $product['price'];

if (isset($_SESSION['cart'][$product['id']])) {
    $is_in_cart = true;
    $qty = $_SESSION['cart'][$product['id']]['qty'];

    $discount_percent = $qty;

    $final_price = $product['price'] * (1 - ($discount_percent / 100));
    $discount_applied = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $product['name']; ?> - EasyCart</title>
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
        <div class="product-details-container">
            <section class="pdp-image-section">
                <div class="pdp-image-container">
                    <img id="mainImage" src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <?php if (isset($product['images']) && count($product['images']) > 1): ?>
                        <div class="image-thumbnails">
                            <?php foreach ($product['images'] as $index => $img): ?>
                                <img src="<?php echo $img; ?>" alt="View <?php echo $index + 1; ?>"
                                    class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                    onclick="switchImage('<?php echo $img; ?>', this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            <section class="pdp-info-section">
                <h3><?php echo $product['name']; ?></h3>
                <?php if ($discount_applied): ?>
                    <p class="product-price">
                        <span
                            style="text-decoration: line-through; color:var(--primary); font-size: 0.7em;">₹<?php echo number_format($product['price'], 2); ?></span>
                        <span style="color: black;">₹<?php echo number_format($final_price, 2); ?></span>
                        <span
                            style="background: #fee2e2; color: black; padding: 4px 10px; border-radius: 20px; font-size: 0.5em; vertical-align: middle; margin-left: 10px;">
                            <?php echo $discount_percent; ?>% OFF
                        </span>
                    </p>
                <?php else: ?>
                    <p class="product-price">₹<?php echo number_format($product['price'], 2); ?></p>
                <?php endif; ?>
                <p><?php echo $product['description']; ?></p>
                <h4 style="margin-top:20px">Features:</h4>
                <ul class="feature-list">
                    <?php foreach ($product['features'] as $feature): ?>
                        <li><?php echo $feature; ?></li>
                    <?php endforeach; ?>
                </ul>
                <form action="cart.php" method="POST"
                    style="box-shadow:none; padding:0; border:none; max-width:100%; display:inline-block;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="add-to-cart-btn"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
                </form>
                <?php
                $in_wishlist = isset($_SESSION['wishlist']) && in_array($product['id'], $_SESSION['wishlist']);
                ?>
                <button class="wishlist-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                    style="background: white; border: 1px solid #ddd; padding: 12px 16px; border-radius: 6px; cursor: pointer; transition: all 0.2s; margin-left:10px;">
                    <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"
                        style="color: <?php echo $in_wishlist ? '#ef4444' : '#64748b'; ?>; font-size: 1.4rem; vertical-align: middle;"></i>
                </button>
            </section>
        </div>
        <br>
        <br>
        <div class="product-grid">
            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>₹<?php echo number_format($product['price'], 2); ?></p>
                    <a href="pdp.php?id=<?php echo $product['id']; ?>"><button class="product-btn">View Details</button></a>
                </div>
            <?php endforeach; ?>
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

    <script src="js/pdp.js?v=<?php echo time(); ?>"></script>
    <script src="js/wishlist.js"></script>
</body>

</html>