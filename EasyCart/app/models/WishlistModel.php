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
    private $qb;

    public function __construct()
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder();
    }

    /**
     * Add item to wishlist
     */
    public function addItem($customerId, $productId, $sessionId = null)
    {
        if ($this->isInWishlist($customerId, $productId, $sessionId)) {
            return true;
        }

        $data = [
            'product_id' => $productId,
            'added_at' => date('Y-m-d H:i:s')
        ];

        if ($customerId) {
            $data['customer_id'] = $customerId;
        } else {
            $data['session_id'] = $sessionId;
        }

        return $this->qb->table('customer_wishlist')->insert($data);
    }

    /**
     * Remove item from wishlist
     */
    public function removeItem($customerId, $productId, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist')
            ->where('product_id', $productId);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId)
                ->where('customer_id', null);
        }

        return $query->delete();
    }

    /**
     * Get all product IDs in wishlist
     */
    public function getWishlistProductIds($customerId = null, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId)
                ->where('customer_id', null);
        }

        return $query->pluck('product_id');
    }

    /**
     * Get all wishlist items with product details
     */
    public function getWishlistItems($customerId = null, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist w')
            ->select([
                'w.wishlist_item_id',
                'w.product_id',
                'w.added_at',
                'p.sku',
                'p.name',
                'p.price',
                'p.description',
                'pi.image_path'
            ])
            ->join('catalog_product_entity p', 'w.product_id = p.entity_id')
            ->leftJoin('catalog_product_image pi', "p.entity_id = pi.product_id AND pi.is_primary = TRUE");

        if ($customerId) {
            $query->where('w.customer_id', $customerId);
        } else {
            $query->where('w.session_id', $sessionId)
                ->where('w.customer_id', null);
        }

        return $query->orderBy('w.added_at', 'DESC')->get();
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist($customerId, $productId, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist')
            ->where('product_id', $productId);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId)
                ->where('customer_id', null);
        }

        return $query->exists();
    }

    /**
     * Get wishlist count
     */
    public function getWishlistCount($customerId = null, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId)
                ->where('customer_id', null);
        }

        return $query->count();
    }

    /**
     * Clear entire wishlist
     */
    public function clearWishlist($customerId = null, $sessionId = null)
    {
        $query = $this->qb->table('customer_wishlist');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId)
                ->where('customer_id', null);
        }

        return $query->delete();
    }

    /**
     * Merge guest wishlist into customer wishlist on login
     */
    public function mergeGuestWishlist($customerId, $sessionId)
    {
        try {
            $this->qb->beginTransaction();

            // 1. Get customer's existing product IDs to avoid duplicates
            $existingIds = $this->qb->table('customer_wishlist')
                ->where('customer_id', $customerId)
                ->pluck('product_id');

            // 2. Delete duplicates from guest session
            if (!empty($existingIds)) {
                $this->qb->table('customer_wishlist')
                    ->where('session_id', $sessionId)
                    ->where('customer_id', null)
                    ->whereIn('product_id', $existingIds)
                    ->delete();
            }

            // 3. Update remaining guest items to belong to the customer
            $this->qb->table('customer_wishlist')
                ->where('session_id', $sessionId)
                ->where('customer_id', null)
                ->update(['customer_id' => $customerId, 'session_id' => null]);

            $this->qb->commit();
            return true;
        } catch (Exception $e) {
            $this->qb->rollBack();
            error_log("Wishlist mergeGuestWishlist error: " . $e->getMessage());
            return false;
        }
    }
}
