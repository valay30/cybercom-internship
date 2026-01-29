
function toggleWishlist(productId, btn) {
    const icon = btn.querySelector('i');

    // Optimistic UI Update
    const isAdding = icon.classList.contains('fa-regular');

    if (isAdding) {
        icon.classList.remove('fa-regular');
        icon.classList.add('fa-solid');
        icon.style.color = '#ef4444'; // Red
        // Animation
        icon.style.transform = 'scale(1.2)';
        setTimeout(() => icon.style.transform = 'scale(1)', 200);
    } else {
        icon.classList.remove('fa-solid');
        icon.classList.add('fa-regular');
        icon.style.color = '#64748b'; // Slate 500
    }

    // Prepare FormData
    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('id', productId);
    formData.append('ajax', 'true');

    // Send Request
    fetch('wishlist.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show toast message (reuse if function exists or create local one)
                const message = data.status === 'added' ? 'Added to Wishlist' : 'Removed from Wishlist';
                showToast(message, 'success');
            } else {
                // Revert UI on error
                toggleVisuals(icon, !isAdding);
                alert('Something went wrong.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert UI on error
            toggleVisuals(icon, !isAdding);
        });
}

function toggleVisuals(icon, toFill) {
    if (toFill) {
        icon.classList.remove('fa-regular');
        icon.classList.add('fa-solid');
        icon.style.color = '#ef4444';
    } else {
        icon.classList.remove('fa-solid');
        icon.classList.add('fa-regular');
        icon.style.color = '#64748b';
    }
}

// Simple Toast Notification (if not already defined)
function showToast(message, type = 'success') {
    // Remove existing toasts
    document.querySelectorAll('.ec-toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `ec-toast ec-toast-${type}`;
    // Basic Style for toast if CSS not present
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.background = 'white';
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '10px';
    toast.style.zIndex = '9999';
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '1'; // Ensure visibility override
    toast.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';

    const iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-info';
    const color = type === 'success' ? '#10b981' : '#3b82f6';

    toast.innerHTML = `
        <i class="fa-solid ${iconClass}" style="color: ${color}; font-size: 1.2rem;"></i>
        <span style="font-weight: 500; color: #1e293b;">${message}</span>
    `;

    document.body.appendChild(toast);

    // Show
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
    });

    // Hide
    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
