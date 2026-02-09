<!-- Header Section -->
<header>
    <h1>EasyCart</h1>
    <nav>
        <a href="index">Home</a>
        <a href="plp">Products</a>
        <a href="wishlist">Wishlist</a>
        <a href="cart">Cart</a>
        <a href="orders">My Orders</a>
        <a href="dashboard">Dashboard</a>
    </nav>
    <?php
    $headerUserName = 'My Account';
    $headerIsAdmin = false;
    $userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;

    if ($userId) {
        if (file_exists(__DIR__ . '/../../models/CustomerModel.php')) {
            require_once __DIR__ . '/../../models/CustomerModel.php';
            if (class_exists('CustomerModel')) {
                $headerCustomerModel = new CustomerModel();
                $headerUser = $headerCustomerModel->getCustomerById($userId);
                if ($headerUser) {
                    $headerUserName = htmlspecialchars($headerUser['full_name']);
                }
                // Check if user is admin
                $headerIsAdmin = $headerCustomerModel->isAdmin($userId);
            }
        }
    }
    ?>

    <?php if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true'): ?>
        <div class="user-info">
            <span><i class="fa-solid fa-user"></i>
                <?php echo $headerUserName; ?>
            </span>
            <?php if ($headerIsAdmin): ?>
                <a href="admin" class="admin-btn" title="Admin Panel"><i class="fa-solid fa-gear"></i></a>
            <?php endif; ?>
            <a href="logout" class="logout-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    <?php else: ?>
        <a href="login" class="user-icon"><i class="fa-solid fa-user"></i></a>
    <?php endif; ?>
</header>