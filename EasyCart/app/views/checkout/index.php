<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Checkout</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <main>
        <h2><i class="fa-solid fa-credit-card"></i> Checkout</h2>

        <div class="cart-container">
            <!-- Left Side: Forms and Options -->
            <section class="cart-items-section">
                <?php include 'app/views/checkout/personal-details.php'; ?>

                <hr>

                <?php include 'app/views/checkout/shipping-options.php'; ?>

                <hr>

                <?php include 'app/views/checkout/payment-methods.php'; ?>

                <hr>

                <?php include 'app/views/checkout/review-items.php'; ?>
            </section>

            <!-- Right Side: Summary -->
            <?php include 'app/views/checkout/payment-summary.php'; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../common/footer.php'; ?>

    <script src="js/checkout.js?v=<?php echo time(); ?>"></script>
</body>

</html>