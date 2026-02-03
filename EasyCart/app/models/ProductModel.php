<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Product Model
 * Handles all product-related database operations
 */
class ProductModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get all products with images, categories, and brands
     */
    public function getAllProducts($limit = null, $offset = 0)
    {
        $query = "SELECT DISTINCT
                    p.entity_id,
                    p.sku,
                    p.name,
                    p.description,
                    p.price,
                    p.shipping_type,
                    pi.image_path,
                    c.name as category_name,
                    c.category_code,
                    b.name as brand_name,
                    b.brand_code
                  FROM catalog_product_entity p
                  LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                  LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                  LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                  LEFT JOIN catalog_product_brand pb ON p.entity_id = pb.product_id
                  LEFT JOIN catalog_brand_entity b ON pb.brand_id = b.entity_id
                  ORDER BY p.entity_id";

        if ($limit) {
            $query .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get product by ID with all details
     */
    public function getProductById($id)
    {
        $query = "SELECT 
                    p.entity_id,
                    p.sku,
                    p.name,
                    p.description,
                    p.price,
                    p.shipping_type,
                    c.name as category_name,
                    c.category_code,
                    b.name as brand_name,
                    b.brand_code
                  FROM catalog_product_entity p
                  LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                  LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                  LEFT JOIN catalog_product_brand pb ON p.entity_id = pb.product_id
                  LEFT JOIN catalog_brand_entity b ON pb.brand_id = b.entity_id
                  WHERE p.entity_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            // Get all images
            $product['images'] = $this->getProductImages($id);

            // Get features
            $product['features'] = $this->getProductFeatures($id);

            return $this->formatProduct($product);
        }

        return null;
    }

    /**
     * Get product by SKU
     */
    public function getProductBySku($sku)
    {
        $query = "SELECT entity_id FROM catalog_product_entity WHERE sku = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sku]);
        $result = $stmt->fetch();

        if ($result) {
            return $this->getProductById($result['entity_id']);
        }

        return null;
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory($categoryCode, $limit = null)
    {
        $query = "SELECT DISTINCT
                    p.entity_id,
                    p.sku,
                    p.name,
                    p.description,
                    p.price,
                    p.shipping_type,
                    pi.image_path,
                    c.name as category_name,
                    c.category_code,
                    b.name as brand_name,
                    b.brand_code
                  FROM catalog_product_entity p
                  LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                  LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                  LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                  LEFT JOIN catalog_product_brand pb ON p.entity_id = pb.product_id
                  LEFT JOIN catalog_brand_entity b ON pb.brand_id = b.entity_id
                  WHERE c.category_code = ?
                  ORDER BY p.entity_id";

        if ($limit) {
            $query .= " LIMIT $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$categoryCode]);

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get products by brand
     */
    public function getProductsByBrand($brandCode, $limit = null)
    {
        $query = "SELECT DISTINCT
                    p.entity_id,
                    p.sku,
                    p.name,
                    p.description,
                    p.price,
                    p.shipping_type,
                    pi.image_path,
                    c.name as category_name,
                    c.category_code,
                    b.name as brand_name,
                    b.brand_code
                  FROM catalog_product_entity p
                  LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                  LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                  LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                  LEFT JOIN catalog_product_brand pb ON p.entity_id = pb.product_id
                  LEFT JOIN catalog_brand_entity b ON pb.brand_id = b.entity_id
                  WHERE b.brand_code = ?
                  ORDER BY p.entity_id";

        if ($limit) {
            $query .= " LIMIT $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$brandCode]);

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Search products by name or description
     */
    public function searchProducts($searchTerm)
    {
        $query = "SELECT DISTINCT
                    p.entity_id,
                    p.sku,
                    p.name,
                    p.description,
                    p.price,
                    p.shipping_type,
                    pi.image_path,
                    c.name as category_name,
                    c.category_code,
                    b.name as brand_name,
                    b.brand_code
                  FROM catalog_product_entity p
                  LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                  LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                  LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                  LEFT JOIN catalog_product_brand pb ON p.entity_id = pb.product_id
                  LEFT JOIN catalog_brand_entity b ON pb.brand_id = b.entity_id
                  WHERE p.name ILIKE ? OR p.description ILIKE ?
                  ORDER BY p.entity_id";

        $searchPattern = "%$searchTerm%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$searchPattern, $searchPattern]);

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get product images
     */
    private function getProductImages($productId)
    {
        $query = "SELECT image_path, is_primary, sort_order
                  FROM catalog_product_image
                  WHERE product_id = ?
                  ORDER BY sort_order";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$productId]);

        return array_column($stmt->fetchAll(), 'image_path');
    }

    /**
     * Get product features/attributes
     */
    private function getProductFeatures($productId)
    {
        $query = "SELECT attribute_value
                  FROM catalog_product_attribute
                  WHERE product_id = ? AND attribute_code = 'feature'
                  ORDER BY attribute_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$productId]);

        return array_column($stmt->fetchAll(), 'attribute_value');
    }

    /**
     * Format single product for compatibility with existing code
     */
    private function formatProduct($product)
    {
        if (!$product) return null;

        return [
            'id' => $product['sku'],
            'entity_id' => $product['entity_id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'shipping_type' => $product['shipping_type'],
            'image' => $product['images'][0] ?? ($product['image_path'] ?? 'images/placeholder.png'),
            'images' => $product['images'] ?? [$product['image_path'] ?? 'images/placeholder.png'],
            'category' => $product['category_code'] ?? null,
            'category_name' => $product['category_name'] ?? null,
            'brand' => $product['brand_code'] ?? null,
            'brand_name' => $product['brand_name'] ?? null,
            'features' => $product['features'] ?? []
        ];
    }

    /**
     * Format multiple products
     */
    private function formatProducts($products)
    {
        $formatted = [];

        foreach ($products as $product) {
            $sku = $product['sku'];

            if (!isset($formatted[$sku])) {
                $formatted[$sku] = $this->formatProduct($product);
            }
        }

        return array_values($formatted);
    }

    /**
     * Get total product count
     */
    public function getTotalCount()
    {
        $query = "SELECT COUNT(*) as count FROM catalog_product_entity";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        return (int)$result['count'];
    }

    /**
     * Get featured products (first 8 products)
     */
    public function getFeaturedProducts($limit = 8)
    {
        return $this->getAllProducts($limit);
    }
}
