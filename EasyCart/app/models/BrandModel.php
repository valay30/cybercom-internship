<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Brand Model
 * Handles all brand-related database operations
 */
class BrandModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get all brands
     */
    public function getAllBrands()
    {
        $query = "SELECT 
                    b.entity_id,
                    b.brand_code,
                    b.name,
                    b.is_active,
                    ba.attribute_value as icon,
                    COUNT(DISTINCT pb.product_id) as product_count
                  FROM catalog_brand_entity b
                  LEFT JOIN catalog_brand_attribute ba ON b.entity_id = ba.brand_id AND ba.attribute_code = 'icon'
                  LEFT JOIN catalog_product_brand pb ON b.entity_id = pb.brand_id
                  WHERE b.is_active = TRUE
                  GROUP BY b.entity_id, b.brand_code, b.name, b.is_active, ba.attribute_value
                  ORDER BY b.name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $this->formatBrands($stmt->fetchAll());
    }

    /**
     * Get brand by code
     */
    public function getBrandByCode($code)
    {
        $query = "SELECT 
                    b.entity_id,
                    b.brand_code,
                    b.name,
                    b.is_active,
                    ba.attribute_value as icon
                  FROM catalog_brand_entity b
                  LEFT JOIN catalog_brand_attribute ba ON b.entity_id = ba.brand_id AND ba.attribute_code = 'icon'
                  WHERE b.brand_code = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        $brand = $stmt->fetch();

        return $brand ? $this->formatBrand($brand) : null;
    }

    /**
     * Format single brand
     */
    private function formatBrand($brand)
    {
        return [
            'id' => $brand['brand_code'],
            'entity_id' => $brand['entity_id'],
            'name' => $brand['name'],
            'icon' => $brand['icon'] ?? 'fa-tag',
            'product_count' => $brand['product_count'] ?? 0
        ];
    }

    /**
     * Format multiple brands
     */
    private function formatBrands($brands)
    {
        return array_map([$this, 'formatBrand'], $brands);
    }
}
