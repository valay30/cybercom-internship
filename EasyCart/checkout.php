<?php

/**
 * Checkout Page - Entry Point
 * Routes to CheckoutController and renders checkout view
 */

require_once 'data.php';
require_once 'app/controllers/CheckoutController.php';

session_start();

// Initialize controller
$checkoutController = new CheckoutController($products, $coupons);

// Handle AJAX requests (shipping updates, coupon validation)
$checkoutController->handleAjax();

// Get data for view
$viewData = $checkoutController->getViewData();
extract($viewData); // Extract variables for use in view

// Render view
require_once 'app/views/checkout/index.php';
