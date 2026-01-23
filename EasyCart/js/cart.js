//JavaScript for Cart Interactions

// Update quantity (increase or decrease)
function updateQuantity(productId, change) {
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const qtyInput = cartItem.querySelector('.qty-input');
    const currentQty = parseInt(qtyInput.value);
    const newQty = currentQty + change;

    // Prevent quantity from going below 1
    if (newQty < 1) {
        return;
    }

    // Update the input value
    qtyInput.value = newQty;

    // Update the hidden form input
    const hiddenQty = cartItem.querySelector('.hidden-qty');
    hiddenQty.value = newQty;

    // Recalculate the item total
    recalculateItemTotal(productId);

    // Recalculate the cart totals
    recalculateCartTotals();

    // Submit the hidden form to update server-side
    setTimeout(() => {
        cartItem.querySelector('.hidden-update-form').submit();
    }, 500);
}

// Remove item from cart
function removeItem(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
        cartItem.querySelector('.hidden-remove-form').submit();
    }
}

// Recalculate individual item total
function recalculateItemTotal(productId) {
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const price = parseFloat(cartItem.dataset.price);
    const qty = parseInt(cartItem.querySelector('.qty-input').value);
    const total = price * qty;

    // Update the item total display
    const itemTotalDiv = cartItem.querySelector('.cart-item-total');
    itemTotalDiv.textContent = '₹' + total.toLocaleString('en-IN');
}

// Recalculate cart subtotal and total
function recalculateCartTotals() {
    let subtotal = 0;

    // Sum up all item totals
    document.querySelectorAll('.cart-item').forEach(item => {
        const price = parseFloat(item.dataset.price);
        const qty = parseInt(item.querySelector('.qty-input').value);
        subtotal += price * qty;
    });

    // Update subtotal display
    const subtotalElements = document.querySelectorAll('.summary-row:not(.total) span:last-child');
    if (subtotalElements.length > 0) {
        subtotalElements[0].textContent = '₹' + subtotal.toLocaleString('en-IN');
    }

    // Update total display (assuming no shipping/tax for now)
    const totalElements = document.querySelectorAll('.summary-row.total span:last-child');
    if (totalElements.length > 0) {
        totalElements[0].textContent = '₹' + subtotal.toLocaleString('en-IN');
    }
}

// Add visual feedback for buttons
document.addEventListener('DOMContentLoaded', function () {
    // Add hover effects and animations
    const qtyButtons = document.querySelectorAll('.qty-btn');
    qtyButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            // Add a pulse animation
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
    });
});