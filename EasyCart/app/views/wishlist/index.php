<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - My Wishlist</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/wishlist.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <main>
        <h2><i class="fa-solid fa-heart" style="color: #ef4444;"></i> My Wishlist</h2>

        <div class="wishlist-grid">
            <?php if ($isEmpty): ?>
                <div class="empty-wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love for later!</p>
                    <a href="plp"><button class="hero-btn"
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
                            <button class="product-btn" onclick="addToCart('<?php echo $product['id']; ?>', this)"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
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

    <?php include __DIR__ . '/../common/footer.php'; ?>

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
                        showToast('Removed from Wishlist', 'success');
                    }
                });
        }

        function addToCart(pid, btn) {
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('id', pid);
            formData.append('ajax', 'true');

            fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Added to Cart Successfully', 'success');
                    } else {
                        showToast('Failed to add to cart', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Error adding to cart', 'error');
                })
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
        }

        function showToast(message, type = 'success') {
            // Remove existing toasts
            document.querySelectorAll('.ec-toast').forEach(t => t.remove());

            const toast = document.createElement('div');
            toast.className = `ec-toast ec-toast-${type}`;

            const iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';

            toast.innerHTML = `
                <i class="fa-solid ${iconClass}"></i>
                <span>${message}</span>
            `;

            document.body.appendChild(toast);

            // Animate
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            // Remove
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>

</html>