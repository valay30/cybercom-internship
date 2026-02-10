<?php
require_once __DIR__ . '/../../config/database.php';

class CartModel
{
    private $qb;

    public function __construct($pdo = null)
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder($pdo);
    }

    /**
     * Add or Update item in cart
     */
    public function addItem($userId, $productId, $qty, $sessionId = null)
    {
        $query = $this->qb->table('sales_cart')
            ->select(['item_id', 'qty'])
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($userId === null && $sessionId !== null) {
            $query->where('session_id', $sessionId);
        } else {
            $query->where('customer_id', $userId);
        }

        $existing = $query->first();

        if ($existing) {
            // Update quantity
            $newQty = $existing['qty'] + $qty;
            $this->qb->table('sales_cart')
                ->where('item_id', $existing['item_id'])
                ->update(['qty' => $newQty, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            // Insert new item
            $data = [
                'product_id' => $productId,
                'qty' => $qty,
                'is_active' => 'TRUE'
            ];

            if ($userId === null && $sessionId !== null) {
                $data['session_id'] = $sessionId;
            } else {
                $data['customer_id'] = $userId;
            }

            $this->qb->table('sales_cart')->insert($data);
        }
    }

    /**
     * Update exact quantity
     */
    public function updateQty($userId, $productId, $qty, $sessionId = null)
    {
        if ($qty <= 0) {
            $this->removeItem($userId, $productId, $sessionId);
            return;
        }

        $query = $this->qb->table('sales_cart')
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($userId === null && $sessionId !== null) {
            $query->where('session_id', $sessionId);
        } else {
            $query->where('customer_id', $userId);
        }

        $query->update(['qty' => $qty, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Remove item
     */
    public function removeItem($userId, $productId, $sessionId = null)
    {
        $query = $this->qb->table('sales_cart')
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($userId === null && $sessionId !== null) {
            $query->where('session_id', $sessionId);
        } else {
            $query->where('customer_id', $userId);
        }

        $query->delete();
    }

    /**
     * Deactivate items after order
     */
    public function deactivateCart($userId)
    {
        $this->qb->table('sales_cart')
            ->where('customer_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => 'FALSE', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get active cart items
     */
    public function getCartItems($userId, $sessionId = null)
    {
        $query = $this->qb->table('sales_cart c')
            ->select(['c.qty', 'p.*', 'pi.image_path as image'])
            ->join('catalog_product_entity p', 'c.product_id = p.entity_id')
            ->leftJoin('catalog_product_image pi', "p.entity_id = pi.product_id AND pi.is_primary = TRUE")
            ->where('c.is_active', true);

        if ($userId === null && $sessionId !== null) {
            $query->where('c.session_id', $sessionId);
        } else {
            $query->where('c.customer_id', $userId);
        }

        return $query->get();
    }

    /**
     * Merge guest cart into user cart on login
     */
    public function mergeGuestCart($userId, $sessionId)
    {
        // Get all guest cart items
        $guestItems = $this->qb->table('sales_cart')
            ->select(['product_id', 'qty'])
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->get();

        // Merge each item into user cart
        foreach ($guestItems as $item) {
            $this->addItem($userId, $item['product_id'], $item['qty']);
        }

        // Delete guest cart items
        $this->qb->table('sales_cart')
            ->where('session_id', $sessionId)
            ->delete();
    }
}
