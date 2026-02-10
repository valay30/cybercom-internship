<?php

/**
 * AdminController
 * Handles admin operations including CSV Import/Export
 */

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';
require_once __DIR__ . '/../../config/database.php';

class AdminController
{
    private $productModel;
    private $categoryModel;
    private $brandModel;
    private $customerModel;
    private $qb;

    public function __construct()
    {
        // Sync cookie to session if session is missing but cookie exists
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id']) && isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true') {
            $_SESSION['user_id'] = $_COOKIE['user_id'];
        }

        // Check authentication - require either session or valid cookie
        $userId = $_SESSION['user_id'] ?? null;
        $isLoggedIn = isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true';

        if (!$userId || !$isLoggedIn) {
            $_SESSION['error'] = "Please login to access admin features.";
            header('Location: login?redirect=admin');
            exit;
        }

        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->brandModel = new BrandModel();

        require_once __DIR__ . '/../models/CustomerModel.php';
        $this->customerModel = new CustomerModel();

        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder();
    }

    /**
     * Show import/export page
     */
    public function showImportExport()
    {
        include __DIR__ . '/../views/admin/import-export.php';
    }

    /**
     * Export products to CSV
     */
    public function exportProducts()
    {
        try {
            $products = $this->productModel->getAllProducts();

            // Set CSV headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=products_' . date('Ymd_His') . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');

            // CSV Header
            fputcsv($output, [
                'sku',
                'name',
                'description',
                'price',
                'shipping_type',
                'category_code',
                'brand_code',
                'image_path',
                'features'
            ]);

            // Write product data
            foreach ($products as $product) {
                $features = !empty($product['features']) ? implode('|', $product['features']) : '';

                fputcsv($output, [
                    $product['id'],
                    $product['name'],
                    $product['description'],
                    $product['price'],
                    $product['shipping_type'] ?? 'standard',
                    $product['category'] ?? '',
                    $product['brand'] ?? '',
                    $product['image'] ?? '',
                    $features
                ]);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            $_SESSION['error'] = "Export failed: " . $e->getMessage();
            header('Location: admin');
            exit;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        if (!$userId) {
            $_SESSION['admin_error'] = "User ID required.";
            header('Location: admin');
            exit;
        }

        // Prevent self-deletion
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['admin_error'] = "Cannot delete logged-in admin user.";
            header('Location: admin');
            exit;
        }

        if ($this->customerModel->deleteCustomer($userId)) {
            $_SESSION['admin_success'] = "User deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete user.";
        }

        header('Location: admin');
        exit;
    }

    /**
     * Import products from CSV
     */
    public function importProducts()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin');
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Please upload a valid CSV file.";
            header('Location: admin');
            exit;
        }

        $file = $_FILES['csv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'csv') {
            $_SESSION['error'] = "Only CSV files are allowed.";
            header('Location: admin');
            exit;
        }

        try {
            $results = $this->processCSVImport($file['tmp_name']);

            $_SESSION['success'] = sprintf(
                "Import completed! %d new, %d updated, %d failed.",
                $results['inserted'],
                $results['updated'],
                $results['failed']
            );

            if (!empty($results['errors'])) {
                $_SESSION['import_errors'] = $results['errors'];
            }
        } catch (Exception $e) {
            error_log("Import error: " . $e->getMessage());
            $_SESSION['error'] = "Import failed: " . $e->getMessage();
        }

        header('Location: admin');
        exit;
    }

    /**
     * Process CSV import
     */
    private function processCSVImport($filePath)
    {
        $results = [
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Could not open CSV file.");
        }

        // Read and validate header
        $header = fgetcsv($handle);
        $expectedHeader = ['sku', 'name', 'description', 'price', 'shipping_type', 'category_code', 'brand_code', 'image_path', 'features'];

        if (!$header || array_diff($expectedHeader, $header)) {
            fclose($handle);
            throw new Exception("Invalid CSV format. Expected columns: " . implode(', ', $expectedHeader));
        }

        $lineNumber = 1;
        $this->qb->beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (empty(array_filter($row))) {
                    continue;
                }

                $data = array_combine($header, $row);
                $result = $this->importSingleProduct($data, $lineNumber);

                if ($result['success']) {
                    if ($result['updated']) {
                        $results['updated']++;
                    } else {
                        $results['inserted']++;
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Line {$lineNumber}: {$result['error']}";
                }
            }

            $this->qb->commit();
        } catch (Exception $e) {
            $this->qb->rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);
        return $results;
    }

    /**
     * Import single product
     */
    private function importSingleProduct($data, $lineNumber)
    {
        // Validate required fields
        if (empty($data['sku']) || empty($data['name']) || empty($data['price'])) {
            return ['success' => false, 'error' => 'Missing required fields (sku, name, price)'];
        }

        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['success' => false, 'error' => 'Invalid price'];
        }

        try {
            // Check if product exists
            $existing = $this->getProductBySku($data['sku']);
            $isUpdate = !empty($existing);

            // Validate category
            $categoryId = null;
            if (!empty($data['category_code'])) {
                $categoryId = $this->getCategoryIdByCode($data['category_code']);
                if (!$categoryId) {
                    return ['success' => false, 'error' => "Category '{$data['category_code']}' not found"];
                }
            }

            // Validate brand
            $brandId = null;
            if (!empty($data['brand_code'])) {
                $brandId = $this->getBrandIdByCode($data['brand_code']);
                if (!$brandId) {
                    return ['success' => false, 'error' => "Brand '{$data['brand_code']}' not found"];
                }
            }

            if ($isUpdate) {
                $this->updateProduct($existing['entity_id'], $data, $categoryId, $brandId);
            } else {
                $this->insertProduct($data, $categoryId, $brandId);
            }

            return ['success' => true, 'updated' => $isUpdate];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Helper: Get product by SKU
     */
    private function getProductBySku($sku)
    {
        return $this->qb->table('catalog_product_entity')
            ->where('sku', $sku)
            ->first();
    }

    /**
     * Helper: Get category ID
     */
    private function getCategoryIdByCode($code)
    {
        return $this->qb->table('catalog_category_entity')
            ->where('category_code', $code)
            ->value('entity_id');
    }

    /**
     * Helper: Get brand ID
     */
    private function getBrandIdByCode($code)
    {
        return $this->qb->table('catalog_brand_entity')
            ->where('brand_code', $code)
            ->value('entity_id');
    }

    /**
     * Insert new product
     */
    private function insertProduct($data, $categoryId, $brandId)
    {
        // Insert product
        $productId = $this->qb->table('catalog_product_entity')->insertGetId([
            'sku' => $data['sku'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'shipping_type' => $data['shipping_type'] ?? 'standard'
        ], 'entity_id');

        // Link category
        if ($categoryId) {
            $this->qb->table('catalog_category_product')->insert([
                'category_id' => $categoryId,
                'product_id' => $productId,
                'position' => 0
            ]);
        }

        // Link brand
        if ($brandId) {
            $this->qb->table('catalog_product_brand')->insert([
                'product_id' => $productId,
                'brand_id' => $brandId
            ]);
        }

        // Insert image
        if (!empty($data['image_path'])) {
            $this->qb->table('catalog_product_image')->insert([
                'product_id' => $productId,
                'image_path' => $data['image_path'],
                'is_primary' => 'TRUE',
                'sort_order' => 0
            ]);
        }

        // Insert features
        if (!empty($data['features'])) {
            $features = explode('|', $data['features']);
            foreach ($features as $feature) {
                $feature = trim($feature);
                if (!empty($feature)) {
                    $this->qb->table('catalog_product_attribute')->insert([
                        'product_id' => $productId,
                        'attribute_code' => 'feature',
                        'attribute_value' => $feature
                    ]);
                }
            }
        }
    }

    /**
     * Update existing product
     */
    private function updateProduct($productId, $data, $categoryId, $brandId)
    {
        // Update product
        $this->qb->table('catalog_product_entity')
            ->where('entity_id', $productId)
            ->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'shipping_type' => $data['shipping_type'] ?? 'standard',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Update category
        if ($categoryId) {
            $this->qb->table('catalog_category_product')->where('product_id', $productId)->delete();
            $this->qb->table('catalog_category_product')->insert([
                'category_id' => $categoryId,
                'product_id' => $productId,
                'position' => 0
            ]);
        }

        // Update brand
        if ($brandId) {
            $this->qb->table('catalog_product_brand')->where('product_id', $productId)->delete();
            $this->qb->table('catalog_product_brand')->insert([
                'product_id' => $productId,
                'brand_id' => $brandId
            ]);
        }

        // Update image
        if (!empty($data['image_path'])) {
            $this->qb->table('catalog_product_image')->where('product_id', $productId)->delete();
            $this->qb->table('catalog_product_image')->insert([
                'product_id' => $productId,
                'image_path' => $data['image_path'],
                'is_primary' => 'TRUE',
                'sort_order' => 0
            ]);
        }

        // Update features
        $this->qb->table('catalog_product_attribute')->where('product_id', $productId)->delete();
        if (!empty($data['features'])) {
            $features = explode('|', $data['features']);
            foreach ($features as $feature) {
                $feature = trim($feature);
                if (!empty($feature)) {
                    $this->qb->table('catalog_product_attribute')->insert([
                        'product_id' => $productId,
                        'attribute_code' => 'feature',
                        'attribute_value' => $feature
                    ]);
                }
            }
        }
    }
}
