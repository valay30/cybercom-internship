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

document.addEventListener('DOMContentLoaded', () => {
    const addToCartForm = document.querySelector('form[action="cart.php"]');

    if (addToCartForm) {
        console.log("Add to Cart form found.");
        addToCartForm.addEventListener('submit', function (e) {
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

                        // Update Price Display Dynamically if quantity info is returned
                        if (data.newQty !== undefined && data.productPrice !== undefined) {
                            updatePDPPrice(data.productPrice, data.newQty);
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
