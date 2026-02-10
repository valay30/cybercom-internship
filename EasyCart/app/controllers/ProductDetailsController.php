<?php

/**
 * ProductDetailsController
 * Handles product detail page logic
 */

require_once __DIR__ . '/../models/ProductModel.php';

class ProductDetailsController
{
    private $productModel;
    private $product;
    private $isInCart;
    private $discountApplied;
    private $finalPrice;
    private $quantity;
    private $featuredProducts;
    private $inWishlist;

    public function __construct()
    {
        $this->productModel = new ProductModel();

        // Initialize wishlist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->loadProduct();
        $this->checkCartStatus();
        $this->loadFeaturedProducts();
    }

    /**
     * Load product by URL key from URL
     */
    private function loadProduct()
    {
        // Try to get by URL key first (SEO-friendly)
        $urlKey = $_GET['url'] ?? $_GET['id'] ?? 'wireless-headphones';
        $this->product = $this->productModel->getProductByUrlKey($urlKey);

        // Fallback to SKU if URL key doesn't work (backward compatibility)
        if (!$this->product && isset($_GET['id'])) {
            $this->product = $this->productModel->getProductBySku($_GET['id']);
        }

        // Final fallback to default product
        if (!$this->product) {
            $this->product = $this->productModel->getProductBySku('p1');
        }
    }

    /**
     * Check if product is in cart and calculate discount
     */
    private function checkCartStatus()
    {
        $this->isInCart = false;
        $this->discountApplied = false;
        $this->finalPrice = $this->product['price'];
        $this->quantity = 0;

        if (isset($_SESSION['cart'][$this->product['id']])) {
            $this->isInCart = true;
            $this->quantity = $_SESSION['cart'][$this->product['id']]['qty'];

            $discount_percent = min($this->quantity, 50); // Max 50% discount
            $this->finalPrice = $this->product['price'] * (1 - ($discount_percent / 100));
            $this->discountApplied = true;
        }

        // Check wishlist status
        $this->inWishlist = in_array($this->product['id'], $_SESSION['wishlist']);
    }

    /**
     * Load random featured products
     */
    private function loadFeaturedProducts()
    {
        $this->featuredProducts = $this->productModel->getFeaturedProducts(4);
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercent()
    {
        return $this->discountApplied ? min($this->quantity, 50) : 0;
    }

    /**
     * Get data for view
     */
    public function getViewData()
    {
        return [
            'product' => $this->product,
            'isInCart' => $this->isInCart,
            'discountApplied' => $this->discountApplied,
            'finalPrice' => $this->finalPrice,
            'quantity' => $this->quantity,
            'discountPercent' => $this->getDiscountPercent(),
            'featuredProducts' => $this->featuredProducts,
            'inWishlist' => $this->inWishlist
        ];
    }
}
