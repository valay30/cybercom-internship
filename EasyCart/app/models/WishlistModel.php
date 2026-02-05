<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Wishlist Model
 * Handles wishlist operations with consolidated table structure
 * 
 * Updated: Now uses single customer_wishlist table (no junction table)
 */
class WishlistModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Add item to wishlist
     * @param int|null $customerId
     * @param int $productId
     * @param string|null $sessionId
     * @return bool
     */
    public function addItem($customerId, $productId, $sessionId = null)
    {
        try {
            // Insert or ignore if already exists
            $sql = "INSERT INTO customer_wishlist (customer_id, product_id, session_id, added_at) 
                    VALUES (:cid, :pid, :sid, NOW())";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':cid' => $customerId,
                ':pid' => $productId,
                ':sid' => $sessionId
            ]);
        } catch (Exception $e) {
            error_log("Wishlist addItem error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove item from wishlist
     * @param int|null $customerId
     * @param int $productId
     * @param string|null $sessionId
     * @return bool
     */
    public function removeItem($customerId, $productId, $sessionId = null)
    {
        try {
            if ($customerId) {
                $sql = "DELETE FROM customer_wishlist 
                        WHERE customer_id = :cid AND product_id = :pid";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([':cid' => $customerId, ':pid' => $productId]);
            } else {
                $sql = "DELETE FROM customer_wishlist 
                        WHERE session_id = :sid AND product_id = :pid AND customer_id IS NULL";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([':sid' => $sessionId, ':pid' => $productId]);
            }
        } catch (Exception $e) {
            error_log("Wishlist removeItem error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all product IDs in wishlist
     * @param int|null $customerId
     * @param string|null $sessionId
     * @return array
     */
    public function getWishlistProductIds($customerId = null, $sessionId = null)
    {
        try {
            if ($customerId) {
                $sql = "SELECT product_id FROM customer_wishlist WHERE customer_id = :cid";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':cid' => $customerId]);
            } else {
                $sql = "SELECT product_id FROM customer_wishlist 
                        WHERE session_id = :sid AND customer_id IS NULL";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':sid' => $sessionId]);
            }

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Wishlist getWishlistProductIds error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all wishlist items with product details
     * @param int|null $customerId
     * @param string|null $sessionId
     * @return array
     */
    public function getWishlistItems($customerId = null, $sessionId = null)
    {
        try {
            $sql = "SELECT 
                        w.wishlist_item_id,
                        w.product_id,
                        w.added_at,
                        p.sku,
                        p.name,
                        p.price,
                        p.description,
                        pi.image_path
                    FROM customer_wishlist w
                    JOIN catalog_product_entity p ON w.product_id = p.entity_id
                    LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                    WHERE ";

            if ($customerId) {
                $sql .= "w.customer_id = :cid";
                $params = [':cid' => $customerId];
            } else {
                $sql .= "w.session_id = :sid AND w.customer_id IS NULL";
                $params = [':sid' => $sessionId];
            }

            $sql .= " ORDER BY w.added_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Wishlist getWishlistItems error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if product is in wishlist
     * @param int|null $customerId
     * @param int $productId
     * @param string|null $sessionId
     * @return bool
     */
    public function isInWishlist($customerId, $productId, $sessionId = null)
    {
        try {
            if ($customerId) {
                $sql = "SELECT 1 FROM customer_wishlist 
                        WHERE customer_id = :cid AND product_id = :pid";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':cid' => $customerId, ':pid' => $productId]);
            } else {
                $sql = "SELECT 1 FROM customer_wishlist 
                        WHERE session_id = :sid AND product_id = :pid AND customer_id IS NULL";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':sid' => $sessionId, ':pid' => $productId]);
            }

            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log("Wishlist isInWishlist error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get wishlist count
     * @param int|null $customerId
     * @param string|null $sessionId
     * @return int
     */
    public function getWishlistCount($customerId = null, $sessionId = null)
    {
        try {
            if ($customerId) {
                $sql = "SELECT COUNT(*) as count FROM customer_wishlist WHERE customer_id = :cid";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':cid' => $customerId]);
            } else {
                $sql = "SELECT COUNT(*) as count FROM customer_wishlist 
                        WHERE session_id = :sid AND customer_id IS NULL";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':sid' => $sessionId]);
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Wishlist getWishlistCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear entire wishlist
     * @param int|null $customerId
     * @param string|null $sessionId
     * @return bool
     */
    public function clearWishlist($customerId = null, $sessionId = null)
    {
        try {
            if ($customerId) {
                $sql = "DELETE FROM customer_wishlist WHERE customer_id = :cid";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([':cid' => $customerId]);
            } else {
                $sql = "DELETE FROM customer_wishlist WHERE session_id = :sid AND customer_id IS NULL";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([':sid' => $sessionId]);
            }
        } catch (Exception $e) {
            error_log("Wishlist clearWishlist error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Merge guest wishlist into customer wishlist on login
     * @param int $customerId
     * @param string $sessionId
     * @return bool
     */
    public function mergeGuestWishlist($customerId, $sessionId)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Delete items from guest wishlist that the customer already has
            // (These are duplicates so we don't need to merge them, just discard the guest copy)
            $deleteSql = "DELETE FROM customer_wishlist 
                          WHERE session_id = :sid 
                          AND customer_id IS NULL 
                          AND product_id IN (
                              SELECT product_id 
                              FROM customer_wishlist 
                              WHERE customer_id = :cid
                          )";
            $stmt = $this->conn->prepare($deleteSql);
            $stmt->execute([':sid' => $sessionId, ':cid' => $customerId]);

            // 2. Update remaining guest items to belong to the customer
            $updateSql = "UPDATE customer_wishlist 
                          SET customer_id = :cid, session_id = NULL 
                          WHERE session_id = :sid 
                          AND customer_id IS NULL";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->execute([':cid' => $customerId, ':sid' => $sessionId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Wishlist mergeGuestWishlist error: " . $e->getMessage());
            return false;
        }
    }
}
