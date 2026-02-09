<?php

/**
 * CheckoutController
 * Handles all checkout-related business logic
 */

class CheckoutController
{
    private $products;
    private $coupons;
    private $cart;
    private $subtotal;
    private $shippingCost;
    private $taxAmount;
    private $totalAmount;
    private $cartType;
    private $enableStandard;
    private $enableExpress;
    private $enableWhiteGlove;
    private $enableFreight;
    private $shippingStd;
    private $shippingExpress;
    private $shippingWhiteGlove;
    private $shippingFreight;
    private $savedAddress;


    public function __construct()
    {
        $this->coupons = [
            'SAVE5' => ['discount' => 5, 'description' => '5% off on total'],
            'SAVE10' => ['discount' => 10, 'description' => '10% off on total'],
            'SAVE15' => ['discount' => 15, 'description' => '15% off on total'],
            'SAVE20' => ['discount' => 20, 'description' => '20% off on total']
        ];

        // Enrich cart with DB data
        $sessionCart = $_SESSION['cart'] ?? [];
        $this->cart = [];

        if (!empty($sessionCart)) {
            require_once __DIR__ . '/../models/ProductModel.php';
            $productModel = new ProductModel();

            foreach ($sessionCart as $sku => $item) {
                $productId = $item['product_id'] ?? null;
                if ($productId) {
                    $product = $productModel->getProductById($productId);
                    if ($product) {
                        // Merge DB details with Session data (qty)
                        $enrichedItem = $product;
                        $enrichedItem['qty'] = $item['qty'];
                        $enrichedItem['product_id'] = $productId; // Ensure ID is present

                        $this->cart[$sku] = $enrichedItem;
                    }
                }
            }
        }

        $this->calculateSubtotal();
        $this->determineShippingOptions();

        // New: Pre-fill address if user is logged in
        $this->savedAddress = null;
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/CustomerModel.php';
            $customerModel = new CustomerModel();

            // Get base customer info (Name, Email from profile)
            $customer = $customerModel->getCustomerById($_SESSION['user_id']);

            // Get address book info (Street, City, etc from customer_address table)
            $address = $customerModel->getAddress($_SESSION['user_id']);

            if ($customer) {
                $this->savedAddress = [
                    'full_name' => $customer['full_name'],
                    'email' => $customer['email'],
                    'telephone' => $address['telephone'] ?? '',
                    'street' => $address['street'] ?? '',
                    'city' => $address['city'] ?? '',
                    'state' => $address['state'] ?? '',
                    'postcode' => $address['postcode'] ?? '',
                    'country' => $address['country'] ?? ''
                ];
            }
        }
    }

    /**
     * Handle AJAX requests
     */
    public function handleAjax()
    {
        if (!isset($_POST['ajax']) || $_POST['ajax'] !== 'true') {
            return;
        }

        header('Content-Type: application/json');

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'update_shipping':
                $this->handleShippingUpdate();
                break;
            case 'apply_coupon':
                $this->handleCouponValidation();
                break;
            case 'place_order':
                $this->handleOrderPlacement();
                break;
        }

        exit;
    }

    /**
     * Handle order placement
     */
    private function handleOrderPlacement()
    {
        // 1. Verify User Login
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
            return;
        }

        if (empty($this->cart)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
            return;
        }

        // 2. Prepare Order Data
        // 2. Prepare Order Data
        // Calculate Discount logic
        $discountAmount = 0.00;
        $couponCode = null;

        if (!empty($_POST['applied_coupon'])) {
            $code = strtoupper($_POST['applied_coupon']);
            if (isset($this->coupons[$code])) {
                $couponCode = $code;
                $percent = $this->coupons[$code]['discount'];
                // Discount on (Subtotal + Shipping) as per JS logic
                $baseAmount = $this->subtotal + $this->shippingCost;
                $discountAmount = $baseAmount * ($percent / 100);
            }
        }

        // Recalculate Totals based on discount
        $amountAfterDiscount = ($this->subtotal + $this->shippingCost) - $discountAmount;
        $this->taxAmount = $amountAfterDiscount * 0.18;
        $this->totalAmount = $amountAfterDiscount + $this->taxAmount;


        // Prepare Shipping Address (Primary Fields)
        $shippingAddress = [
            'fullname' => $_POST['name'] ?? '',
            'phone' => $_POST['mobile'] ?? '',
            'email' => $_POST['email'] ?? '',
            'street' => $_POST['street'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'postcode' => $_POST['postcode'] ?? '',
            'country' => $_POST['country'] ?? ''
        ];

        // Prepare Billing Address (Secondary Fields or Copy)
        $billingAddress = [];
        if (isset($_POST['same_as_shipping']) && $_POST['same_as_shipping'] == '1') {
            $billingAddress = $shippingAddress;
        } else {
            $billingAddress = [
                'fullname' => $_POST['name'] ?? '',   // Use primary/shipping name
                'phone' => $_POST['mobile'] ?? '',    // Use primary/shipping mobile
                'email' => $_POST['email'] ?? '',
                'street' => $_POST['billing_street'] ?? '',
                'city' => $_POST['billing_city'] ?? '',
                'state' => $_POST['billing_state'] ?? '',
                'postcode' => $_POST['billing_postcode'] ?? '',
                'country' => $_POST['billing_country'] ?? ''
            ];
        }

        $orderData = [
            'shipping_type' => $_POST['shipping_type'] ?? 'standard',
            'shipping_cost' => $this->shippingCost,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $couponCode,
            'payment_method' => $_POST['payment'] ?? 'cod',
            'grand_total' => $this->totalAmount,
            'billing_address' => $billingAddress,
            'shipping_address' => $shippingAddress
        ];

        // 3. Save to Database
        require_once __DIR__ . '/../models/OrderModel.php';
        $orderModel = new OrderModel();

        $orderId = $orderModel->createOrder($_SESSION['user_id'], $orderData, $this->cart);

        if ($orderId) {
            // 4. Update Customer Address (Save for future use)
            require_once __DIR__ . '/../models/CustomerModel.php';
            $customerModel = new CustomerModel();

            // Save Shipping Address
            try {
                $customerModel->saveAddress($_SESSION['user_id'], $shippingAddress, 'shipping');
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/../../address_error.log', date('Y-m-d H:i:s') . " - Shipping Save Failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            // Save Billing Address
            try {
                $customerModel->saveAddress($_SESSION['user_id'], $billingAddress, 'billing');
            } catch (Exception $e) {
                file_put_contents(__DIR__ . '/../../address_error.log', date('Y-m-d H:i:s') . " - Billing Save Failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            // Clear cart
            unset($_SESSION['cart']);
            $this->cart = [];

            echo json_encode(['success' => true, 'redirect' => 'orders?success=1']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create order. Please try again.']);
        }
    }

    /**
     * Handle shipping update AJAX request
     */
    private function handleShippingUpdate()
    {
        $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);

        // Calculate tax and total
        $taxable_amount = $this->subtotal + $shipping_cost;
        $tax_amount = $taxable_amount * 0.18;
        $total_amount = $taxable_amount + $tax_amount;

        echo json_encode([
            'success' => true,
            'subtotal' => $this->subtotal,
            'shipping' => $shipping_cost,
            'tax' => $tax_amount,
            'total' => $total_amount,
            'formatted' => [
                'subtotal' => '₹' . number_format($this->subtotal, 2),
                'shipping' => '₹' . number_format($shipping_cost, 2),
                'tax' => '₹' . number_format($tax_amount, 2),
                'total' => '₹' . number_format($total_amount, 2)
            ]
        ]);
    }

    /**
     * Handle coupon validation AJAX request
     */
    private function handleCouponValidation()
    {
        $coupon_code = strtoupper(trim($_POST['coupon_code'] ?? ''));

        if (empty($coupon_code)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
            return;
        }

        if (isset($this->coupons[$coupon_code])) {
            echo json_encode([
                'success' => true,
                'discount' => $this->coupons[$coupon_code]['discount'],
                'description' => $this->coupons[$coupon_code]['description'],
                'message' => 'Coupon applied successfully!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid coupon code']);
        }
    }

    /**
     * Calculate subtotal with discounts
     */
    private function calculateSubtotal()
    {
        $this->subtotal = 0;

        foreach ($this->cart as $item) {
            if (isset($item['qty']) && isset($item['price'])) {
                $discount_percent = min($item['qty'], 50);
                $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
                $this->subtotal += ($discounted_price * $item['qty']);
            }
        }
    }

    /**
     * Determine shipping options based on cart contents
     */
    private function determineShippingOptions()
    {
        $has_freight_product = false;

        // Check for freight products in cart
        foreach ($this->cart as $item) {
            if (isset($item['shipping_type']) && $item['shipping_type'] === 'freight') {
                $has_freight_product = true;
                break;
            }
        }

        // Determine cart shipping options based on rules
        if ($has_freight_product) {
            // Rule A: Any freight product -> Enable White Glove + Freight, Disable Standard + Express
            $this->cartType = 'freight';
            $this->enableStandard = false;
            $this->enableExpress = false;
            $this->enableWhiteGlove = true;
            $this->enableFreight = true;
        } elseif ($this->subtotal > 300) {
            // Rule B: Subtotal > 300 -> Enable White Glove + Freight, Disable Standard + Express
            $this->cartType = 'white_glove_freight';
            $this->enableStandard = false;
            $this->enableExpress = false;
            $this->enableWhiteGlove = true;
            $this->enableFreight = true;
        } else {
            // Rule C: Subtotal < 300 -> Enable Standard + Express, Disable White Glove + Freight
            $this->cartType = 'standard_express';
            $this->enableStandard = true;
            $this->enableExpress = true;
            $this->enableWhiteGlove = false;
            $this->enableFreight = false;
        }



        // Calculate shipping costs
        $this->calculateShippingCosts();
    }

    /**
     * Calculate shipping costs for all options
     */
    private function calculateShippingCosts()
    {
        // Standard: Flat 40
        $this->shippingStd = 40;

        // Express: Flat 80 OR 10% of subtotal (whichever is lower)
        $this->shippingExpress = min(80, $this->subtotal * 0.10);

        // White Glove: Flat 150 OR 5% of subtotal (whichever is lower)
        $this->shippingWhiteGlove = min(150, $this->subtotal * 0.05);

        // Freight: 3% of subtotal, Minimum 200
        $this->shippingFreight = max(200, $this->subtotal * 0.03);

        // Default shipping cost based on cart type
        if ($this->enableStandard) {
            $this->shippingCost = $this->shippingStd;
        } else {
            $this->shippingCost = $this->shippingWhiteGlove;
        }

        // Calculate tax and total
        $this->taxAmount = ($this->subtotal + $this->shippingCost) * 0.18;
        $this->totalAmount = $this->subtotal + $this->shippingCost + $this->taxAmount;
    }

    /**
     * Get data for view
     */
    public function getViewData()
    {
        return [
            'cart' => $this->cart,
            'subtotal' => $this->subtotal,
            'shippingCost' => $this->shippingCost,
            'taxAmount' => $this->taxAmount,
            'totalAmount' => $this->totalAmount,
            'cartType' => $this->cartType,
            'enableStandard' => $this->enableStandard,
            'enableExpress' => $this->enableExpress,
            'enableWhiteGlove' => $this->enableWhiteGlove,
            'enableFreight' => $this->enableFreight,
            'shippingStd' => $this->shippingStd,
            'shippingExpress' => $this->shippingExpress,
            'shippingWhiteGlove' => $this->shippingWhiteGlove,
            'shippingFreight' => $this->shippingFreight,
            'savedAddress' => $this->savedAddress
        ];
    }
}
