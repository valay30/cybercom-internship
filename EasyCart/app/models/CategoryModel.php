<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Category Model
 * Handles all category-related database operations
 */
class CategoryModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $query = "SELECT 
                    c.entity_id,
                    c.category_code,
                    c.name,
                    c.is_active,
                    ca.attribute_value as icon,
                    COUNT(DISTINCT cp.product_id) as product_count
                  FROM catalog_category_entity c
                  LEFT JOIN catalog_category_attribute ca ON c.entity_id = ca.category_id AND ca.attribute_code = 'icon'
                  LEFT JOIN catalog_category_product cp ON c.entity_id = cp.category_id
                  WHERE c.is_active = TRUE
                  GROUP BY c.entity_id, c.category_code, c.name, c.is_active, ca.attribute_value
                  ORDER BY c.name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Get category by code
     */
    public function getCategoryByCode($code)
    {
        $query = "SELECT 
                    c.entity_id,
                    c.category_code,
                    c.name,
                    c.is_active,
                    ca.attribute_value as icon
                  FROM catalog_category_entity c
                  LEFT JOIN catalog_category_attribute ca ON c.entity_id = ca.category_id AND ca.attribute_code = 'icon'
                  WHERE c.category_code = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        $category = $stmt->fetch();

        return $category ? $this->formatCategory($category) : null;
    }

    /**
     * Format single category
     */
    private function formatCategory($category)
    {
        return [
            'id' => $category['category_code'],
            'entity_id' => $category['entity_id'],
            'name' => $category['name'],
            'icon' => $category['icon'] ?? 'fa-tag',
            'product_count' => $category['product_count'] ?? 0
        ];
    }

    /**
     * Format multiple categories
     */
    private function formatCategories($categories)
    {
        return array_map([$this, 'formatCategory'], $categories);
    }
}
