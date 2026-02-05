// JavaScript for Cart Interactions

// Update quantity (increase or decrease)
function updateQuantity(productId, change) {
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const qtyInput = cartItem.querySelector('.qty-input');
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;

    // Prevent quantity from going below 1
    if (newQty < 1) {
        return;
    }

    // Optimistic UI update
    qtyInput.value = newQty;

    // Update visual prices immediately for responsiveness
    updateItemVisuals(cartItem, newQty);

    // Send AJAX request
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', productId);
    formData.append('qty', newQty);
    formData.append('ajax', 'true');

    fetch('cart', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item total from server
                const itemTotalDiv = cartItem.querySelector('.cart-item-total');
                if (itemTotalDiv) {
                    itemTotalDiv.textContent = '₹' + data.itemTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                // Update cart subtotal/total from server
                updateCartSummary(data.subtotal);

                // Update hidden input if present (fallback)
                const hiddenQty = cartItem.querySelector('.hidden-qty');
                if (hiddenQty) hiddenQty.value = newQty;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            qtyInput.value = currentQty;
            updateItemVisuals(cartItem, currentQty);
            alert('Failed to update quantity. Please try again.');
        });
}

function updateItemVisuals(cartItem, qty) {
    const price = parseFloat(cartItem.dataset.price);
    let discountPercent = qty;
    if (discountPercent > 50) discountPercent = 50;
    const discountedPrice = price * (1 - (discountPercent / 100));

    // Update the displayed unit price
    const priceParagraph = cartItem.querySelector('.cart-item-details p');
    if (priceParagraph) {
        const spans = priceParagraph.querySelectorAll('span');
        // Assuming second span is the discounted price based on PHP structure
        if (spans.length >= 2) {
            spans[1].textContent = '₹' + discountedPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}

function updateCartSummary(subtotal) {
    const formattedSubtotal = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Update subtotal display
    const subtotalElements = document.querySelectorAll('.summary-row:not(.total) span:last-child');
    if (subtotalElements.length > 0) {
        subtotalElements[0].textContent = formattedSubtotal;
    }

    // Update total display
    const totalElements = document.querySelectorAll('.summary-row.total span:last-child');
    if (totalElements.length > 0) {
        totalElements[0].textContent = formattedSubtotal;
    }
}

// Remove item from cart
function removeItem(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('id', productId);
        formData.append('ajax', 'true');

        fetch('cart', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    if (cartItem) {
                        // Animate removal
                        cartItem.style.transition = 'opacity 0.3s ease';
                        cartItem.style.opacity = '0';
                        setTimeout(() => {
                            cartItem.remove();
                            updateCartSummary(data.subtotal);

                            // Check if cart is empty
                            if (document.querySelectorAll('.cart-item').length === 0) {
                                location.reload(); // Reload to show "Your cart is empty" message
                            }
                        }, 300);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item.');
            });
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