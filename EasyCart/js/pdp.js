// JavaScript for Image Switching
function switchImage(imageSrc, thumbnail) {
    // Update the main image
    document.getElementById('mainImage').src = imageSrc;

    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });

    // Add active class to clicked thumbnail
    thumbnail.classList.add('active');
}


// ==========================================
// ATTACH ADD TO CART LISTENER (Reusable)
// ==========================================
function attachAddToCartListener(form) {
    if (!form) return;

    console.log("Attaching Add to Cart listener to form:", form);
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log("Add to Cart submitted.");

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        const productId = this.querySelector('input[name="id"]').value;

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

        const formData = new FormData(this);
        formData.append('ajax', 'true');

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log("Response received", response);
                return response.json();
            })
            .then(data => {
                console.log("Data:", data);
                if (data.success) {
                    showToast('Item added to cart successfully!', 'success');

                    // Get the product ID
                    const productId = formData.get('id');
                    const newQty = data.newQty || 1;

                    // Replace the form with quantity controls
                    const quantityControlsHTML = `
                        <div class="quantity-controls-pdp" style="display: inline-flex; align-items: center; gap: 15px; background: white; border: 2px solid var(--primary); border-radius: 8px; padding: 8px 16px;">
                            <button onclick="updateQuantity('${productId}', -1)" 
                                class="qty-btn" 
                                style="background: none; border: none; color: var(--primary); font-size: 1.5rem; cursor: pointer; padding: 0 8px; transition: all 0.2s;"
                                onmouseover="this.style.transform='scale(1.2)'" 
                                onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span id="qty-display-${productId}" style="font-weight: 600; font-size: 1.2rem; min-width: 30px; text-align: center;">
                                ${newQty}
                            </span>
                            <button onclick="updateQuantity('${productId}', 1)" 
                                class="qty-btn" 
                                style="background: none; border: none; color: var(--primary); font-size: 1.5rem; cursor: pointer; padding: 0 8px; transition: all 0.2s;"
                                onmouseover="this.style.transform='scale(1.2)'" 
                                onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    `;

                    // Replace the form with quantity controls
                    form.outerHTML = quantityControlsHTML;

                    // Update Price Display with discount
                    if (data.productPrice !== undefined) {
                        updatePDPPrice(data.productPrice, newQty);
                    }
                } else {
                    showToast('Failed to add item to cart.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const addToCartForm = document.querySelector('form[action="cart.php"]');

    if (addToCartForm) {
        console.log("Add to Cart form found on page load.");
        attachAddToCartListener(addToCartForm);
    } else {
        console.error("Add to Cart form NOT found.");
    }
});

function updatePDPPrice(originalPrice, qty) {
    const priceContainer = document.querySelector('.pdp-info-section .product-price');
    if (!priceContainer) return;

    let discountPercent = qty;
    if (discountPercent > 50) discountPercent = 50;

    // Only apply discount visuals if qty > 0 (which it should be after add)
    if (discountPercent > 0) {
        const discountedPrice = originalPrice * (1 - (discountPercent / 100));

        // Format to Indian Currency
        const fmtOriginal = '₹' + parseFloat(originalPrice).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const fmtFinal = '₹' + discountedPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        priceContainer.innerHTML = `
            <span style="text-decoration: line-through; color:var(--primary); font-size: 0.7em;">${fmtOriginal}</span>
            <span style="color: black;">${fmtFinal}</span>
            <span style="background: #fee2e2; color: black; padding: 4px 10px; border-radius: 20px; font-size: 0.5em; vertical-align: middle; margin-left: 10px;">
                ${discountPercent}% OFF
            </span>
        `;
    }
}

function showToast(message, type = 'success') {
    console.log("Showing toast:", message, type);
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `ec-toast ec-toast-${type}`; // Updated class name
    toast.innerHTML = `
        <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
        <span>${message}</span>
    `;

    // Append to body
    document.body.appendChild(toast);
    console.log("Toast appended to body");

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
        console.log("Toast 'show' class added");
    }, 10);


    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// ==========================================
// UPDATE QUANTITY ON PDP (+ / - Buttons)
// ==========================================
function updateQuantity(productId, change) {
    const qtyDisplay = document.getElementById(`qty-display-${productId}`);
    if (!qtyDisplay) return;

    let currentQty = parseInt(qtyDisplay.textContent);
    let newQty = currentQty + change;

    // Prevent negative quantities
    if (newQty < 0) newQty = 0;

    // Send AJAX request to update cart
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', productId);
    formData.append('qty', newQty);
    formData.append('ajax', 'true');

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If quantity is now 0, replace with "Add to Cart" button
                if (newQty === 0) {
                    const quantityControls = document.querySelector('.quantity-controls-pdp');
                    if (quantityControls) {
                        const addToCartHTML = `
                            <form id="add-to-cart-form" action="cart.php" method="POST"
                                style="box-shadow:none; padding:0; border:none; max-width:100%; display:inline-block;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="${productId}">
                                <button type="submit" class="add-to-cart-btn"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
                            </form>
                        `;
                        quantityControls.outerHTML = addToCartHTML;

                        // Re-attach the event listener to the new form
                        setTimeout(() => {
                            const newForm = document.getElementById('add-to-cart-form');
                            if (newForm) {
                                attachAddToCartListener(newForm);
                            }
                        }, 100);

                        // Reset price to original (no discount)
                        const priceContainer = document.querySelector('.pdp-info-section .product-price');
                        if (priceContainer && data.productPrice) {
                            const fmtOriginal = '₹' + parseFloat(data.productPrice).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            priceContainer.innerHTML = fmtOriginal;
                        }
                    }
                    showToast('Item removed from cart', 'success');
                } else {
                    // Update the display
                    qtyDisplay.textContent = newQty;

                    // Update price display with new discount
                    if (data.productPrice !== undefined) {
                        updatePDPPrice(data.productPrice, newQty);
                    }

                    showToast('Quantity updated!', 'success');
                }
            } else {
                showToast('Failed to update quantity', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
}

