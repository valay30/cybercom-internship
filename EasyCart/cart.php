<?php

/**
 * Cart Page - Entry Point
 * Routes to CartController and renders cart view
 */

require_once 'app/controllers/CartController.php';

session_start();

// Initialize controller
$cartController = new CartController();

// Handle POST actions (add, update, remove)
$cartController->handleAction();

// Get data for view
$cartData = $cartController->getCartData();
$subtotal = $cartController->calculateSubtotal();
$checkoutLink = $cartController->getCheckoutLink();

// Render view
require_once 'app/views/cart/index.php';
