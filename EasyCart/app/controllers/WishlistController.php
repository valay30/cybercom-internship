<?php

/**
 * WishlistController
 * Handles all wishlist-related business logic
 */

require_once __DIR__ . '/../models/ProductModel.php';

class WishlistController
{
    private $productModel;
    private $wishlistModel;
    private $wishlistId;

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
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();

        // Get or Create Wishlist ID from DB
        $this->wishlistId = $this->wishlistModel->getWishlistId($userId, $sessionId);

        // Populate $_SESSION['wishlist'] for legacy compatibility (optional, but good for frontend checks that might rely on it)
        $this->refreshSessionWishlist();
    }

    private function refreshSessionWishlist()
    {
        // Get product IDs (entity_ids) from DB
        $dbIds = $this->wishlistModel->getWishlistProductIds($this->wishlistId);

        // Convert to SKUs for session (since frontend uses SKU as ID apparently)
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
        header('Location: wishlist.php');
        exit;
    }

    /**
     * Toggle product in wishlist (add or remove)
     */
    private function toggleWishlist($entityId)
    {
        // Check if exists in DB
        // For efficiency, we can check session, but DB is safer source of truth.
        // Or simply try to remove. If row deleted, it was there.
        // But we need to toggle.

        // Let's check current state
        $existingIds = $this->wishlistModel->getWishlistProductIds($this->wishlistId);

        if (in_array($entityId, $existingIds)) {
            // Remove
            $this->wishlistModel->removeItem($this->wishlistId, $entityId);
            return 'removed';
        } else {
            // Add
            $this->wishlistModel->addItem($this->wishlistId, $entityId);
            return 'added';
        }
    }

    /**
     * Get wishlist products data
     */
    public function getWishlistProducts()
    {
        $wishlistProducts = [];
        $entityIds = $this->wishlistModel->getWishlistProductIds($this->wishlistId);

        foreach ($entityIds as $eid) {
            $product = $this->productModel->getProductById($eid);
            if ($product) {
                // Key by SKU ($product['id']) to match previous behavior
                $wishlistProducts[$product['id']] = $product;
            }
        }

        return $wishlistProducts;
    }

    /**
     * Check if wishlist is empty
     */
    public function isEmpty()
    {
        $ids = $this->wishlistModel->getWishlistProductIds($this->wishlistId);
        return empty($ids);
    }
}
