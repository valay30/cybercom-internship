<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - My Wishlist</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .empty-wishlist {
            text-align: center;
            padding: 50px;
            grid-column: 1 / -1;
        }

        .empty-wishlist i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h2><i class="fa-solid fa-heart" style="color: #ef4444;"></i> My Wishlist</h2>

        <div class="wishlist-grid">
            <?php if ($isEmpty): ?>
                <div class="empty-wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love for later!</p>
                    <a href="plp.php"><button class="hero-btn"
                            style="margin-top:20px; background:var(--primary); color:white;">Start Shopping</button></a>
                </div>
            <?php else: ?>
                <?php foreach ($wishlistProducts as $pid => $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3>
                            <?php echo $product['name']; ?>
                        </h3>
                        <p>₹
                            <?php echo number_format($product['price']); ?>
                        </p>
                        <div style="display:flex; gap:10px; justify-content:center;">
                            <a href="pdp.php?id=<?php echo $product['id']; ?>"><button class="product-btn">View
                                    Details</button></a>
                            <button class="product-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                                style="background: white; color: #ef4444; border: 1px solid #ef4444;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function toggleWishlist(pid, btn) {
            // Since we are on wishlist page, 'toggle' means remove
            if (!confirm('Remove this item from wishlist?')) return;

            const formData = new FormData();
            formData.append('action', 'toggle');
            formData.append('id', pid);
            formData.append('ajax', 'true');

            fetch('wishlist.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.status === 'removed') {
                        // Remove card from grid
                        const card = btn.closest('.product-card');
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            if (data.count === 0) location.reload(); // Show empty state
                        }, 300);
                    }
                });
        }
    </script>
</body>

</html>