<?php
session_start();
require_once 'data.php';

// Logic to "save" order if coming from checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, we'd add the session cart to the database here
    unset($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - My Orders</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h2>My Orders</h2>
        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">#<?php echo $order['id']; ?></span>
                        <span class="order-date"><?php echo $order['date']; ?></span>
                        <span class="order-status delivered"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo $item['image']; ?>" alt="">
                                <span><?php echo $item['name']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-footer">
                        <div class="order-total">Total: <span
                                class="total-amount">₹<?php echo number_format($order['total']); ?></span></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>