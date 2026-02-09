<?php
require_once __DIR__ . '/../../config/database.php';

class CartModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Add or Update item in cart
     * @param int|null $userId - Customer ID (null for guest)
     * @param int $productId - Product entity ID
     * @param int $qty - Quantity to add
     * @param string|null $sessionId - Session ID for guest users
     */
    public function addItem($userId, $productId, $qty, $sessionId = null)
    {
        // For guest users, use session_id instead of customer_id
        if ($userId === null && $sessionId !== null) {
            // Check if item exists for guest
            $sql = "SELECT item_id, qty FROM sales_cart 
                    WHERE session_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$sessionId, $productId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update quantity
                $newQty = $existing['qty'] + $qty;
                $updateSql = "UPDATE sales_cart SET qty = ?, updated_at = NOW() WHERE item_id = ?";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->execute([$newQty, $existing['item_id']]);
            } else {
                // Insert new item for guest
                $insertSql = "INSERT INTO sales_cart (session_id, product_id, qty, is_active) VALUES (?, ?, ?, TRUE)";
                $insertStmt = $this->conn->prepare($insertSql);
                $insertStmt->execute([$sessionId, $productId, $qty]);
            }
        } else {
            // Logged in user
            // Check if item exists and is active
            $sql = "SELECT item_id, qty FROM sales_cart 
                    WHERE customer_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $productId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update quantity
                $newQty = $existing['qty'] + $qty;
                $updateSql = "UPDATE sales_cart SET qty = ?, updated_at = NOW() WHERE item_id = ?";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->execute([$newQty, $existing['item_id']]);
            } else {
                // Insert new item
                $insertSql = "INSERT INTO sales_cart (customer_id, product_id, qty, is_active) VALUES (?, ?, ?, TRUE)";
                $insertStmt = $this->conn->prepare($insertSql);
                $insertStmt->execute([$userId, $productId, $qty]);
            }
        }
    }

    /**
     * Update exact quantity
     * @param int|null $userId - Customer ID (null for guest)
     * @param int $productId - Product entity ID
     * @param int $qty - New quantity
     * @param string|null $sessionId - Session ID for guest users
     */
    public function updateQty($userId, $productId, $qty, $sessionId = null)
    {
        if ($qty <= 0) {
            $this->removeItem($userId, $productId, $sessionId);
            return;
        }

        if ($userId === null && $sessionId !== null) {
            // Guest user
            $sql = "UPDATE sales_cart SET qty = ?, updated_at = NOW() 
                    WHERE session_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$qty, $sessionId, $productId]);
        } else {
            // Logged in user
            $sql = "UPDATE sales_cart SET qty = ?, updated_at = NOW() 
                    WHERE customer_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$qty, $userId, $productId]);
        }
    }

    /**
     * Remove item
     * @param int|null $userId - Customer ID (null for guest)
     * @param int $productId - Product entity ID
     * @param string|null $sessionId - Session ID for guest users
     */
    public function removeItem($userId, $productId, $sessionId = null)
    {
        if ($userId === null && $sessionId !== null) {
            // Guest user
            $sql = "DELETE FROM sales_cart 
                    WHERE session_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$sessionId, $productId]);
        } else {
            // Logged in user
            $sql = "DELETE FROM sales_cart 
                    WHERE customer_id = ? AND product_id = ? AND is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $productId]);
        }
    }

    /**
     * Deactivate items after order
     */
    public function deactivateCart($userId)
    {
        $sql = "UPDATE sales_cart SET is_active = FALSE, updated_at = NOW() 
                WHERE customer_id = ? AND is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
    }

    /**
     * Get active cart items
     * @param int|null $userId - Customer ID (null for guest)
     * @param string|null $sessionId - Session ID for guest users
     */
    public function getCartItems($userId, $sessionId = null)
    {
        if ($userId === null && $sessionId !== null) {
            // Guest user
            $sql = "SELECT c.qty, p.*, pi.image_path as image
                    FROM sales_cart c
                    JOIN catalog_product_entity p ON c.product_id = p.entity_id
                    LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                    WHERE c.session_id = ? AND c.is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$sessionId]);
        } else {
            // Logged in user
            $sql = "SELECT c.qty, p.*, pi.image_path as image
                    FROM sales_cart c
                    JOIN catalog_product_entity p ON c.product_id = p.entity_id
                    LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                    WHERE c.customer_id = ? AND c.is_active = TRUE";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Merge guest cart into user cart on login
     * @param int $userId - Customer ID
     * @param string $sessionId - Guest session ID
     */
    public function mergeGuestCart($userId, $sessionId)
    {
        // Get all guest cart items
        $sql = "SELECT product_id, qty FROM sales_cart 
                WHERE session_id = ? AND is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$sessionId]);
        $guestItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Merge each item into user cart
        foreach ($guestItems as $item) {
            $this->addItem($userId, $item['product_id'], $item['qty']);
        }

        // Delete guest cart items
        $deleteSql = "DELETE FROM sales_cart WHERE session_id = ?";
        $deleteStmt = $this->conn->prepare($deleteSql);
        $deleteStmt->execute([$sessionId]);
    }
}
