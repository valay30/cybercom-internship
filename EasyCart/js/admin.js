/**
 * Admin Import/Export JavaScript
 * Handles file upload UI and AJAX CSV download
 */

// File Upload Handling
const fileInput = document.getElementById('csv_file');
const uploadArea = document.getElementById('uploadArea');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');

// File input change
if (fileInput) {
    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            fileName.textContent = this.files[0].name;
            fileInfo.classList.add('show');
        }
    });
}

// Clear file
function clearFile() {
    fileInput.value = '';
    fileInfo.classList.remove('show');
}

// Drag and drop handlers
if (uploadArea) {
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.borderColor = '#6366f1';
        this.style.background = '#eef2ff';
    });

    uploadArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        this.style.borderColor = '#cbd5e1';
        this.style.background = '#f8fafc';
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.borderColor = '#cbd5e1';
        this.style.background = '#f8fafc';

        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            if (file.name.endsWith('.csv')) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = file.name;
                fileInfo.classList.add('show');
            } else {
                alert('Please upload a CSV file');
            }
        }
    });
}

// AJAX CSV Download
function downloadCSV() {
    const btn = document.getElementById('exportBtn');
    const icon = document.getElementById('exportIcon');
    const text = document.getElementById('exportText');

    // Show loading state
    btn.disabled = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    text.textContent = 'Downloading...';

    // Fetch CSV file
    fetch('admin?action=export', {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Export failed');
            }
            return response.blob();
        })
        .then(blob => {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'products_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.csv';
            document.body.appendChild(a);
            a.click();

            // Cleanup
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            // Show success message
            showNotification('CSV file downloaded successfully!', 'success');

            // Reset button state
            btn.disabled = false;
            icon.className = 'fa-solid fa-file-export';
            text.textContent = 'Download CSV';
        })
        .catch(error => {
            console.error('Download error:', error);
            showNotification('Failed to download CSV file', 'error');

            // Reset button state
            btn.disabled = false;
            icon.className = 'fa-solid fa-file-export';
            text.textContent = 'Download CSV';
        });
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
    notification.innerHTML = `
        <i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'circle-exclamation'}"></i>
        <div>${message}</div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
