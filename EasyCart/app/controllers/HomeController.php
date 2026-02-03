<?php

/**
 * HomeController
 * Handles home page logic
 */

class HomeController
{
    private $products;
    private $categories;
    private $brands;
    private $featuredProducts;

    public function __construct($products, $categories, $brands)
    {
        $this->products = $products;
        $this->categories = $categories;
        $this->brands = $brands;

        // Initialize wishlist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->loadFeaturedProducts();
    }

    /**
     * Load random featured products
     */
    private function loadFeaturedProducts()
    {
        $randomProducts = $this->products;
        shuffle($randomProducts);
        $this->featuredProducts = array_slice($randomProducts, 0, 4);
    }

    /**
     * Get data for view
     */
    public function getViewData()
    {
        return [
            'featuredProducts' => $this->featuredProducts,
            'categories' => $this->categories,
            'brands' => $this->brands,
            'wishlistIds' => $_SESSION['wishlist']
        ];
    }
}
