<?php
require_once 'data.php';
session_start();

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Handle Actions (Add/Remove) using AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = $_POST['id'] ?? '';

    if ($action === 'toggle' && isset($products[$pid])) {
        if (in_array($pid, $_SESSION['wishlist'])) {
            // Remove
            $key = array_search($pid, $_SESSION['wishlist']);
            unset($_SESSION['wishlist'][$key]);
            $status = 'removed';
        } else {
            // Add
            $_SESSION['wishlist'][] = $pid;
            $status = 'added';
        }

        // Re-index array
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);

        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true, 'status' => $status, 'count' => count($_SESSION['wishlist'])]);
            exit;
        }
    }

    // Fallback for non-AJAX
    header('Location: wishlist.php');
    exit;
}
?>
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
    <header>
        <h1>EasyCart</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="plp.php">Products</a>
            <a href="wishlist.php" class="active">Wishlist</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
        </nav>
        <?php if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true'): ?>
            <div class="user-info">
                <span><i class="fa-solid fa-user"></i>
                    <?php echo htmlspecialchars($_COOKIE['user_name']); ?>
                </span>
                <a href="logout.php" class="logout-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        <?php else: ?>
            <a href="login.php" class="user-icon"><i class="fa-solid fa-user"></i></a>
        <?php endif; ?>
    </header>

    <main>
        <h2><i class="fa-solid fa-heart" style="color: #ef4444;"></i> My Wishlist</h2>

        <div class="wishlist-grid">
            <?php if (empty($_SESSION['wishlist'])): ?>
                <div class="empty-wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love for later!</p>
                    <a href="plp.php"><button class="hero-btn"
                            style="margin-top:20px; background:var(--primary); color:white;">Start Shopping</button></a>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['wishlist'] as $pid): ?>
                    <?php if (isset($products[$pid])):
                        $product = $products[$pid];
                        ?>
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
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3><i class="fa-solid fa-cart-shopping"></i> EasyCart</h3>
                <p>Your one-stop destination for all your shopping needs.</p>
            </div>
            <!-- Standard Footer Columns -->
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 EasyCart. All rights reserved.</p>
        </div>
    </footer>

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