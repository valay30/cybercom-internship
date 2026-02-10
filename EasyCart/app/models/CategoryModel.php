<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Category Model
 * Handles all category-related database operations
 */
class CategoryModel
{
    private $qb;

    public function __construct()
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder();
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $categories = $this->qb->table('catalog_category_entity c')
            ->select([
                'c.entity_id',
                'c.category_code',
                'c.name',
                'c.is_active',
                'ca.attribute_value as icon',
                'COUNT(DISTINCT cp.product_id) as product_count'
            ])
            ->leftJoin('catalog_category_attribute ca', "c.entity_id = ca.category_id AND ca.attribute_code = 'icon'")
            ->leftJoin('catalog_category_product cp', 'c.entity_id = cp.category_id')
            ->where('c.is_active', true)
            ->groupBy(['c.entity_id', 'c.category_code', 'c.name', 'c.is_active', 'ca.attribute_value'])
            ->orderBy('c.name')
            ->get();

        return $this->formatCategories($categories);
    }

    /**
     * Get category by code
     */
    public function getCategoryByCode($code)
    {
        $category = $this->qb->table('catalog_category_entity c')
            ->select([
                'c.entity_id',
                'c.category_code',
                'c.name',
                'c.is_active',
                'ca.attribute_value as icon'
            ])
            ->leftJoin('catalog_category_attribute ca', "c.entity_id = ca.category_id AND ca.attribute_code = 'icon'")
            ->where('c.category_code', $code)
            ->first();

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
