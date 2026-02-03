<h3><i class="fa-solid fa-list-check"></i> Review Items</h3>
<div class="review-items-table">
    <div class="table-header">
        <div class="header-item">Image</div>
        <div class="header-item">Product Name</div>
        <div class="header-item">Quantity</div>
        <div class="header-item">Unit Price</div>
        <div class="header-item">Subtotal</div>
    </div>
    <?php if (empty($cart)): ?>
        <div class="table-row empty-cart">
            <div class="row-item" colspan="5" style="text-align:center;">Your cart is empty</div>
        </div>
    <?php else: ?>
        <?php foreach ($cart as $id => $item): ?>
            <div class="table-row">
                <div class="row-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>"
                        class="product-image">
                </div>
                <div class="row-item product-name"><?php echo $item['name']; ?></div>
                <div class="row-item quantity"><?php echo $item['qty']; ?></div>
                <?php
                $d_percent = min($item['qty'], 50);
                $d_price = $item['price'] * (1 - ($d_percent / 100));
                $line_total = $d_price * $item['qty'];
                ?>
                <div class="row-item unit-price">
                    <span
                        style="text-decoration: line-through; font-size: 0.8em; color: #999;">₹<?php echo number_format($item['price'], 2); ?></span><br>
                    ₹<?php echo number_format($d_price, 2); ?>
                </div>
                <div class="row-item subtotal">
                    ₹<?php echo number_format($line_total, 2); ?>
                    <div style="font-size: 0.7em; color: #999;"><?php echo $d_percent; ?>% Off</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>