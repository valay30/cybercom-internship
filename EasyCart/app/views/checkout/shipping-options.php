<h3><i class="fa-solid fa-truck"></i> Shipping Options</h3>
<div class="shipping-options">
    <!-- Standard Shipping -->
    <label class="shipping-option <?php echo $enableStandard ? 'active' : ''; ?>" for="standard">
        <input type="radio" id="standard" name="shipping" value="<?php echo $shippingStd; ?>" <?php echo $enableStandard ? 'checked' : ''; ?> form="checkoutForm"
            onchange="updateShipping(this)" <?php echo !$enableStandard ? 'disabled' : ''; ?>>
        <div class="shipping-details">
            <div class="shipping-name">
                <i class="fa-solid fa-truck"></i>
                <strong>Standard Shipping</strong>
            </div>
            <div class="shipping-time">5-7 Business Days</div>
            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹40</div>
        </div>
    </label>

    <!-- Express Shipping -->
    <label class="shipping-option" for="express">
        <input type="radio" id="express" name="shipping" value="<?php echo $shippingExpress; ?>"
            form="checkoutForm" onchange="updateShipping(this)" <?php echo !$enableExpress ? 'disabled' : ''; ?>>
        <div class="shipping-details">
            <div class="shipping-name">
                <i class="fa-solid fa-rocket"></i>
                <strong>Express Shipping</strong>
            </div>
            <div class="shipping-time">1-2 Business Days</div>
            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹80 OR 10% of
                subtotal (whichever is lower)</div>
        </div>
    </label>

    <!-- White Glove Delivery -->
    <label
        class="shipping-option <?php echo ($enableWhiteGlove && !$enableStandard) ? 'active' : ''; ?>"
        for="white-glove">
        <input type="radio" id="white-glove" name="shipping"
            value="<?php echo $shippingWhiteGlove; ?>" form="checkoutForm"
            onchange="updateShipping(this)" <?php echo !$enableWhiteGlove ? 'disabled' : ''; ?> <?php echo ($enableWhiteGlove && !$enableStandard) ? 'checked' : ''; ?>>
        <div class="shipping-details">
            <div class="shipping-name">
                <i class="fa-solid fa-hands-holding-circle"></i>
                <strong>White Glove Delivery</strong>
            </div>
            <div class="shipping-time">Scheduled Appointment</div>
            <div class="shipping-price" style="font-size: 0.8em; color: #666;">Flat ₹150 OR 5% of
                subtotal (whichever is lower)</div>
        </div>
    </label>

    <!-- Freight Shipping -->
    <label class="shipping-option" for="freight">
        <input type="radio" id="freight" name="shipping" value="<?php echo $shippingFreight; ?>"
            form="checkoutForm" onchange="updateShipping(this)" <?php echo !$enableFreight ? 'disabled' : ''; ?>>
        <div class="shipping-details">
            <div class="shipping-name">
                <i class="fa-solid fa-truck-moving"></i>
                <strong>Freight Shipping</strong>
            </div>
            <div class="shipping-time">7-14 Business Days</div>
            <div class="shipping-price" style="font-size: 0.8em; color: #666;">3% of subtotal or Minimum
                ₹200</div>
        </div>
    </label>
</div>