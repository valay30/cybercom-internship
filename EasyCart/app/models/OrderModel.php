<?php

require_once __DIR__ . '/../../config/database.php';

class OrderModel
{
    private $qb;

    public function __construct($pdo = null)
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder($pdo);
    }

    public function getQb()
    {
        return $this->qb;
    }

    public function createOrder($userId, $orderData, $cartItems)
    {
        try {
            $this->qb->beginTransaction();

            // Generate Order Number
            $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);

            // 1. Insert into sales_order
            $orderId = $this->qb->table('sales_order')->insertGetId([
                'customer_id' => $userId,
                'order_number' => $orderNumber,
                'subtotal' => $orderData['subtotal'],
                'shipping_type' => $orderData['shipping_type'],
                'shipping_cost' => $orderData['shipping_cost'],
                'tax_amount' => $orderData['tax_amount'],
                'discount_amount' => $orderData['discount_amount'] ?? 0.00,
                'coupon_code' => $orderData['coupon_code'] ?? null,
                'payment_method' => $orderData['payment_method'] ?? null,
                'grand_total' => $orderData['grand_total'],
                'order_status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ], 'order_id');

            if (!$orderId) {
                throw new Exception("Failed to create order");
            }

            // 2. Insert into sales_order_address
            $addressesToSave = [];
            // Prepare Billing Address
            if (isset($orderData['billing_address'])) {
                $addressesToSave['billing'] = $orderData['billing_address'];
            }
            // Prepare Shipping Address
            if (isset($orderData['shipping_address'])) {
                $addressesToSave['shipping'] = $orderData['shipping_address'];
            } elseif (isset($orderData['address'])) {
                $addressesToSave['shipping'] = $orderData['address'];
                if (!isset($addressesToSave['billing'])) {
                    $addressesToSave['billing'] = $orderData['address'];
                }
            }

            foreach ($addressesToSave as $type => $addr) {
                $this->qb->table('sales_order_address')->insert([
                    'order_id' => $orderId,
                    'customer_id' => $userId,
                    'address_type' => $type,
                    'full_name' => $addr['full_name'] ?? $addr['name'] ?? '',
                    'email' => $addr['email'] ?? '',
                    'street' => $addr['street'] ?? '',
                    'city' => $addr['city'] ?? '',
                    'state' => $addr['state'] ?? '',
                    'postcode' => $addr['postcode'] ?? '',
                    'country' => $addr['country'] ?? '',
                    'telephone' => $addr['phone'] ?? $addr['telephone'] ?? ''
                ]);
            }

            // 3. Insert into sales_order_product
            // Prefetch product IDs if needed
            $productTable = $this->qb->table('catalog_product_entity');

            foreach ($cartItems as $sku => $item) {
                $productId = $item['product_id'] ?? null;

                if (!$productId) {
                    $productId = $productTable->where('sku', $sku)->value('entity_id');
                }

                $this->qb->table('sales_order_product')->insert([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'product_sku' => $sku,
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                    'row_total' => $item['price'] * $item['qty']
                ]);
            }

            // 4. Deactivate items in DB Cart (if customer logged in)
            if ($userId) {
                require_once __DIR__ . '/CartModel.php';
                // Pass current PDO connection to share transaction
                $cartModel = new CartModel($this->qb->getPdo());
                $cartModel->deactivateCart($userId);
            }

            // 5. Save Address to Customer Address Book (if logged in)
            if ($userId) {
                require_once __DIR__ . '/CustomerModel.php';
                $customerModel = new CustomerModel($this->qb->getPdo());

                if (isset($addressesToSave['shipping'])) {
                    $customerModel->saveAddress($userId, $addressesToSave['shipping'], 'shipping');
                }
                if (isset($addressesToSave['billing'])) {
                    $customerModel->saveAddress($userId, $addressesToSave['billing'], 'billing');
                }
            }

            $this->qb->commit();
            return $orderNumber;
        } catch (Exception $e) {
            $this->qb->rollBack();
            error_log("Create Order Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserOrders($userId)
    {
        $orders = $this->qb->table('sales_order')
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Fetch items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);

            // Calculate total item count (sum of quantities)
            $itemCount = 0;
            foreach ($order['items'] as $item) {
                $itemCount += intval($item['quantity']);
            }
            $order['item_count'] = $itemCount;

            // Format ID for display
            $order['id'] = $order['order_number']; // Use order_number as ID

            // Check formatted date
            if (isset($order['created_at'])) {
                $order['date'] = date('F j, Y', strtotime($order['created_at']));
            } else {
                $order['date'] = 'N/A';
            }

            $order['total'] = $order['grand_total'];
        }

        return $orders;
    }

    private function getOrderItems($orderId)
    {
        return $this->qb->table('sales_order_product sop')
            ->select(['sop.*', "COALESCE(pi.image_path, 'images/placeholder.png') as image"])
            ->leftJoin('catalog_product_entity p', 'sop.product_sku = p.sku')
            ->leftJoin('catalog_product_image pi', "p.entity_id = pi.product_id AND pi.is_primary = TRUE")
            ->where('sop.order_id', $orderId)
            ->get();
    }

    /**
     * Get the last shipping address used by this customer
     */
    public function getLastOrderAddress($userId)
    {
        return $this->qb->table('sales_order_address sa')
            ->select(['sa.*'])
            ->join('sales_order so', 'sa.order_id = so.order_id')
            ->where('so.customer_id', $userId)
            ->where('sa.address_type', 'shipping')
            ->orderBy('so.created_at', 'DESC')
            ->first();
    }
}
