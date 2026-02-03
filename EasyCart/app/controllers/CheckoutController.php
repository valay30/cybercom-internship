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


    public function __construct()
    {
        $this->coupons = [
            'SAVE5' => ['discount' => 5, 'description' => '5% off on total'],
            'SAVE10' => ['discount' => 10, 'description' => '10% off on total'],
            'SAVE15' => ['discount' => 15, 'description' => '15% off on total'],
            'SAVE20' => ['discount' => 20, 'description' => '20% off on total']
        ];

        $this->cart = $_SESSION['cart'] ?? [];

        $this->calculateSubtotal();
        $this->determineShippingOptions();
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

        // 2. Prepare Order Data
        $orderData = [
            'shipping_type' => $_POST['shipping_type'] ?? 'standard',
            'shipping_cost' => $this->shippingCost,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'grand_total' => $this->totalAmount,
            'address' => $_POST['address'] ?? []
        ];

        // 3. Save to Database
        require_once __DIR__ . '/../models/OrderModel.php';
        $orderModel = new OrderModel();

        $orderId = $orderModel->createOrder($_SESSION['user_id'], $orderData, $this->cart);

        if ($orderId) {
            // Clear cart
            unset($_SESSION['cart']);
            $this->cart = [];

            echo json_encode(['success' => true, 'redirect' => 'orders.php?success=1']);
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

        // Store in session
        $_SESSION['cart_type'] = $this->cartType;

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
            'shippingFreight' => $this->shippingFreight
        ];
    }
}
