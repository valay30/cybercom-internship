<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <main>
        <section class="hero-section">
            <div class="hero-content">
                <h2><i class="fa-solid fa-cart-shopping"></i> Shop Smart with EasyCart</h2>
                <p>Discover quality products at unbeatable prices , shop with confidence!</p>
                <a href="plp"><button class="hero-btn">Browse Products</button></a>
            </div>
        </section>

        <section class="products-section">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>₹<?php echo number_format($product['price']); ?></p>
                        <div style="display:flex; justify-content:center; gap:10px; margin-top:10px;">
                            <a href="pdp?url=<?php echo isset($product['url_key']) && $product['url_key'] ? $product['url_key'] : $product['id']; ?>"><button class="product-btn">View Details</button></a>
                            <?php
                            $in_wishlist = in_array($product['id'], $wishlistIds);
                            ?>
                            <button class="wishlist-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                                style="background: white; border: 1px solid #ddd; padding: 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                                <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"
                                    style="color: <?php echo $in_wishlist ? '#ef4444' : '#64748b'; ?>; font-size: 1.2rem;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="two-column-section">
            <section class="categories-section">
                <h2>Popular Categories</h2>
                <ul class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="plp?category[]=<?php echo $cat['id']; ?>"><i
                                    class="<?php echo $cat['icon']; ?>"></i>
                                <?php echo $cat['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <section class="brands-section">
                <h2>Popular Brands</h2>
                <ul class="brand-list">
                    <?php foreach ($brands as $brand): ?>
                        <li><a href="plp?brand[]=<?php echo $brand['id']; ?>"><i
                                    class="<?php echo $brand['icon']; ?>"></i> <?php echo $brand['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../common/footer.php'; ?>

    <script src="js/wishlist.js"></script>

</body>

</html>