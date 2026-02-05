<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h3 style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;"><i
            class="fa-solid fa-truck"></i> Shipping Address</h3>
    <div id="auto-save-indicator"
        style="display: none; align-items: center; gap: 6px; color: #10b981; font-size: 0.85rem; font-weight: 500;">
        <i class="fa-solid fa-check-circle"></i>
        <span>Auto-saved</span>
    </div>
</div>
<div style="height: 2px; background: var(--bg-light); margin-bottom: 24px;"></div>

<!-- Shipping (Primary) Fields -->
<div class="checkout-details-grid">
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required placeholder="Enter full name"
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
        <input type="email" id="email" name="email" required placeholder="Enter email address"
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

<!-- Copy Toggle -->
<div style="margin-top: 30px; margin-bottom: 20px; display: flex; align-items: center;">
    <label class="custom-checkbox" style="display: inline-flex; align-items: center; gap: 12px; cursor: pointer; padding: 10px 15px; background: var(--bg-light); border-radius: 8px; border: 1px solid #e5e7eb; transition: all 0.2s ease;">
        <input type="checkbox" id="same_as_shipping" name="same_as_shipping" value="1" form="checkoutForm" onchange="toggleBillingAddress(this)" style="width: 18px; height: 18px; accent-color: var(--primary);">
        <span style="font-weight: 500; color: var(--text-main); font-size: 0.95rem;">Billing address same as Shipping address</span>
    </label>
</div>

<!-- Billing (Secondary) Address Section -->
<div id="billing-address-section" style="display: block; margin-top: 20px;">
    <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 20px;">
        <h3 style="margin-bottom: 0; font-size: 1.1rem; border-bottom: none;"><i class="fa-solid fa-file-invoice"></i> Billing Address</h3>
    </div>

    <div class="checkout-details-grid">
        <!-- Name and Mobile removed per user request, will use primary contact details -->
        <div class="form-group full-width">
            <label for="billing_street">Street Address</label>
            <input type="text" id="billing_street" name="billing_street" placeholder="House No, Street Name" required form="checkoutForm">
        </div>
        <div class="form-group">
            <label for="billing_city">City</label>
            <input type="text" id="billing_city" name="billing_city" placeholder="Enter City" required form="checkoutForm">
        </div>
        <div class="form-group">
            <label for="billing_state">State</label>
            <input type="text" id="billing_state" name="billing_state" placeholder="Enter State" required form="checkoutForm">
        </div>
        <div class="form-group">
            <label for="billing_postcode">Zip/Postcode</label>
            <input type="text" id="billing_postcode" name="billing_postcode" placeholder="Enter Pincode" required form="checkoutForm">
        </div>
        <div class="form-group">
            <label for="billing_country">Country</label>
            <input type="text" id="billing_country" name="billing_country" placeholder="Enter Country" required form="checkoutForm">
        </div>
    </div>
</div>

<script>
    function toggleBillingAddress(checkbox) {

        const fields = [{
                shipping: 'street',
                billing: 'billing_street'
            },
            {
                shipping: 'city',
                billing: 'billing_city'
            },
            {
                shipping: 'state',
                billing: 'billing_state'
            },
            {
                shipping: 'postcode',
                billing: 'billing_postcode'
            },
            {
                shipping: 'country',
                billing: 'billing_country'
            }
        ];

        if (checkbox.checked) {
            // Copy values and Make Readonly
            fields.forEach(pair => {
                const shippingInput = document.getElementById(pair.shipping);
                const billingInput = document.getElementById(pair.billing);
                if (shippingInput && billingInput) {
                    billingInput.value = shippingInput.value;
                    billingInput.readOnly = true;
                    billingInput.dispatchEvent(new Event('input'));
                }
            });
        } else {
            // Make Editable and Clear Values
            fields.forEach(pair => {
                const billingInput = document.getElementById(pair.billing);
                if (billingInput) {
                    billingInput.readOnly = false;
                    billingInput.value = ''; // Clear value
                    billingInput.dispatchEvent(new Event('input'));
                }
            });
        }
    }

    // Add listeners to Primary (Shipping) fields to auto-update Billing if checked
    document.addEventListener('DOMContentLoaded', function() {
        const fields = [{
                shipping: 'street',
                billing: 'billing_street'
            },
            {
                shipping: 'city',
                billing: 'billing_city'
            },
            {
                shipping: 'state',
                billing: 'billing_state'
            },
            {
                shipping: 'postcode',
                billing: 'billing_postcode'
            },
            {
                shipping: 'country',
                billing: 'billing_country'
            }
        ];

        fields.forEach(pair => {
            const shippingInput = document.getElementById(pair.shipping);
            if (shippingInput) {
                shippingInput.addEventListener('input', function() {
                    const checkbox = document.getElementById('same_as_shipping');
                    if (checkbox && checkbox.checked) {
                        const billingInput = document.getElementById(pair.billing);
                        if (billingInput) {
                            billingInput.value = this.value;
                            billingInput.dispatchEvent(new Event('input'));
                        }
                    }
                });
            }
        });
    });
</script>