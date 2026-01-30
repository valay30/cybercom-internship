<?php
require_once 'data.php';
session_start();
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

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

// Pagination
$items_per_page = 9;
$total_items = count($filtered_products);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1)
    $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0)
    $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($filtered_products, $offset, $items_per_page);

// ==========================================
// AJAX RESPONSE (Return JSON if AJAX request)
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    $response = [
        'success' => true,
        'products' => [],
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'showing_from' => $offset + 1,
        'showing_to' => min($offset + count($paginated_products), $total_items)
    ];

    // Format products for JSON response
    foreach ($paginated_products as $product) {
        $response['products'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'shipping_type' => $product['shipping_type'],
            'in_wishlist' => in_array($product['id'], $_SESSION['wishlist'])
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Stop here, don't render HTML
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
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="plp-container">
            <!-- Sidebar Filters -->
            <aside class="filters-sidebar">
                <h3><i class="fa-solid fa-filter"></i> Filters</h3>
                <form id="filterForm" action="plp.php" method="GET">

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
                    <span class="product-count">Showing
                        <strong><?php echo $offset + 1; ?>-<?php echo min($offset + count($paginated_products), $total_items); ?></strong>
                        of <strong><?php echo $total_items; ?></strong> items</span>

                    <!-- Sort Dropdown -->
                    <form id="sortForm" action="plp.php" method="GET"
                        style="box-shadow:none; padding:0; border:none; margin:0; background:none; max-width: 250px;">
                        <!-- Preserve other filters -->
                        <?php
                        foreach ($_GET as $key => $val) {
                            if ($key == 'sort' || $key == 'page') // Exclude page from sort reset, usually go to page 1? Yes.
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
                        <select name="sort">
                            <option value="">Sort By: Relevance</option>
                            <option value="price_asc" <?php echo $sort_option === 'price_asc' ? 'selected' : ''; ?>>Price:
                                Low to High</option>
                            <option value="price_desc" <?php echo $sort_option === 'price_desc' ? 'selected' : ''; ?>>
                                Price: High to Low</option>
                        </select>
                    </form>
                </div>

                <div class="product-grid">
                    <?php if (empty($paginated_products)): ?>
                        <div style="grid-column:1/-1; text-align:center; padding:50px;">
                            <h3>No products found</h3>
                            <p>Try adjusting your search or filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($paginated_products as $product): ?>
                            <div class="product-card">
                                <img src="<?php echo $product['image']; ?>" alt="">
                                <h3><?php echo $product['name']; ?></h3>
                                <p>₹<?php echo number_format($product['price']); ?></p>
                                <!-- Shipping Type Badge -->
                                <?php if ($product['shipping_type'] === 'express'): ?>
                                    <span
                                        style="display: inline-block; padding: 4px 8px; background: #28a745; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;">
                                        Express
                                    </span>
                                <?php else: ?>
                                    <span
                                        style="display: inline-block; padding: 4px 8px; background: #6c757d; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;">
                                        Freight
                                    </span>
                                <?php endif; ?>
                                <div class="product-actions"
                                    style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                                    <a href="pdp.php?id=<?php echo $product['id']; ?>"><button class="product-btn">View
                                            Details</button></a>
                                    <?php
                                    $in_wishlist = isset($_SESSION['wishlist']) && in_array($product['id'], $_SESSION['wishlist']);
                                    ?>
                                    <button class="wishlist-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                                        style="background: white; border: 1px solid #ddd; padding: 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                                        <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"
                                            style="color: <?php echo $in_wishlist ? '#ef4444' : '#64748b'; ?>; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="display:flex; justify-content:center; gap:10px; margin-top:40px;">
                        <?php
                        $queryParams = $_GET;
                        // Unset page to append cleanly or just overwrite
                        ?>

                        <?php if ($current_page > 1): ?>
                            <?php $queryParams['page'] = $current_page - 1; ?>
                            <a href="?<?php echo http_build_query($queryParams); ?>" class="pagination-btn"><i
                                    class="fa-solid fa-angle-left"></i> </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php $queryParams['page'] = $i; ?>
                            <a href="?<?php echo http_build_query($queryParams); ?>"
                                class="pagination-btn <?php echo $i === $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <?php $queryParams['page'] = $current_page + 1; ?>
                            <a href="?<?php echo http_build_query($queryParams); ?>" class="pagination-btn"><i
                                    class="fa-solid fa-angle-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <script>
        const WISHLIST_IDS = <?php echo json_encode(isset($_SESSION['wishlist']) ? array_values($_SESSION['wishlist']) : []); ?>;
    </script>
    <script src="js/plp.js"></script>
    <script src="js/wishlist.js"></script>
</body>

</html>