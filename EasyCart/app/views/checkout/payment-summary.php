<section class="cart-summary-section">
    <h3><i class="fa-solid fa-receipt"></i> Payment Summary</h3>

    <!-- Coupon Code Section -->
    <div class="coupon-section"
        style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <label for="coupon-input"
            style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;"> Have a Coupon Code?
        </label>
        <div style="display: flex; gap: 10px;">
            <input type="text" id="coupon-input" placeholder="Enter coupon code"
                style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px; text-transform: uppercase;">
            <button id="apply-coupon-btn" onclick="applyCoupon()"
                style="padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                Apply
            </button>
        </div>
        <div id="coupon-message" style="margin-top: 10px; font-size: 0.9em; display: none;"></div>
        <div id="coupon-success" class="coupon-success-card"></div>
    </div>

    <div class="summary-row">
        <span>Subtotal</span>
        <span id="summary-subtotal"
            data-subtotal="<?php echo $subtotal; ?>">₹<?php echo number_format($subtotal, 2); ?></span>
    </div>
    <div class="summary-row">
        <span>Shipping Charges</span>
        <span id="summary-shipping">₹<?php echo number_format($shippingCost, 2); ?></span>
    </div>

    <!-- Coupon Discount Row (Hidden by default) -->
    <div class="summary-row" id="coupon-discount-row" style="display: none; color: var(--text-muted);">
        <span>Coupon Discount (<span id="coupon-percent"></span>%)</span>
        <span id="coupon-discount-amount">-₹0.00</span>
    </div>

    <div class="summary-row">
        <span>GST (18%)</span>
        <span id="summary-tax">₹<?php echo number_format($taxAmount, 2); ?></span>
    </div>

    <hr>

    <div class="summary-row total">
        <span>Total Amount</span>
        <span id="summary-total">₹<?php echo number_format($totalAmount, 2); ?></span>
    </div>

    <form id="checkoutForm" action="orders.php" method="POST"
        style="border:none; box-shadow:none; padding:0; margin-top:20px; margin-bottom:20px;">
        <button type="submit" class="checkout-btn">
            <i class="fa-solid fa-bag-shopping"></i> Place Order
        </button>
    </form>

    <a href="cart.php" class="continue-shopping">
        <i class="fa-solid fa-arrow-left"></i> Edit Cart
    </a>
</section>