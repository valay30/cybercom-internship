<?php

require_once __DIR__ . '/../../config/database.php';

class OrderModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function createOrder($userId, $orderData, $cartItems)
    {
        try {
            $this->conn->beginTransaction();

            // Generate Order Number
            $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);

            // 1. Insert into sales_order
            $query = "INSERT INTO sales_order (
                        customer_id, 
                        order_number,
                        subtotal, 
                        shipping_type, 
                        shipping_cost, 
                        tax_amount, 
                        discount_amount,
                        coupon_code,
                        payment_method,
                        grand_total, 
                        order_status,
                        created_at
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()) RETURNING order_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $userId,
                $orderNumber,
                $orderData['subtotal'],
                $orderData['shipping_type'],
                $orderData['shipping_cost'],
                $orderData['tax_amount'],
                $orderData['discount_amount'] ?? 0.00,
                $orderData['coupon_code'] ?? null,
                $orderData['payment_method'] ?? null,
                $orderData['grand_total'],
                'pending'
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception("Failed to retrieve order ID");
            }
            $orderId = $result['order_id'];

            // 2. Insert into sales_order_address
            $address = $orderData['address'] ?? [];
            $fullName = $address['fullname'] ?? 'Guest';
            $addrQuery = "INSERT INTO sales_order_address (
                            order_id,
                            customer_id,
                            address_type,
                            full_name,
                            email,
                            street,
                            city,
                            state,
                            postcode,
                            country,
                            telephone
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $addrStmt = $this->conn->prepare($addrQuery);
            $addrStmt->execute([
                $orderId,
                $userId,
                'shipping',
                $fullName,
                $address['email'] ?? '',
                $address['street'] ?? '',
                $address['city'] ?? '',
                $address['state'] ?? '',
                $address['postcode'] ?? '',
                $address['country'] ?? '',
                $address['phone'] ?? ''
            ]);

            // 3. Insert into sales_order_product
            $itemQuery = "INSERT INTO sales_order_product (
                            order_id,
                            product_id,
                            product_sku, 
                            product_name, 
                            price, 
                            quantity, 
                            row_total
                          ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $itemStmt = $this->conn->prepare($itemQuery);
            $lookupStmt = $this->conn->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");

            foreach ($cartItems as $sku => $item) {
                $productId = $item['product_id'] ?? null;

                // Fallback: lookup ID if not in session (e.g. old cart session)
                if (!$productId) {
                    $lookupStmt->execute([$sku]);
                    $productId = $lookupStmt->fetchColumn();
                }

                $itemStmt->execute([
                    $orderId,
                    $productId, // Insert Product ID
                    $sku, // SKU is key
                    $item['name'],
                    $item['price'],
                    $item['qty'],
                    $item['price'] * $item['qty']
                ]);
            }

            // 4. Deactivate items in DB Cart (if customer logged in)
            if ($userId) {
                require_once __DIR__ . '/CartModel.php';
                $cartModel = new CartModel();
                $cartModel->deactivateCart($userId);
            }

            // 5. Save Address to Customer Address Book (if logged in)
            if ($userId) {
                // Check if this specific address already exists for the user avoid duplicates
                $checkAddr = "SELECT entity_id FROM customer_address 
                              WHERE customer_id = ? 
                              AND street = ? 
                              AND city = ? 
                              AND postcode = ?";
                $checkStmt = $this->conn->prepare($checkAddr);
                $checkStmt->execute([
                    $userId,
                    $address['street'] ?? '',
                    $address['city'] ?? '',
                    $address['postcode'] ?? ''
                ]);

                if (!$checkStmt->fetch()) {
                    // Start: Insert new address
                    $saveAddrSql = "INSERT INTO customer_address (
                                        customer_id,
                                        street,
                                        city,
                                        state,
                                        postcode,
                                        country,
                                        telephone,
                                        is_default
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                    // Check if this is their first address (make it default)
                    $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM customer_address WHERE customer_id = ?");
                    $countStmt->execute([$userId]);
                    $isDefault = ($countStmt->fetchColumn() == 0) ? true : false;

                    $saveStmt = $this->conn->prepare($saveAddrSql);
                    $saveStmt->execute([
                        $userId,
                        $address['street'] ?? '',
                        $address['city'] ?? '',
                        $address['state'] ?? '',
                        $address['postcode'] ?? '',
                        $address['country'] ?? '',
                        $address['phone'] ?? '',
                        $isDefault ? 1 : 0 // Boolean to integer/bit
                    ]);
                }
            }

            $this->conn->commit();
            return $orderNumber; // Return the display-friendly Order Number

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Database Error: " . $e->getMessage());
            // For debugging (remove in production if strict)
            // echo "DEBUG ERROR: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function getUserOrders($userId)
    {
        $query = "SELECT * FROM sales_order WHERE customer_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch items for each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);

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
        // Join with product/image tables to get the product image
        // Match using SKU (assuming sales_order_product.product_sku maps to catalog_product_entity.sku)
        $query = "SELECT 
                    sop.*, 
                    COALESCE(pi.image_path, 'images/placeholder.png') as image
                  FROM sales_order_product sop
                  LEFT JOIN catalog_product_entity p ON sop.product_sku = p.sku
                  LEFT JOIN catalog_product_image pi ON p.entity_id = pi.product_id AND pi.is_primary = TRUE
                  WHERE sop.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Get the last shipping address used by this customer
     */
    public function getLastOrderAddress($userId)
    {
        // Fetch the most recent address based on order date
        $query = "SELECT sa.* 
                  FROM sales_order_address sa
                  JOIN sales_order so ON sa.order_id = so.order_id
                  WHERE so.customer_id = ? AND sa.address_type = 'shipping'
                  ORDER BY so.created_at DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
