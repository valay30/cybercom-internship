// JavaScript for Checkout Form Validation

// Validation Helper Functions
function showError(input, message) {
    const formGroup = input.parentElement;
    let errorDiv = formGroup.querySelector('.error-message');

    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        formGroup.appendChild(errorDiv);
    }

    errorDiv.textContent = message;
    input.classList.add('error');
    input.classList.remove('success');
}

function showSuccess(input) {
    const formGroup = input.parentElement;
    const errorDiv = formGroup.querySelector('.error-message');

    if (errorDiv) {
        errorDiv.remove();
    }

    input.classList.remove('error');
    input.classList.add('success');
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateMobile(mobile) {
    // Exactly 10 digits
    const re = /^[0-9]{10}$/;
    return re.test(mobile);
}

function validateName(name) {
    // At least 2 characters, only letters and spaces
    return name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name);
}

function validateAddress(address) {
    // At least 10 characters
    return address.trim().length >= 10;
}

// Get form elements
const checkoutForm = document.getElementById('checkoutForm');
const nameInput = document.getElementById('name');
const mobileInput = document.getElementById('mobile');
const emailInput = document.getElementById('email');
const addressInput = document.getElementById('address');

// ===================================
// LocalStorage Form Data Persistence
// ===================================

const STORAGE_KEY = 'easycart_checkout_data';

// Function to save form data to localStorage
function saveFormData() {
    const formData = {
        name: nameInput.value,
        mobile: mobileInput.value,
        email: emailInput.value,
        address: addressInput.value,
        shipping: document.querySelector('input[name="shipping"]:checked')?.value || '',
        payment: document.querySelector('input[name="payment"]:checked')?.value || '',
        upiId: document.getElementById('upi-id')?.value || '',
        cardNumber: document.getElementById('card-number')?.value || '',
        cardExpiry: document.getElementById('card-expiry')?.value || '',
        timestamp: new Date().getTime()
    };

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
        showAutoSaveIndicator();
    } catch (e) {
        console.warn('Failed to save form data to localStorage:', e);
    }
}

// Function to show auto-save indicator
let autoSaveTimeout;
function showAutoSaveIndicator() {
    const indicator = document.getElementById('auto-save-indicator');
    if (!indicator) return;

    // Clear any existing timeout
    if (autoSaveTimeout) clearTimeout(autoSaveTimeout);

    // Show indicator
    indicator.style.display = 'flex';

    // Hide after 2 seconds
    autoSaveTimeout = setTimeout(() => {
        indicator.style.display = 'none';
    }, 2000);
}

// Function to restore form data from localStorage
function restoreFormData() {
    try {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (!savedData) return;

        const formData = JSON.parse(savedData);

        // Check if data is not too old (7 days)
        const sevenDaysInMs = 7 * 24 * 60 * 60 * 1000;
        if (new Date().getTime() - formData.timestamp > sevenDaysInMs) {
            localStorage.removeItem(STORAGE_KEY);
            return;
        }

        // Restore personal details
        if (formData.name) nameInput.value = formData.name;
        if (formData.mobile) mobileInput.value = formData.mobile;
        if (formData.email) emailInput.value = formData.email;
        if (formData.address) addressInput.value = formData.address;

        // Restore shipping selection
        if (formData.shipping) {
            const shippingRadio = document.querySelector(`input[name="shipping"][value="${formData.shipping}"]`);
            if (shippingRadio) {
                shippingRadio.checked = true;
                updateShipping(shippingRadio);
            }
        }

        // Restore payment selection
        if (formData.payment) {
            const paymentRadio = document.querySelector(`input[name="payment"][value="${formData.payment}"]`);
            if (paymentRadio) {
                paymentRadio.checked = true;
                updatePayment(paymentRadio);
            }
        }

        // Restore payment details
        if (formData.upiId) {
            const upiInput = document.getElementById('upi-id');
            if (upiInput) upiInput.value = formData.upiId;
        }
        if (formData.cardNumber) {
            const cardNumInput = document.getElementById('card-number');
            if (cardNumInput) cardNumInput.value = formData.cardNumber;
        }
        if (formData.cardExpiry) {
            const cardExpiryInput = document.getElementById('card-expiry');
            if (cardExpiryInput) cardExpiryInput.value = formData.cardExpiry;
        }

    } catch (e) {
        console.warn('Failed to restore form data from localStorage:', e);
    }
}

// Function to clear saved form data
function clearFormData() {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
        console.warn('Failed to clear form data from localStorage:', e);
    }
}

// Auto-save form data on input changes
const formInputs = [nameInput, mobileInput, emailInput, addressInput];
formInputs.forEach(input => {
    input.addEventListener('input', saveFormData);
});

// Save on shipping/payment selection change
document.addEventListener('change', function (e) {
    if (e.target.name === 'shipping' || e.target.name === 'payment') {
        saveFormData();
    }
});

// Save payment details on input
const upiIdInput = document.getElementById('upi-id');
const cardNumberInput = document.getElementById('card-number');
const cardExpiryInput = document.getElementById('card-expiry');

if (upiIdInput) upiIdInput.addEventListener('input', saveFormData);
if (cardNumberInput) cardNumberInput.addEventListener('input', saveFormData);
if (cardExpiryInput) cardExpiryInput.addEventListener('input', saveFormData);

// Restore form data when page loads
window.addEventListener('DOMContentLoaded', restoreFormData);

// Clear form data after successful order placement
checkoutForm.addEventListener('submit', function (e) {
    // Only clear if form is valid (will be submitted)
    const isValid = checkoutForm.checkValidity();
    if (isValid) {
        // Clear saved data after a short delay to ensure form submits first
        setTimeout(clearFormData, 100);
    }
});

// Name validation
nameInput.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Full name is required');
    } else if (!validateName(this.value)) {
        showError(this, 'Please enter a valid name (letters and spaces only, min 2 characters)');
    } else {
        showSuccess(this);
    }
});

// Mobile validation
mobileInput.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Mobile number is required');
    } else if (!validateMobile(this.value)) {
        showError(this, 'Please enter a valid 10-digit mobile number');
    } else {
        showSuccess(this);
    }
});

// Only allow numbers in mobile input
mobileInput.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

// Email validation
emailInput.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Email is required');
    } else if (!validateEmail(this.value)) {
        showError(this, 'Please enter a valid email address');
    } else {
        showSuccess(this);
    }
});

// Address validation
addressInput.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Shipping address is required');
    } else if (!validateAddress(this.value)) {
        showError(this, 'Please enter a complete address (minimum 10 characters)');
    } else {
        showSuccess(this);
    }
});

// Form submission validation
checkoutForm.addEventListener('submit', function (e) {
    e.preventDefault();

    let isValid = true;

    // Validate name
    if (nameInput.value.trim() === '') {
        showError(nameInput, 'Full name is required');
        isValid = false;
    } else if (!validateName(nameInput.value)) {
        showError(nameInput, 'Please enter a valid name (letters and spaces only, min 2 characters)');
        isValid = false;
    } else {
        showSuccess(nameInput);
    }

    // Validate mobile
    if (mobileInput.value.trim() === '') {
        showError(mobileInput, 'Mobile number is required');
        isValid = false;
    } else if (!validateMobile(mobileInput.value)) {
        showError(mobileInput, 'Please enter a valid 10-digit mobile number');
        isValid = false;
    } else {
        showSuccess(mobileInput);
    }

    // Validate email
    if (emailInput.value.trim() === '') {
        showError(emailInput, 'Email is required');
        isValid = false;
    } else if (!validateEmail(emailInput.value)) {
        showError(emailInput, 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess(emailInput);
    }

    // Validate address
    if (addressInput.value.trim() === '') {
        showError(addressInput, 'Shipping address is required');
        isValid = false;
    } else if (!validateAddress(addressInput.value)) {
        showError(addressInput, 'Please enter a complete address (minimum 10 characters)');
        isValid = false;
    } else {
        showSuccess(addressInput);
    }

    // Payment Validation
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;

    if (paymentMethod === 'upi') {
        const upiInput = document.getElementById('upi-id');
        if (upiInput.value.trim() === '') {
            showError(upiInput, 'UPI ID is required');
            isValid = false;
        } else if (!/^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/.test(upiInput.value)) {
            showError(upiInput, 'Invalid UPI ID format');
            isValid = false;
        } else {
            showSuccess(upiInput);
        }
    } else if (paymentMethod === 'card') {
        const cardNum = document.getElementById('card-number');
        const cardExpiry = document.getElementById('card-expiry');
        const cardCvv = document.getElementById('card-cvv');

        // Validate Card Number
        if (cardNum.value.replace(/\s/g, '').length < 16) {
            showError(cardNum, 'Invalid Card Number');
            isValid = false;
        } else {
            showSuccess(cardNum);
        }

        // Validate Expiry
        if (!/^(0[1-9]|1[0-2])\/([0-9]{2})$/.test(cardExpiry.value)) {
            showError(cardExpiry, 'Invalid Expiry (MM/YY)');
            isValid = false;
        } else {
            showSuccess(cardExpiry);
        }

        // Validate CVV
        if (!/^[0-9]{3}$/.test(cardCvv.value)) {
            showError(cardCvv, 'Invalid CVV');
            isValid = false;
        } else {
            showSuccess(cardCvv);
        }
    }

    if (isValid) {
        // In production, submit the form
        this.submit();
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
});

// Clear error on input
[nameInput, mobileInput, emailInput, addressInput].forEach(input => {
    input.addEventListener('input', function () {
        if (this.classList.contains('error')) {
            const formGroup = this.parentElement;
            const errorDiv = formGroup.querySelector('.error-message');
            if (errorDiv && this.value.trim() !== '') {
                errorDiv.style.opacity = '0.5';
            }
        }
    });
});


//JavaScript for Shipping Option Highlighting with AJAX

function updateShipping(radio) {
    console.log('🚀 updateShipping called with value:', radio.value);

    // Remove active class from all shipping options
    document.querySelectorAll('.shipping-option').forEach(option => {
        option.classList.remove('active');
    });

    // Add active class to selected option
    radio.closest('.shipping-option').classList.add('active');

    // Get shipping cost from selected radio button
    const shippingCost = parseFloat(radio.value);

    // Get summary elements
    const shippingElement = document.getElementById('summary-shipping');
    const taxElement = document.getElementById('summary-tax');
    const totalElement = document.getElementById('summary-total');
    const summarySection = document.querySelector('.cart-summary-section');

    // Add loading state with visual feedback
    if (summarySection) {
        summarySection.style.transition = 'opacity 0.3s ease';
        summarySection.style.opacity = '0.6';
        summarySection.style.pointerEvents = 'none';
    }

    // Create FormData for AJAX request
    const formData = new FormData();
    formData.append('action', 'update_shipping');
    formData.append('shipping_cost', shippingCost);
    formData.append('ajax', 'true');

    // Send AJAX request to server
    fetch('checkout.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update shipping cost with animation
                if (shippingElement) {
                    shippingElement.style.transition = 'all 0.3s ease';
                    shippingElement.style.transform = 'scale(1.1)';
                    shippingElement.style.color = '#10b981';
                    shippingElement.textContent = data.formatted.shipping;

                    setTimeout(() => {
                        shippingElement.style.transform = 'scale(1)';
                        shippingElement.style.color = '';
                    }, 300);
                }

                // Update tax with animation
                if (taxElement) {
                    taxElement.style.transition = 'all 0.3s ease';
                    taxElement.style.transform = 'scale(1.1)';
                    taxElement.style.color = '#10b981';
                    taxElement.textContent = data.formatted.tax;

                    setTimeout(() => {
                        taxElement.style.transform = 'scale(1)';
                        taxElement.style.color = '';
                    }, 300);
                }

                // Update total with animation
                if (totalElement) {
                    totalElement.style.transition = 'all 0.3s ease';
                    totalElement.style.transform = 'scale(1.1)';
                    totalElement.style.color = '#10b981';
                    totalElement.textContent = data.formatted.total;

                    setTimeout(() => {
                        totalElement.style.transform = 'scale(1)';
                        totalElement.style.color = '';
                    }, 300);
                }

                // Remove loading state
                if (summarySection) {
                    summarySection.style.opacity = '1';
                    summarySection.style.pointerEvents = 'auto';
                }

                console.log('✅ Shipping updated via AJAX:', data);
            } else {
                throw new Error('Server returned error');
            }
        })
        .catch(error => {
            console.error('❌ AJAX Error:', error);
            alert('Failed to update shipping. Please try again.');

            // Remove loading state on error
            if (summarySection) {
                summarySection.style.opacity = '1';
                summarySection.style.pointerEvents = 'auto';
            }
        });
}

//JavaScript for Payment Method Highlighting

function updatePayment(radio) {
    console.log("Payment changed to:", radio.value); // Debug Log

    // Remove active class from all payment options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
    });

    // Add active class to selected option
    radio.closest('.payment-option').classList.add('active');

    // Toggle Fields
    const upiFields = document.getElementById('upi-fields');
    const cardFields = document.getElementById('card-fields');

    if (upiFields) upiFields.style.display = 'none';
    if (cardFields) cardFields.style.display = 'none';

    if (radio.value === 'upi' && upiFields) {
        upiFields.style.display = 'block';
    } else if (radio.value === 'card' && cardFields) {
        cardFields.style.display = 'block';
    }
}

// Attach Event Listeners on Load
document.addEventListener('DOMContentLoaded', function () {
    const paymentRadios = document.querySelectorAll('input[name="payment"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            updatePayment(this);
        });
    });
});
