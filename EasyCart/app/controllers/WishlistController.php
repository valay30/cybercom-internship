<?php

/**
 * WishlistController
 * Handles all wishlist-related business logic
 */

require_once __DIR__ . '/../models/ProductModel.php';

class WishlistController
{
    private $productModel;
    private $wishlist;

    public function __construct()
    {
        $this->productModel = new ProductModel();

        // Initialize wishlist session
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        $this->wishlist = &$_SESSION['wishlist'];
    }

    /**
     * Handle POST actions (toggle wishlist item)
     */
    public function handleAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $_POST['action'] ?? '';
        $pid = $_POST['id'] ?? '';

        if ($action === 'toggle') {
            // Verify product exists
            $product = $this->productModel->getProductBySku($pid);

            if ($product) {
                $status = $this->toggleWishlist($pid);

                // Handle AJAX requests
                if (isset($_POST['ajax'])) {
                    echo json_encode([
                        'success' => true,
                        'status' => $status,
                        'count' => count($this->wishlist)
                    ]);
                    exit;
                }
            }
        }

        // Fallback for non-AJAX
        header('Location: wishlist.php');
        exit;
    }

    /**
     * Toggle product in wishlist (add or remove)
     */
    private function toggleWishlist($pid)
    {
        if (in_array($pid, $this->wishlist)) {
            // Remove
            $key = array_search($pid, $this->wishlist);
            unset($this->wishlist[$key]);
            $status = 'removed';
        } else {
            // Add
            $this->wishlist[] = $pid;
            $status = 'added';
        }

        // Re-index array
        $this->wishlist = array_values($this->wishlist);
        $_SESSION['wishlist'] = $this->wishlist;

        return $status;
    }

    /**
     * Get wishlist products data
     */
    public function getWishlistProducts()
    {
        $wishlistProducts = [];

        foreach ($this->wishlist as $pid) {
            $product = $this->productModel->getProductBySku($pid);
            if ($product) {
                $wishlistProducts[$pid] = $product;
            }
        }

        return $wishlistProducts;
    }

    /**
     * Check if wishlist is empty
     */
    public function isEmpty()
    {
        return empty($this->wishlist);
    }
}
