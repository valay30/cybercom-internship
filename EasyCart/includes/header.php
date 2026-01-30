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