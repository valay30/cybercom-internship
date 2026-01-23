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


//JavaScript for Shipping Option Highlighting

function updateShipping(radio) {
    // Remove active class from all shipping options
    document.querySelectorAll('.shipping-option').forEach(option => {
        option.classList.remove('active');
    });

    // Add active class to selected option
    radio.closest('.shipping-option').classList.add('active');

    // Update shipping cost in summary
    const shippingCost = parseInt(radio.value);
    const subtotalText = document.querySelector('.summary-row:not(.total) span:last-child').textContent;
    const subtotal = parseInt(subtotalText.replace(/[^0-9]/g, ''));
    const total = subtotal + shippingCost;

    // Update shipping charges display
    const shippingElements = document.querySelectorAll('.summary-row')[1];
    if (shippingElements) {
        shippingElements.querySelector('span:last-child').textContent = '₹' + shippingCost.toLocaleString('en-IN');
    }

    // Update total amount display
    const totalElements = document.querySelector('.summary-row.total span:last-child');
    if (totalElements) {
        totalElements.textContent = '₹' + total.toLocaleString('en-IN');
    }
}

//JavaScript for Payment Method Highlighting

function updatePayment(radio) {
    // Remove active class from all payment options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
    });

    // Add active class to selected option
    radio.closest('.payment-option').classList.add('active');
}
