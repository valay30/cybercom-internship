<?php

/**
 * ProductDetailsController
 * Handles product detail page logic
 */

class ProductDetailsController
{
    private $products;
    private $product;
    private $productId;
    private $isInCart;
    private $discountApplied;
    private $finalPrice;
    private $quantity;
    private $featuredProducts;
    private $inWishlist;

    public function __construct($products)
    {
        $this->products = $products;

        // Initialize wishlist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->loadProduct();
        $this->checkCartStatus();
        $this->loadFeaturedProducts();
    }

    /**
     * Load product by ID from URL
     */
    private function loadProduct()
    {
        $this->productId = $_GET['id'] ?? 'p1';
        $this->product = $this->products[$this->productId] ?? $this->products['p1'];
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
        $randomProducts = $this->products;
        shuffle($randomProducts);
        $this->featuredProducts = array_slice($randomProducts, 0, 4);
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
