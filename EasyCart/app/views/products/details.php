<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $product['name']; ?> - EasyCart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

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
                <?php if ($discountApplied): ?>
                    <p class="product-price">
                        <span
                            style="text-decoration: line-through; color:var(--primary); font-size: 0.7em;">₹<?php echo number_format($product['price'], 2); ?></span>
                        <span style="color: black;">₹<?php echo number_format($finalPrice, 2); ?></span>
                        <span
                            style="background: #fee2e2; color: black; padding: 4px 10px; border-radius: 20px; font-size: 0.5em; vertical-align: middle; margin-left: 10px;">
                            <?php echo $discountPercent; ?>% OFF
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

                <!-- Shipping Type Badge -->
                <div style="margin: 15px 0;">
                    <?php if ($product['shipping_type'] === 'express'): ?>
                        <span
                            style="display: inline-block; padding: 6px 12px; background: #28a745; color: white; border-radius: 4px; font-size: 0.9em; font-weight: 600;">
                            Express Delivery
                        </span>
                    <?php else: ?>
                        <span
                            style="display: inline-block; padding: 6px 12px; background: #6c757d; color: white; border-radius: 4px; font-size: 0.9em; font-weight: 600;">
                            Freight Delivery
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($isInCart && $quantity > 0): ?>
                    <!-- Quantity Controls (when item is in cart) -->
                    <div class="quantity-controls-pdp"
                        style="display: inline-flex; align-items: center; gap: 15px; background: white; border: 2px solid var(--primary); border-radius: 8px; padding: 8px 16px;">
                        <button onclick="updateQuantity('<?php echo $product['id']; ?>', -1)" class="qty-btn"
                            style="background: none; border: none; color: var(--primary); font-size: 1.5rem; cursor: pointer; padding: 0 8px; transition: all 0.2s;"
                            onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <span id="qty-display-<?php echo $product['id']; ?>"
                            style="font-weight: 600; font-size: 1.2rem; min-width: 30px; text-align: center;">
                            <?php echo $quantity; ?>
                        </span>
                        <button onclick="updateQuantity('<?php echo $product['id']; ?>', 1)" class="qty-btn"
                            style="background: none; border: none; color: var(--primary); font-size: 1.5rem; cursor: pointer; padding: 0 8px; transition: all 0.2s;"
                            onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Add to Cart Button (when item is NOT in cart) -->
                    <form id="add-to-cart-form" action="cart.php" method="POST"
                        style="box-shadow:none; padding:0; border:none; max-width:100%; display:inline-block;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="add-to-cart-btn"><i class="fa-solid fa-cart-plus"></i> Add to
                            Cart</button>
                    </form>
                <?php endif; ?>
                <button class="wishlist-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                    style="background: white; border: 1px solid #ddd; padding: 12px 16px; border-radius: 6px; cursor: pointer; transition: all 0.2s; margin-left:10px;">
                    <i class="<?php echo $inWishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"
                        style="color: <?php echo $inWishlist ? '#ef4444' : '#64748b'; ?>; font-size: 1.4rem; vertical-align: middle;"></i>
                </button>
            </section>
        </div>
        <br>
        <br>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $featuredProduct): ?>
                <div class="product-card">
                    <img src="<?php echo $featuredProduct['image']; ?>" alt="<?php echo $featuredProduct['name']; ?>">
                    <h3><?php echo $featuredProduct['name']; ?></h3>
                    <p>₹<?php echo number_format($featuredProduct['price'], 2); ?></p>
                    <a href="pdp.php?id=<?php echo $featuredProduct['id']; ?>"><button class="product-btn">View Details</button></a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <script src="js/pdp.js?v=<?php echo time(); ?>"></script>
    <script src="js/wishlist.js"></script>
</body>

</html>