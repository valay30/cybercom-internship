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
            form="checkoutForm">
    </div>
    <div class="form-group">
        <label for="mobile">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" required placeholder="Enter 10-digit mobile number"
            form="checkoutForm">
    </div>
    <div class="form-group full-width">
        <label for="email">Email ID</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email address"
            form="checkoutForm">
    </div>
    <div class="form-group full-width">
        <label for="address">Shipping Address</label>
        <textarea id="address" name="address" required placeholder="Enter complete address with pincode"
            rows="3" form="checkoutForm"></textarea>
    </div>
</div>