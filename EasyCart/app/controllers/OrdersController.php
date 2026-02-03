<?php

/**
 * OrdersController
 * Handles order management logic
 */

class OrdersController
{
    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
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
