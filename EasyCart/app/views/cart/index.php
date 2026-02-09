<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - My Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <main>
        <h2>My Cart</h2>
        <div class="cart-container">
            <section class="cart-items-section">
                <?php if (empty($cartData)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <?php foreach ($cartData as $id => $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $id; ?>" data-price="<?php echo $item['price']; ?>">
                            <img src="<?php echo $item['image']; ?>" alt="">
                            <div class="cart-item-details">
                                <h4><?php echo $item['name']; ?></h4>

                                <?php
                                $d_percent = min($item['qty'], 50);
                                $d_price = $item['price'] * (1 - ($d_percent / 100));
                                $line_total = $d_price * $item['qty'];
                                ?>

                                <p>
                                    <span style="text-decoration: line-through; color: #999; font-size: 0.9em;">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <span style="color: black; padding-left: 15px;">₹<?php echo number_format($d_price, 2); ?></span>
                                </p>
                            </div>
                            <div class="quantity-controls">
                                <button class="qty-btn qty-decrease" onclick="updateQuantity('<?php echo $id; ?>', -1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" class="qty-input" value="<?php echo $item['qty']; ?>"
                                    data-product-id="<?php echo $id; ?>" readonly>
                                <button class="qty-btn qty-increase" onclick="updateQuantity('<?php echo $id; ?>', 1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-total" data-product-id="<?php echo $id; ?>">
                                ₹<?php echo number_format($line_total, 2); ?>
                            </div>
                            <button class="remove-btn" onclick="removeItem('<?php echo $id; ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <!-- Hidden form for server-side updates -->
                            <form action="cart" method="POST" class="hidden-update-form" style="display:none;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="qty" class="hidden-qty" value="<?php echo $item['qty']; ?>">
                            </form>
                            <form action="cart" method="POST" class="hidden-remove-form" style="display:none;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section class="cart-summary-section">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total</span><span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <a href="<?php echo $checkoutLink; ?>"><button class="checkout-btn">Proceed to Checkout</button></a>
            </section>
        </div>
    </main>
    <?php include __DIR__ . '/../common/footer.php'; ?>

    <script src="js/cart.js"></script>
</body>

</html>