<?php
require_once 'data.php';
session_start();

// Get filter parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle category parameter - can be string or array
$selected_categories = isset($_GET['category']) ? $_GET['category'] : [];
if (!is_array($selected_categories)) {
    $selected_categories = [$selected_categories]; // Convert single value to array
}

// Handle brand parameter - can be string or array
$selected_brands = isset($_GET['brand']) ? $_GET['brand'] : [];
if (!is_array($selected_brands)) {
    $selected_brands = [$selected_brands]; // Convert single value to array
}
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int) $_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int) $_GET['max_price'] : 15000;
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : '';

// Filtering
$filtered_products = array_filter($products, function ($product) use ($search_query, $selected_categories, $selected_brands, $min_price, $max_price) {
    // Search
    if ($search_query && stripos($product['name'], $search_query) === false && stripos($product['description'], $search_query) === false) {
        return false;
    }
    // Category
    if (!empty($selected_categories) && !in_array($product['category'], $selected_categories)) {
        return false;
    }
    // Brand
    if (!empty($selected_brands) && !in_array($product['brand'], $selected_brands)) {
        return false;
    }
    // Price
    if ($product['price'] < $min_price || $product['price'] > $max_price) {
        return false;
    }
    return true;
});

// Sorting
if ($sort_option === 'price_asc') {
    usort($filtered_products, function ($a, $b) {
        return $a['price'] - $b['price'];
    });
} elseif ($sort_option === 'price_desc') {
    usort($filtered_products, function ($a, $b) {
        return $b['price'] - $a['price'];
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - Products</title>
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
        <div class="plp-container">
            <!-- Sidebar Filters -->
            <aside class="filters-sidebar">
                <h3><i class="fa-solid fa-filter"></i> Filters</h3>
                <form action="plp.php" method="GET">

                    <!-- Categories -->
                    <div class="filter-group">
                        <h4>Categories</h4>
                        <div class="checkbox-list">
                            <?php foreach ($categories as $cat): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="category[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $selected_categories) ? 'checked' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Brands -->
                    <div class="filter-group">
                        <h4>Brands</h4>
                        <div class="checkbox-list">
                            <?php foreach ($brands as $brand): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="brand[]" value="<?php echo $brand['id']; ?>" <?php echo in_array($brand['id'], $selected_brands) ? 'checked' : ''; ?>>
                                    <?php echo $brand['name']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <h4>Price Range (₹)</h4>
                        <div class="price-slider-container">
                            <div class="price-values">
                                <span>₹<span id="min-price-disp"><?php echo $min_price ?: '0'; ?></span></span>
                                    <span>–</span>
                                    <span>₹<span
                                            id="max-price-disp"><?php echo $max_price == 15000 ? '15000+' : ($max_price ?: '15000'); ?></span></span>
                            </div>

                            <div class="range-slider">
                                <div class="slider-track"></div>
                                <input type="range" class="min-range" min="0" max="15000"
                                    value="<?php echo $min_price ?: '0'; ?>" step="100" id="min-range-input">
                                <input type="range" class="max-range" min="0" max="15000"
                                    value="<?php echo $max_price ?: '15000'; ?>" step="100" id="max-range-input">
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" name="min_price" id="hidden_min_price"
                                value="<?php echo $min_price ?: 0; ?>">
                            <input type="hidden" name="max_price" id="hidden_max_price"
                                value="<?php echo $max_price == 15000 ? 15000 : $max_price; ?>">
                        </div>
                    </div>

                    <button type="submit" class="apply-filters-btn">Apply Filters</button>
                    <?php if (!empty($_GET)): ?>
                        <a href="plp.php" style="display:block; text-align:center; margin-top:10px; font-size:0.9rem;">Clear
                            All</a>
                    <?php endif; ?>
                </form>
            </aside>

            <!-- Product Grid -->
            <section class="products-display products-section">
                <div class="sort-bar">
                    <!-- Quick Search -->
                    <form action="plp.php" method="GET" class="quick-search-form">
                        <!-- Preserve existing filters -->
                        <?php
                        foreach ($_GET as $key => $val) {
                            if ($key == 'search')
                                continue;
                            if (is_array($val)) {
                                foreach ($val as $v) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                                }
                            } else {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
                            }
                        }
                        ?>
                        <div class="search-input-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" placeholder="Search products..."
                                value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php if ($search_query): ?>
                                <a href="plp.php" class="clear-search" title="Clear search">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Product Count -->
                    <span class="product-count">Showing <strong><?php echo count($filtered_products); ?></strong>
                        products</span>

                    <!-- Sort Dropdown -->
                    <form id="sortForm" action="plp.php" method="GET"
                        style="box-shadow:none; padding:0; border:none; margin:0; background:none; max-width: 250px;">
                        <!-- Preserve other filters -->
                        <?php
                        foreach ($_GET as $key => $val) {
                            if ($key == 'sort')
                                continue;
                            if (is_array($val)) {
                                foreach ($val as $v) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                                }
                            } else {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
                            }
                        }
                        ?>
                        <select name="sort" onchange="document.getElementById('sortForm').submit()">
                            <option value="">Sort By: Relevance</option>
                            <option value="price_asc" <?php echo $sort_option === 'price_asc' ? 'selected' : ''; ?>>Price:
                                Low
                                to High</option>
                            <option value="price_desc" <?php echo $sort_option === 'price_desc' ? 'selected' : ''; ?>>
                                Price:
                                High to Low</option>
                        </select>
                    </form>
                </div>

                <div class="product-grid">
                    <?php if (empty($filtered_products)): ?>
                        <div style="grid-column:1/-1; text-align:center; padding:50px;">
                            <h3>No products found</h3>
                            <p>Try adjusting your search or filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filtered_products as $product): ?>
                            <div class="product-card">
                                <img src="<?php echo $product['image']; ?>" alt="">
                                <h3><?php echo $product['name']; ?></h3>
                                <p>₹<?php echo number_format($product['price']); ?></p>
                                <a href="pdp.php?id=<?php echo $product['id']; ?>"><button class="product-btn">View
                                        Details</button></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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

    <script src="js/plp.js"></script>
</body>

</html>