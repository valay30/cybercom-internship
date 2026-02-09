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
    private $conn;

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

        $db = new Database();
        $this->conn = $db->getConnection();
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
        $this->conn->beginTransaction();

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

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
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
        $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$sku]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get category ID
     */
    private function getCategoryIdByCode($code)
    {
        $sql = "SELECT entity_id FROM catalog_category_entity WHERE category_code = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['entity_id'] : null;
    }

    /**
     * Helper: Get brand ID
     */
    private function getBrandIdByCode($code)
    {
        $sql = "SELECT entity_id FROM catalog_brand_entity WHERE brand_code = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['entity_id'] : null;
    }

    /**
     * Insert new product
     */
    private function insertProduct($data, $categoryId, $brandId)
    {
        // Insert product
        $sql = "INSERT INTO catalog_product_entity (sku, name, description, price, shipping_type) 
                VALUES (?, ?, ?, ?, ?) RETURNING entity_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['sku'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['shipping_type'] ?? 'standard'
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $productId = $result['entity_id'];

        // Link category
        if ($categoryId) {
            $sql = "INSERT INTO catalog_category_product (category_id, product_id, position) VALUES (?, ?, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoryId, $productId]);
        }

        // Link brand
        if ($brandId) {
            $sql = "INSERT INTO catalog_product_brand (product_id, brand_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId, $brandId]);
        }

        // Insert image
        if (!empty($data['image_path'])) {
            $sql = "INSERT INTO catalog_product_image (product_id, image_path, is_primary, sort_order) 
                    VALUES (?, ?, true, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId, $data['image_path']]);
        }

        // Insert features
        if (!empty($data['features'])) {
            $features = explode('|', $data['features']);
            foreach ($features as $feature) {
                $feature = trim($feature);
                if (!empty($feature)) {
                    $sql = "INSERT INTO catalog_product_attribute (product_id, attribute_code, attribute_value) 
                            VALUES (?, 'feature', ?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$productId, $feature]);
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
        $sql = "UPDATE catalog_product_entity 
                SET name = ?, description = ?, price = ?, shipping_type = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE entity_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['shipping_type'] ?? 'standard',
            $productId
        ]);

        // Update category
        if ($categoryId) {
            $sql = "DELETE FROM catalog_category_product WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId]);

            $sql = "INSERT INTO catalog_category_product (category_id, product_id, position) VALUES (?, ?, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoryId, $productId]);
        }

        // Update brand
        if ($brandId) {
            $sql = "DELETE FROM catalog_product_brand WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId]);

            $sql = "INSERT INTO catalog_product_brand (product_id, brand_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId, $brandId]);
        }

        // Update image
        if (!empty($data['image_path'])) {
            $sql = "DELETE FROM catalog_product_image WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId]);

            $sql = "INSERT INTO catalog_product_image (product_id, image_path, is_primary, sort_order) 
                    VALUES (?, ?, true, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$productId, $data['image_path']]);
        }

        // Update features
        $sql = "DELETE FROM catalog_product_attribute WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);

        if (!empty($data['features'])) {
            $features = explode('|', $data['features']);
            foreach ($features as $feature) {
                $feature = trim($feature);
                if (!empty($feature)) {
                    $sql = "INSERT INTO catalog_product_attribute (product_id, attribute_code, attribute_value) 
                            VALUES (?, 'feature', ?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$productId, $feature]);
                }
            }
        }
    }
}
