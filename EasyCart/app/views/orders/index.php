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
                        <div class="order-header">
                            <div>
                                <span class="order-id">#<?php echo $order['id']; ?></span>
                                <span class="order-date"><?php echo $order['date']; ?></span>
                            </div>
                            <div class="order-status-badge">
                                <?php
                                $shippingIcon = 'fa-truck'; // Default
                                switch ($order['shipping_type'] ?? '') {
                                    case 'express':
                                        $shippingIcon = 'fa-rocket';
                                        break;
                                    case 'white_glove':
                                        $shippingIcon = 'fa-hands-holding-circle';
                                        break;
                                    case 'freight':
                                        $shippingIcon = 'fa-truck-moving';
                                        break;
                                }
                                ?>
                                <span class="shipping-type-badge"><i class="fa-solid <?php echo $shippingIcon; ?>"></i> <?php echo ucfirst($order['shipping_type']); ?></span>
                                <span class="order-status <?php echo strtolower($order['order_status']); ?>"><?php echo ucfirst($order['order_status']); ?></span>
                            </div>
                        </div>
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

                        <!-- Price Breakup -->
                        <div class="order-summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>₹<?php echo number_format($order['subtotal']); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping (<?php echo ucfirst($order['shipping_type']); ?>)</span>
                                <span>₹<?php echo number_format($order['shipping_cost']); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (18%)</span>
                                <span>₹<?php echo number_format($order['tax_amount']); ?></span>
                            </div>
                            <div class="summary-row total-row">
                                <span>Total</span>
                                <span class="total-amount">₹<?php echo number_format($order['grand_total']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <style>
                .order-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }

                .order-status-badge {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }

                .shipping-type-badge {
                    background: #f3f4f6;
                    color: #4b5563;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 0.85rem;
                    font-weight: 500;
                }

                .order-item {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    margin-bottom: 10px;
                    padding-bottom: 10px;
                    border-bottom: 1px dashed #eee;
                }

                .order-item:last-child {
                    border-bottom: none;
                }

                .order-item img {
                    width: 60px;
                    height: 60px;
                    object-fit: cover;
                    border-radius: 6px;
                    border: 1px solid #eee;
                }

                .item-details {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }

                .item-name {
                    font-weight: 500;
                    color: #333;
                }

                .item-qty {
                    font-size: 0.85rem;
                    color: #666;
                }

                .order-summary-details {
                    background: #f9fafb;
                    padding: 15px;
                    border-radius: 8px;
                    margin-top: 15px;
                }

                .summary-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    font-size: 0.9rem;
                    color: #555;
                }

                .summary-row.total-row {
                    border-top: 1px solid #e5e7eb;
                    padding-top: 8px;
                    margin-bottom: 0;
                    font-weight: 700;
                    color: #111;
                    font-size: 1.1rem;
                }
            </style>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>