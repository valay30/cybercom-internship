<?php

/**
 * Orders Page - Entry Point
 * Routes to OrdersController and renders orders view
 */

require_once 'app/controllers/OrdersController.php';

session_start();

// Initialize controller
$ordersController = new OrdersController();

// Handle order placement from checkout
$ordersController->handleOrderPlacement();

// Get data for view
$viewData = $ordersController->getViewData();
extract($viewData);

// Render view
require_once 'app/views/orders/index.php';
