<?php

require_once __DIR__ . '/../../config/database.php';

class WishlistModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get or Create Wishlist ID for a customer or session
     * @param int|null $customerId
     * @param string|null $sessionId
     * @return int Wishlist ID
     */
    public function getWishlistId($customerId = null, $sessionId = null)
    {
        if ($customerId) {
            // Check if wishlist exists for customer
            $sql = "SELECT wishlist_id FROM public.customer_wishlist WHERE customer_id = :cid";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':cid' => $customerId]);
        } else {
            // Check if wishlist exists for session
            $sql = "SELECT wishlist_id FROM public.customer_wishlist WHERE session_id = :sid AND customer_id IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':sid' => $sessionId]);
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['wishlist_id'];
        }

        // Create new wishlist
        $sql = "INSERT INTO public.customer_wishlist (customer_id, session_id, created_at) VALUES (:cid, :sid, NOW()) RETURNING wishlist_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cid' => $customerId, ':sid' => $sessionId]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['wishlist_id'];
    }

    /**
     * Add item to wishlist
     * @param int $wishlistId
     * @param int $productId
     * @return bool
     */
    public function addItem($wishlistId, $productId)
    {
        // Check if already exists
        $sql = "SELECT 1 FROM public.customer_wishlist_product WHERE wishlist_id = :wid AND product_id = :pid";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':wid' => $wishlistId, ':pid' => $productId]);

        if ($stmt->fetch()) {
            return true; // Already in wishlist
        }

        // Add
        $sql = "INSERT INTO public.customer_wishlist_product (wishlist_id, product_id, added_at) VALUES (:wid, :pid, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':wid' => $wishlistId, ':pid' => $productId]);
    }

    /**
     * Remove item from wishlist
     * @param int $wishlistId
     * @param int $productId
     * @return bool
     */
    public function removeItem($wishlistId, $productId)
    {
        $sql = "DELETE FROM public.customer_wishlist_product WHERE wishlist_id = :wid AND product_id = :pid";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':wid' => $wishlistId, ':pid' => $productId]);
    }

    /**
     * Get all product IDs in wishlist
     * @param int $wishlistId
     * @return array
     */
    public function getWishlistProductIds($wishlistId)
    {
        $sql = "SELECT product_id FROM public.customer_wishlist_product WHERE wishlist_id = :wid";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':wid' => $wishlistId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
