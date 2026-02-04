<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;"><i
            class="fa-solid fa-user-circle"></i> Personal Details</h3>
    <div id="auto-save-indicator"
        style="display: none; align-items: center; gap: 6px; color: #10b981; font-size: 0.85rem; font-weight: 500;">
        <i class="fa-solid fa-check-circle"></i>
        <span>Auto-saved</span>
    </div>
</div>
<div style="height: 2px; background: var(--bg-light); margin-bottom: 24px;"></div>
<div class="checkout-details-grid">
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required placeholder="Enter your full name"
            value="<?php echo htmlspecialchars($savedAddress['full_name'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" required placeholder="Enter 10-digit mobile number"
            value="<?php echo htmlspecialchars($savedAddress['telephone'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group full-width">
        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email address"
            value="<?php echo htmlspecialchars($savedAddress['email'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group full-width">
        <label for="street">Street Address</label>
        <input type="text" id="street" name="street" required placeholder="House No, Street Name"
            value="<?php echo htmlspecialchars($savedAddress['street'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" required placeholder="Enter City"
            value="<?php echo htmlspecialchars($savedAddress['city'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="state">State</label>
        <input type="text" id="state" name="state" required placeholder="Enter State"
            value="<?php echo htmlspecialchars($savedAddress['state'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="postcode">Zip/Postcode</label>
        <input type="text" id="postcode" name="postcode" required placeholder="Enter Pincode"
            value="<?php echo htmlspecialchars($savedAddress['postcode'] ?? ''); ?>"
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="country">Country</label>
        <input type="text" id="country" name="country" required placeholder="Enter Country"
            value="<?php echo htmlspecialchars($savedAddress['country'] ?? ''); ?>"
            form="checkoutForm">
    </div>
</div>