<?php

/**
 * Product Details Page - Entry Point
 * Routes to ProductDetailsController and renders product details view
 */

require_once 'data.php';
require_once 'app/controllers/ProductDetailsController.php';

session_start();

// Initialize controller
$pdpController = new ProductDetailsController($products);

// Get data for view
$viewData = $pdpController->getViewData();
extract($viewData);

// Render view
require_once 'app/views/products/details.php';
