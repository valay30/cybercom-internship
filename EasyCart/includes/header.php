<!-- Header Section -->
<header>
    <h1>EasyCart</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="plp.php">Products</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
    </nav>
    <?php
    $headerUserName = 'My Account';
    $userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;

    if ($userId) {
        // Ensure strictly only one inclusion/definition if possible, but require_once handles it.
        // We use a try-catch or checks to avoid path issues if models are already loaded differently?
        // Actually __DIR__ is safe.
        if (file_exists(__DIR__ . '/../app/models/CustomerModel.php')) {
            require_once __DIR__ . '/../app/models/CustomerModel.php';
            // Check if class exists to be safe (it should after require)
            if (class_exists('CustomerModel')) {
                $headerCustomerModel = new CustomerModel();
                $headerUser = $headerCustomerModel->getCustomerById($userId);
                if ($headerUser) {
                    $headerUserName = htmlspecialchars($headerUser['full_name']);
                }
            }
        }
    }
    ?>

    <?php if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true'): ?>
        <div class="user-info">
            <span><i class="fa-solid fa-user"></i>
                <?php echo $headerUserName; ?>
            </span>
            <a href="logout.php" class="logout-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    <?php else: ?>
        <a href="login.php" class="user-icon"><i class="fa-solid fa-user"></i></a>
    <?php endif; ?>
</header>