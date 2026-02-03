<?php

/**
 * Product Listing Page - Entry Point
 * Routes to ProductListingController and renders product listing view
 */

require_once 'app/controllers/ProductListingController.php';

session_start();

// Initialize controller
$plpController = new ProductListingController();

// Handle AJAX requests
$plpController->handleAjax();

// Get data for view
$viewData = $plpController->getViewData();
extract($viewData);

// Render view
require_once 'app/views/products/index.php';
