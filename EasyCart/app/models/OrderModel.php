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
                        grand_total, 
                        order_status,
                        created_at
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW()) RETURNING order_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $userId,
                $orderNumber,
                $orderData['subtotal'],
                $orderData['shipping_type'],
                $orderData['shipping_cost'],
                $orderData['tax_amount'],
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
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';

            $addrQuery = "INSERT INTO sales_order_address (
                            order_id,
                            address_type,
                            firstname,
                            lastname,
                            street,
                            city,
                            postcode,
                            telephone
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $addrStmt = $this->conn->prepare($addrQuery);
            $addrStmt->execute([
                $orderId,
                'shipping',
                $firstName,
                $lastName,
                $address['address'] ?? '',
                $address['city'] ?? '',
                $address['zip'] ?? '',
                $address['phone'] ?? ''
            ]);

            // 3. Insert into sales_order_product
            $itemQuery = "INSERT INTO sales_order_product (
                            order_id, 
                            product_sku, 
                            product_name, 
                            price, 
                            quantity, 
                            row_total
                          ) VALUES (?, ?, ?, ?, ?, ?)";

            $itemStmt = $this->conn->prepare($itemQuery);

            foreach ($cartItems as $sku => $item) {
                $itemStmt->execute([
                    $orderId,
                    $sku, // SKU is key
                    $item['name'],
                    $item['price'],
                    $item['qty'],
                    $item['price'] * $item['qty']
                ]);
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
}
