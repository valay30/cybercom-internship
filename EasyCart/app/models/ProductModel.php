<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Product Model
 * Handles all product-related database operations
 */
class ProductModel
{
    private $qb;

    public function __construct($pdo = null)
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder($pdo);
    }

    /**
     * Helper to build base product query with joins
     */
    private function getBaseQuery()
    {
        return $this->qb->table('catalog_product_entity p')
            ->select([
                'DISTINCT p.entity_id',
                'p.sku',
                'p.name',
                'p.description',
                'p.price',
                'p.shipping_type',
                'p.url_key',
                'pi.image_path',
                'c.name as category_name',
                'c.category_code',
                'b.name as brand_name',
                'b.brand_code'
            ])
            ->leftJoin('catalog_product_image pi', "p.entity_id = pi.product_id AND pi.is_primary = TRUE")
            ->leftJoin('catalog_category_product cp', 'p.entity_id = cp.product_id')
            ->leftJoin('catalog_category_entity c', 'cp.category_id = c.entity_id')
            ->leftJoin('catalog_product_brand pb', 'p.entity_id = pb.product_id')
            ->leftJoin('catalog_brand_entity b', 'pb.brand_id = b.entity_id');
    }

    /**
     * Get all products with images, categories, and brands
     */
    public function getAllProducts($limit = null, $offset = 0)
    {
        $query = $this->getBaseQuery()->orderBy('p.entity_id');

        if ($limit) {
            $query->limit((int)$limit);
            if ($offset) {
                $query->offset((int)$offset);
            }
        }

        return $this->formatProducts($query->get());
    }

    /**
     * Get product by ID with all details
     */
    public function getProductById($id)
    {
        $product = $this->getBaseQuery()
            ->where('p.entity_id', $id)
            ->first();

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
        $result = $this->qb->table('catalog_product_entity')
            ->where('sku', $sku)
            ->value('entity_id');

        if ($result) {
            return $this->getProductById($result);
        }

        return null;
    }

    /**
     * Get product by URL key (SEO-friendly URL)
     */
    public function getProductByUrlKey($urlKey)
    {
        $result = $this->qb->table('catalog_product_entity')
            ->where('url_key', $urlKey)
            ->value('entity_id');

        if ($result) {
            return $this->getProductById($result);
        }

        return null;
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory($categoryCode, $limit = null)
    {
        $query = $this->getBaseQuery()
            ->where('c.category_code', $categoryCode)
            ->orderBy('p.entity_id');

        if ($limit) {
            $query->limit((int)$limit);
        }

        return $this->formatProducts($query->get());
    }

    /**
     * Get products by brand
     */
    public function getProductsByBrand($brandCode, $limit = null)
    {
        $query = $this->getBaseQuery()
            ->where('b.brand_code', $brandCode)
            ->orderBy('p.entity_id');

        if ($limit) {
            $query->limit((int)$limit);
        }

        return $this->formatProducts($query->get());
    }

    /**
     * Search products by name or description
     */
    public function searchProducts($searchTerm)
    {
        $searchPattern = "%$searchTerm%";

        // Note: Using ILIKE for case-insensitive search if PostgreSQL, or LIKE for MySQL
        // Assuming PostgreSQL as per context (insertGetId usage)
        $query = $this->getBaseQuery()
            ->where('p.name', 'ILIKE', $searchPattern)
            ->orWhere('p.description', 'ILIKE', $searchPattern)
            ->orderBy('p.entity_id');

        return $this->formatProducts($query->get());
    }

    /**
     * Get product images
     */
    private function getProductImages($productId)
    {
        return $this->qb->table('catalog_product_image')
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->pluck('image_path');
    }

    /**
     * Get product features/attributes
     */
    private function getProductFeatures($productId)
    {
        return $this->qb->table('catalog_product_attribute')
            ->where('product_id', $productId)
            ->where('attribute_code', 'feature')
            ->orderBy('attribute_id')
            ->pluck('attribute_value');
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
            'url_key' => $product['url_key'] ?? null,
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
        return $this->qb->table('catalog_product_entity')->count();
    }

    /**
     * Get featured products (first 8 products)
     */
    public function getFeaturedProducts($limit = 8)
    {
        return $this->getAllProducts($limit);
    }
}
