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
     */
    public function addItem($userId, $productId, $qty)
    {
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

    /**
     * Update exact quantity
     */
    public function updateQty($userId, $productId, $qty)
    {
        if ($qty <= 0) {
            $this->removeItem($userId, $productId);
            return;
        }

        $sql = "UPDATE sales_cart SET qty = ?, updated_at = NOW() 
                WHERE customer_id = ? AND product_id = ? AND is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$qty, $userId, $productId]);
    }

    /**
     * Remove item
     */
    public function removeItem($userId, $productId)
    {
        $sql = "DELETE FROM sales_cart 
                WHERE customer_id = ? AND product_id = ? AND is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $productId]);
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
     */
    public function getCartItems($userId)
    {
        $sql = "SELECT c.qty, p.*, pi.image_path as image
                FROM sales_cart c
                JOIN catalog_product_entity p ON c.product_id = p.entity_id
                LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                WHERE c.customer_id = ? AND c.is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
