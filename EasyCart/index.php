<?php
require_once 'data.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Home</title>
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
        <section class="hero-section">
            <div class="hero-content">
                <h2><i class="fa-solid fa-cart-shopping"></i> Shop Smart with EasyCart</h2>
                <p>Discover quality products at unbeatable prices – shop with confidence!</p>
                <a href="plp.php"><button class="hero-btn">Browse Products</button></a>
            </div>
        </section>

        <section class="products-section">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach (array_slice($products, 0, 4) as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>₹<?php echo number_format($product['price']); ?></p>
                        <a href="pdp.php?id=<?php echo $product['id']; ?>"><button class="product-btn">View
                                Details</button></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="two-column-section">
            <section class="categories-section">
                <h2>Popular Categories</h2>
                <ul class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="plp.php?cat=<?php echo $cat['id']; ?>"><i class="<?php echo $cat['icon']; ?>"></i>
                                <?php echo $cat['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <section class="brands-section">
                <h2>Popular Brands</h2>
                <ul class="brand-list">
                    <?php foreach ($brands as $brand): ?>
                        <li><a href="plp.php?brand=<?php echo $brand['id']; ?>"><i
                                    class="<?php echo $brand['icon']; ?>"></i> <?php echo $brand['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
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