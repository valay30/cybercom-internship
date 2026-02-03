<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart - Checkout</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Disabled shipping option styling */
        .shipping-option:has(input:disabled) {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Coupon Success Card Styling */
        .coupon-success-card {
            margin-top: 15px;
            padding: 12px 15px;
            background: linear-gradient(to right, #ecfdf5, #f0fdf4);
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: space-between;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

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

    <?php include 'includes/footer.php'; ?>

    <script src="js/checkout.js?v=<?php echo time(); ?>"></script>
</body>

</html>