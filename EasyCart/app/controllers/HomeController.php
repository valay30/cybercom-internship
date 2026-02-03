<?php

/**
 * HomeController
 * Handles home page logic
 */

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';

class HomeController
{
    private $productModel;
    private $categoryModel;
    private $brandModel;
    private $categories;
    private $brands;
    private $featuredProducts;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->brandModel = new BrandModel();

        // Initialize wishlist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->loadData();
    }

    private function loadData()
    {
        $this->categories = $this->categoryModel->getAllCategories();
        $this->brands = $this->brandModel->getAllBrands();
        $this->featuredProducts = $this->productModel->getFeaturedProducts(4);
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
