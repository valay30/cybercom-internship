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
        <h2><i class="fa-solid fa-box"></i> My Orders</h2>

        <?php if (!$hasOrders): ?>
            <div style="text-align: center; padding: 50px;">
                <i class="fa-solid fa-box-open" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="plp.php"><button class="hero-btn" style="margin-top: 20px; background: var(--primary); color: white;">Browse Products</button></a>
            </div>
        <?php else: ?>
            <div class="orders-container">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">

                        <!-- Main Summary Row (Always Visible) -->
                        <div class="order-summary-header">
                            <div class="summary-col">
                                <span class="label">Order ID</span>
                                <span class="value order-id">#<?php echo $order['id']; ?></span>
                            </div>
                            <div class="summary-col">
                                <span class="label">Date</span>
                                <span class="value"><?php echo $order['date']; ?></span>
                            </div>
                            <div class="summary-col">
                                <span class="label">Shipping</span>
                                <span class="value badge-<?php echo $order['shipping_type']; ?>">
                                    <?php echo ucfirst($order['shipping_type']); ?>
                                </span>
                            </div>
                            <div class="summary-col">
                                <span class="label">Amount</span>
                                <span class="value amount">₹<?php echo number_format($order['grand_total']); ?></span>
                            </div>
                            <div class="summary-col action">
                                <span class="status-badge <?php echo strtolower($order['order_status']); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Collapsible Detail View -->
                        <details class="order-details-toggle">
                            <summary class="details-trigger">
                                <span>Order Detail View</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </summary>

                            <div class="details-content">
                                <div class="order-items-section">
                                    <h4>Products Included</h4>
                                    <div class="order-items">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="order-item">
                                                <img src="<?php echo $item['image']; ?>" alt="">
                                                <div class="item-details">
                                                    <span class="item-name"><?php echo $item['product_name'] ?? $item['name']; ?></span>
                                                    <span class="item-qty">Qty: <?php echo $item['quantity'] ?? $item['qty']; ?></span>
                                                </div>
                                                <span class="item-price">₹<?php echo number_format($item['price']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="order-billing-section">
                                    <h4>Price Breakup</h4>
                                    <div class="order-summary-details">
                                        <div class="summary-row">
                                            <span>Subtotal</span>
                                            <span>₹<?php echo number_format($order['subtotal']); ?></span>
                                        </div>
                                        <div class="summary-row">
                                            <span>Shipping Cost</span>
                                            <span>₹<?php echo number_format($order['shipping_cost']); ?></span>
                                        </div>
                                        <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                                            <div class="summary-row discount">
                                                <span>Discount</span>
                                                <span>-₹<?php echo number_format($order['discount_amount']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="summary-row">
                                            <span>Tax (18%)</span>
                                            <span>₹<?php echo number_format($order['tax_amount']); ?></span>
                                        </div>
                                        <div class="summary-row total-row">
                                            <span>Total Amount</span>
                                            <span class="total-amount">₹<?php echo number_format($order['grand_total']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                    </div>
                <?php endforeach; ?>
            </div>


        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>