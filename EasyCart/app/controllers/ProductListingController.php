<?php

/**
 * ProductListingController
 * Handles product filtering, sorting, searching, and pagination
 */

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';

class ProductListingController
{
    private $products;
    private $categories;
    private $brands;
    private $filteredProducts;
    private $paginatedProducts;
    private $searchQuery;
    private $selectedCategories;
    private $selectedBrands;
    private $minPrice;
    private $maxPrice;
    private $sortOption;
    private $currentPage;
    private $totalPages;
    private $totalItems;
    private $itemsPerPage = 9;
    private $offset;

    public function __construct()
    {
        // Initialize models
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        $brandModel = new BrandModel();

        // Fetch data from database
        $this->products = $productModel->getAllProducts();
        $this->categories = $categoryModel->getAllCategories();
        $this->brands = $brandModel->getAllBrands();

        // Initialize wishlist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->parseFilters();
        $this->applyFilters();
        $this->applySorting();
        $this->applyPagination();
    }

    /**
     * Parse filter parameters from GET request
     */
    private function parseFilters()
    {
        $this->searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Handle category parameter
        $this->selectedCategories = isset($_GET['category']) ? $_GET['category'] : [];
        if (!is_array($this->selectedCategories)) {
            $this->selectedCategories = [$this->selectedCategories];
        }

        // Handle brand parameter
        $this->selectedBrands = isset($_GET['brand']) ? $_GET['brand'] : [];
        if (!is_array($this->selectedBrands)) {
            $this->selectedBrands = [$this->selectedBrands];
        }

        $this->minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int) $_GET['min_price'] : 0;
        $this->maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int) $_GET['max_price'] : 15000;
        $this->sortOption = isset($_GET['sort']) ? $_GET['sort'] : '';
    }

    /**
     * Apply filters to products
     */
    private function applyFilters()
    {
        $this->filteredProducts = array_filter($this->products, function ($product) {
            // Search filter
            if (
                $this->searchQuery &&
                stripos($product['name'], $this->searchQuery) === false &&
                stripos($product['description'], $this->searchQuery) === false
            ) {
                return false;
            }

            // Category filter
            if (!empty($this->selectedCategories) && !in_array($product['category'], $this->selectedCategories)) {
                return false;
            }

            // Brand filter
            if (!empty($this->selectedBrands) && !in_array($product['brand'], $this->selectedBrands)) {
                return false;
            }

            // Price filter
            if ($product['price'] < $this->minPrice || $product['price'] > $this->maxPrice) {
                return false;
            }

            return true;
        });
    }

    /**
     * Apply sorting to filtered products
     */
    private function applySorting()
    {
        if ($this->sortOption === 'price_asc') {
            usort($this->filteredProducts, function ($a, $b) {
                return $a['price'] - $b['price'];
            });
        } elseif ($this->sortOption === 'price_desc') {
            usort($this->filteredProducts, function ($a, $b) {
                return $b['price'] - $a['price'];
            });
        }
    }

    /**
     * Apply pagination to filtered products
     */
    private function applyPagination()
    {
        $this->totalItems = count($this->filteredProducts);
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        $this->currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        }

        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
        $this->paginatedProducts = array_slice($this->filteredProducts, $this->offset, $this->itemsPerPage);
    }

    /**
     * Handle AJAX request for product listing
     */
    public function handleAjax()
    {
        if (!isset($_GET['ajax']) || $_GET['ajax'] !== 'true') {
            return;
        }

        $response = [
            'success' => true,
            'products' => [],
            'total_items' => $this->totalItems,
            'total_pages' => $this->totalPages,
            'current_page' => $this->currentPage,
            'showing_from' => $this->offset + 1,
            'showing_to' => min($this->offset + count($this->paginatedProducts), $this->totalItems)
        ];

        // Format products for JSON response
        foreach ($this->paginatedProducts as $product) {
            $response['products'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'shipping_type' => $product['shipping_type'],
                'in_wishlist' => in_array($product['id'], $_SESSION['wishlist'])
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * Get data for view
     */
    public function getViewData()
    {
        return [
            'categories' => $this->categories,
            'brands' => $this->brands,
            'paginatedProducts' => $this->paginatedProducts,
            'searchQuery' => $this->searchQuery,
            'selectedCategories' => $this->selectedCategories,
            'selectedBrands' => $this->selectedBrands,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'sortOption' => $this->sortOption,
            'currentPage' => $this->currentPage,
            'totalPages' => $this->totalPages,
            'totalItems' => $this->totalItems,
            'offset' => $this->offset,
            'wishlistIds' => $_SESSION['wishlist']
        ];
    }
}
