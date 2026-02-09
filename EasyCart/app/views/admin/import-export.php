<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Import/Export - EasyCart Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1><i class="fa-solid fa-gear"></i> Product Import / Export</h1>
            <p>Manage your product catalog in bulk using CSV files</p>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Import Errors -->
        <?php if (isset($_SESSION['import_errors']) && !empty($_SESSION['import_errors'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong>Import Errors:</strong>
                    <ul class="error-list">
                        <?php foreach ($_SESSION['import_errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php unset($_SESSION['import_errors']); ?>
        <?php endif; ?>

        <!-- Export Section -->
        <div class="section-card">
            <h2>
                <i class="fa-solid fa-download"></i>
                Export Products
            </h2>
            <p>Download all products from the database as a CSV file.</p>

            <div class="btn-group">
                <a href="admin?action=export" class="btn btn-primary">
                    <i class="fa-solid fa-file-export"></i>
                    Download CSV
                </a>
            </div>

            <div class="info-box">
                <h4>CSV Format:</h4>
                <p style="margin: 10px 0; color: #64748b; font-size: 14px;">
                    The exported file contains these columns:
                </p>
                <code>sku, name, description, price, shipping_type, category_code, brand_code, image_path, features</code>
            </div>
        </div>

        <!-- Import Section -->
        <div class="section-card">
            <h2>
                <i class="fa-solid fa-upload"></i>
                Import Products
            </h2>
            <p>Upload a CSV file to add or update products in bulk. Existing products (matched by SKU) will be updated.</p>

            <form action="admin?action=import" method="POST" enctype="multipart/form-data" id="importForm">
                <div class="file-upload-area" id="uploadArea">
                    <div class="file-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <p style="margin: 10px 0; color: #64748b;">
                        <label for="csv_file">Click to browse</label> or drag and drop your CSV file here
                    </p>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                    <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">Maximum file size: 10MB</p>
                </div>

                <div class="selected-file-info" id="fileInfo">
                    <i class="fa-solid fa-file-csv"></i>
                    <span class="file-name" id="fileName"></span>
                    <button type="button" onclick="clearFile()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-file-import"></i>
                        Import Products
                    </button>
                    <a href="plp" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Products
                    </a>
                </div>
            </form>

            <div class="info-box">
                <h4>Important Notes:</h4>
                <ul>
                    <li><strong>Existing products</strong> (same SKU) will be updated with new data</li>
                    <li><strong>New products</strong> (new SKU) will be created</li>
                    <li><strong>Category and Brand codes</strong> must already exist in the database</li>
                    <li><strong>Features</strong> should be separated by pipe character: <code>Feature 1|Feature 2|Feature 3</code></li>
                    <li><strong>All changes</strong> are wrapped in a transaction (all-or-nothing)</li>
                </ul>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../common/footer.php'; ?>

    <script>
        const fileInput = document.getElementById('csv_file');
        const uploadArea = document.getElementById('uploadArea');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');

        // File input change
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileInfo.classList.add('show');
            }
        });

        // Clear file
        function clearFile() {
            fileInput.value = '';
            fileInfo.classList.remove('show');
        }

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#6366f1';
            this.style.background = '#eef2ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#cbd5e1';
            this.style.background = '#f8fafc';
        });

        uploadArea.addEventListener('drop', function(e) {
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
    </script>
</body>

</html>