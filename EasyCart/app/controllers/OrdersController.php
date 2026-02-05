<?php

/**
 * OrdersController
 * Handles order management logic
 */

class OrdersController
{
    private $orderModel;
    private $orders;

    public function __construct()
    {
        // 1. Check Login
        if (!isset($_SESSION['user_id'])) {
            header('Location: login?redirect=orders');
            exit;
        }

        // 2. Initialize Model
        require_once __DIR__ . '/../models/OrderModel.php';
        $this->orderModel = new OrderModel();

        // 3. Fetch Orders
        $this->orders = $this->orderModel->getUserOrders($_SESSION['user_id']);
    }

    /**
     * Handle order placement from checkout
     */
    public function handleOrderPlacement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // In a real app, we'd save the order to database
        // For now, just clear the cart
        unset($_SESSION['cart']);

        // Optionally redirect to orders page with success message
        // header('Location: orders.php?success=1');
        // exit;
    }

    /**
     * Get orders data for view
     */
    public function getViewData()
    {
        return [
            'orders' => $this->orders,
            'hasOrders' => !empty($this->orders)
        ];
    }
}
