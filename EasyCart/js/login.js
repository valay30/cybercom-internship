// JavaScript for Tab Switching and Form Validation

function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');

    // Add active class to clicked button
    event.target.classList.add('active');
}

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

function validatePassword(password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    const minLength = password.length >= 6;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);

    return {
        valid: minLength && hasUpper && hasLower && hasNumber,
        minLength,
        hasUpper,
        hasLower,
        hasNumber
    };
}

function validateName(name) {
    // At least 2 characters, only letters and spaces
    return name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name);
}

// Login Form Validation
const loginForm = document.querySelector('#login-tab form');
const loginEmail = document.getElementById('login-email');
const loginPassword = document.getElementById('login-password');

loginEmail.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Email is required');
    } else if (!validateEmail(this.value)) {
        showError(this, 'Please enter a valid email address');
    } else {
        showSuccess(this);
    }
});

loginPassword.addEventListener('blur', function () {
    if (this.value === '') {
        showError(this, 'Password is required');
    } else if (this.value.length < 6) {
        showError(this, 'Password must be at least 6 characters');
    } else {
        showSuccess(this);
    }
});

loginForm.addEventListener('submit', function (e) {
    e.preventDefault();

    let isValid = true;

    // Validate email
    if (loginEmail.value.trim() === '') {
        showError(loginEmail, 'Email is required');
        isValid = false;
    } else if (!validateEmail(loginEmail.value)) {
        showError(loginEmail, 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess(loginEmail);
    }

    // Validate password
    if (loginPassword.value === '') {
        showError(loginPassword, 'Password is required');
        isValid = false;
    } else if (loginPassword.value.length < 6) {
        showError(loginPassword, 'Password must be at least 6 characters');
        isValid = false;
    } else {
        showSuccess(loginPassword);
    }

    if (isValid) {
        // Show loading state
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging in...';

        // Prepare data
        const formData = new FormData(this);
        formData.append('ajax', 'true');

        fetch(this.getAttribute('action'), {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Login successful!', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    showToast(data.error || 'Login failed', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            });
    }
});

// Signup Form Validation
const signupForm = document.querySelector('#signup-tab form');
const signupFullname = document.getElementById('signup-fullname');
const signupEmail = document.getElementById('signup-email');
const signupPassword = document.getElementById('signup-password');
const signupConfirmPassword = document.getElementById('signup-confirm-password');

// ... (Existing input listeners remain, only updating submit handler) ...

signupFullname.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        showError(this, 'Full name is required');
    } else if (!validateName(this.value)) {
        showError(this, 'Please enter a valid name (letters and spaces only, min 2 characters)');
    } else {
        showSuccess(this);
    }
});

signupEmail.addEventListener('blur', function () {
    const email = this.value.trim();
    if (email === '') {
        showError(this, 'Email is required');
    } else if (!validateEmail(email)) {
        showError(this, 'Please enter a valid email address');
    } else {
        // Check if email exists via AJAX
        const formData = new FormData();
        formData.append('action', 'check_email');
        formData.append('email', email);

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    showError(this, 'This email is already registered. Please login.');
                } else {
                    showSuccess(this);
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
                showSuccess(this); // Fallback to success on error
            });
    }
});

signupPassword.addEventListener('input', function () {
    const validation = validatePassword(this.value);

    if (this.value === '') {
        showError(this, 'Password is required');
    } else if (!validation.valid) {
        let message = 'Password must contain: ';
        const missing = [];
        if (!validation.minLength) missing.push('6+ characters');
        if (!validation.hasUpper) missing.push('uppercase letter');
        if (!validation.hasLower) missing.push('lowercase letter');
        if (!validation.hasNumber) missing.push('number');
        message += missing.join(', ');
        showError(this, message);
    } else {
        showSuccess(this);
    }

    // Also validate confirm password if it has a value
    if (signupConfirmPassword.value !== '') {
        signupConfirmPassword.dispatchEvent(new Event('blur'));
    }
});

signupConfirmPassword.addEventListener('blur', function () {
    if (this.value === '') {
        showError(this, 'Please confirm your password');
    } else if (this.value !== signupPassword.value) {
        showError(this, 'Passwords do not match');
    } else {
        showSuccess(this);
    }
});

signupForm.addEventListener('submit', function (e) {
    e.preventDefault();

    let isValid = true;

    // Validate full name
    if (signupFullname.value.trim() === '') {
        showError(signupFullname, 'Full name is required');
        isValid = false;
    } else if (!validateName(signupFullname.value)) {
        showError(signupFullname, 'Please enter a valid name (letters and spaces only, min 2 characters)');
        isValid = false;
    } else {
        showSuccess(signupFullname);
    }

    // Validate email
    if (signupEmail.value.trim() === '') {
        showError(signupEmail, 'Email is required');
        isValid = false;
    } else if (!validateEmail(signupEmail.value)) {
        showError(signupEmail, 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess(signupEmail);
    }

    // Validate password
    const validation = validatePassword(signupPassword.value);
    if (signupPassword.value === '') {
        showError(signupPassword, 'Password is required');
        isValid = false;
    } else if (!validation.valid) {
        let message = 'Password must contain: ';
        const missing = [];
        if (!validation.minLength) missing.push('6+ characters');
        if (!validation.hasUpper) missing.push('uppercase letter');
        if (!validation.hasLower) missing.push('lowercase letter');
        if (!validation.hasNumber) missing.push('number');
        message += missing.join(', ');
        showError(signupPassword, message);
        isValid = false;
    } else {
        showSuccess(signupPassword);
    }

    // Validate confirm password
    if (signupConfirmPassword.value === '') {
        showError(signupConfirmPassword, 'Please confirm your password');
        isValid = false;
    } else if (signupConfirmPassword.value !== signupPassword.value) {
        showError(signupConfirmPassword, 'Passwords do not match');
        isValid = false;
    } else {
        showSuccess(signupConfirmPassword);
    }

    if (isValid) {
        // Show loading state
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        // Prepare data
        const formData = new FormData(this);
        formData.append('ajax', 'true');

        fetch(this.getAttribute('action'), {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Signup successful!', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showToast(data.error || 'Signup failed', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            });
    } else {
        console.log('Form validation failed');
    }
});

// Toast Notification
function showToast(message, type = 'success') {
    // Remove existing
    const existing = document.querySelector('.login-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'login-toast';
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1000;
        transform: translateY(100px);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-left: 4px solid ${type === 'success' ? '#10b981' : '#ef4444'};
    `;

    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    const color = type === 'success' ? '#10b981' : '#ef4444';

    toast.innerHTML = `
        <i class="fa-solid ${icon}" style="color: ${color}; font-size: 1.2rem;"></i>
        <span style="color: #334155; font-weight: 500;">${message}</span>
    `;

    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
    });

    // Auto dismiss
    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        setTimeout(() => toast.remove(), 300);
    }, type === 'success' ? 1500 : 4000);
}

// Clear error on input
document.querySelectorAll('input').forEach(input => {
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


// Auto-hide alert messages after 3 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                alert.remove();
            }, 500); // Wait for fade out to finish
        }, 3000); // 3 seconds delay
    }
});
