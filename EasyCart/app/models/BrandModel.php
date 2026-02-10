<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Brand Model
 * Handles all brand-related database operations
 */
class BrandModel
{
    private $qb;

    public function __construct()
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder();
    }

    /**
     * Get all brands
     */
    public function getAllBrands()
    {
        $brands = $this->qb->table('catalog_brand_entity b')
            ->select([
                'b.entity_id',
                'b.brand_code',
                'b.name',
                'b.is_active',
                'ba.attribute_value as icon',
                'COUNT(DISTINCT pb.product_id) as product_count'
            ])
            ->leftJoin('catalog_brand_attribute ba', "b.entity_id = ba.brand_id AND ba.attribute_code = 'icon'")
            ->leftJoin('catalog_product_brand pb', 'b.entity_id = pb.brand_id')
            ->where('b.is_active', true)
            ->groupBy(['b.entity_id', 'b.brand_code', 'b.name', 'b.is_active', 'ba.attribute_value'])
            ->orderBy('b.name')
            ->get();

        return $this->formatBrands($brands);
    }

    /**
     * Get brand by code
     */
    public function getBrandByCode($code)
    {
        $brand = $this->qb->table('catalog_brand_entity b')
            ->select([
                'b.entity_id',
                'b.brand_code',
                'b.name',
                'b.is_active',
                'ba.attribute_value as icon'
            ])
            ->leftJoin('catalog_brand_attribute ba', "b.entity_id = ba.brand_id AND ba.attribute_code = 'icon'")
            ->where('b.brand_code', $code)
            ->first();

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
