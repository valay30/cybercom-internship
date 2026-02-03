<h3><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
<div class="payment-options">
    <label class="payment-option active" for="upi">
        <input type="radio" id="upi" name="payment" value="upi" checked form="checkoutForm"
            onchange="updatePayment(this)">
        <div class="payment-details">
            <div class="payment-name">
                <i class="fa-brands fa-google-pay"></i>
                <strong>UPI</strong>
            </div>
            <div class="payment-desc">Google Pay, PhonePe, Paytm & more</div>
        </div>
    </label>

    <label class="payment-option" for="card">
        <input type="radio" id="card" name="payment" value="card" form="checkoutForm"
            onchange="updatePayment(this)">
        <div class="payment-details">
            <div class="payment-name">
                <i class="fa-solid fa-credit-card"></i>
                <strong>Credit / Debit Card</strong>
            </div>
            <div class="payment-desc">Pay securely with your card</div>
        </div>
    </label>

    <label class="payment-option" for="netbanking">
        <input type="radio" id="netbanking" name="payment" value="netbanking" form="checkoutForm"
            onchange="updatePayment(this)">
        <div class="payment-details">
            <div class="payment-name">
                <i class="fa-solid fa-building-columns"></i>
                <strong>Net Banking</strong>
            </div>
            <div class="payment-desc">Pay via your bank account</div>
        </div>
    </label>
    <label class="payment-option" for="cod">
        <input type="radio" id="cod" name="payment" value="cod" form="checkoutForm"
            onchange="updatePayment(this)">
        <div class="payment-details">
            <div class="payment-name">
                <i class="fa-solid fa-money-bill-wave"></i>
                <strong>Cash on Delivery</strong>
            </div>
            <div class="payment-desc">Pay when you receive</div>
        </div>
    </label>
</div>

<!-- Hidden Payment Fields -->
<div class="payment-input-container">
    <div id="upi-fields" class="payment-method-fields" style="display: block;">
        <div class="form-group">
            <label for="upi-id">UPI ID</label>
            <input type="text" id="upi-id" name="upi_id" placeholder="example@upi" form="checkoutForm">
            <small>Enter your VPA (Virtual Payment Address)</small>
        </div>
    </div>

    <div id="card-fields" class="payment-method-fields" style="display: none;">
        <div class="form-group">
            <label for="card-number">Card Number</label>
            <input type="text" id="card-number" name="card_number" placeholder="0000 0000 0000 0000"
                maxlength="19" form="checkoutForm">
        </div>
        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label for="card-expiry">Expiry Date</label>
                <input type="text" id="card-expiry" name="card_expiry" placeholder="MM/YY" maxlength="5"
                    form="checkoutForm">
            </div>
            <div class="form-group" style="flex:1">
                <label for="card-cvv">CVV</label>
                <input type="password" id="card-cvv" name="card_cvv" placeholder="123" maxlength="3"
                    form="checkoutForm">
            </div>
        </div>
    </div>
</div>