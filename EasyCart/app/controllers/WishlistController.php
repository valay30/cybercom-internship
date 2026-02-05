<?php

/**
 * WishlistController
 * Handles all wishlist-related business logic
 * 
 * Updated: Now works with consolidated wishlist table
 */

require_once __DIR__ . '/../models/ProductModel.php';

class WishlistController
{
    private $productModel;
    private $wishlistModel;
    private $customerId;
    private $sessionId;

    public function __construct()
    {
        $this->productModel = new ProductModel();

        require_once __DIR__ . '/../models/WishlistModel.php';
        $this->wishlistModel = new WishlistModel();

        // Initialize session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get User/Session Info
        $this->customerId = $_SESSION['user_id'] ?? null;
        $this->sessionId = session_id();

        // Populate $_SESSION['wishlist'] for frontend compatibility
        $this->refreshSessionWishlist();
    }

    private function refreshSessionWishlist()
    {
        // Get product IDs (entity_ids) from DB
        $dbIds = $this->wishlistModel->getWishlistProductIds($this->customerId, $this->sessionId);

        // Convert to SKUs for session (since frontend uses SKU as ID)
        $skus = [];
        foreach ($dbIds as $eid) {
            $product = $this->productModel->getProductById($eid);
            if ($product) {
                $skus[] = $product['id']; // This is SKU based on ProductModel::formatProduct
            }
        }
        $_SESSION['wishlist'] = $skus;
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
        $pid = $_POST['id'] ?? ''; // This is SKU

        if ($action === 'toggle') {
            // Verify product exists and get ID
            $product = $this->productModel->getProductBySku($pid);

            if ($product) {
                $status = $this->toggleWishlist($product['entity_id']);

                // Update session
                $this->refreshSessionWishlist();

                // Handle AJAX requests
                if (isset($_POST['ajax'])) {
                    echo json_encode([
                        'success' => true,
                        'status' => $status,
                        'count' => count($_SESSION['wishlist'])
                    ]);
                    exit;
                }
            }
        }

        // Fallback for non-AJAX
        header('Location: wishlist');
        exit;
    }

    /**
     * Toggle product in wishlist (add or remove)
     */
    private function toggleWishlist($entityId)
    {
        // Check if exists in DB
        $isInWishlist = $this->wishlistModel->isInWishlist($this->customerId, $entityId, $this->sessionId);

        if ($isInWishlist) {
            // Remove
            $this->wishlistModel->removeItem($this->customerId, $entityId, $this->sessionId);
            return 'removed';
        } else {
            // Add
            $this->wishlistModel->addItem($this->customerId, $entityId, $this->sessionId);
            return 'added';
        }
    }

    /**
     * Get wishlist products data
     */
    public function getWishlistProducts()
    {
        // Use the new method that returns full product details
        $items = $this->wishlistModel->getWishlistItems($this->customerId, $this->sessionId);

        $wishlistProducts = [];
        foreach ($items as $item) {
            // Format product data
            $product = [
                'id' => $item['sku'],
                'entity_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'description' => $item['description'],
                'image' => $item['image_path'] ?? 'images/placeholder.png',
                'added_at' => $item['added_at']
            ];

            // Key by SKU to match previous behavior
            $wishlistProducts[$item['sku']] = $product;
        }

        return $wishlistProducts;
    }

    /**
     * Check if wishlist is empty
     */
    public function isEmpty()
    {
        $count = $this->wishlistModel->getWishlistCount($this->customerId, $this->sessionId);
        return $count === 0;
    }

    /**
     * Get wishlist count
     */
    public function getCount()
    {
        return $this->wishlistModel->getWishlistCount($this->customerId, $this->sessionId);
    }

    /**
     * Clear entire wishlist
     */
    public function clearWishlist()
    {
        $result = $this->wishlistModel->clearWishlist($this->customerId, $this->sessionId);
        $this->refreshSessionWishlist();
        return $result;
    }
}
