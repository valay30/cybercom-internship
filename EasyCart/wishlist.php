<?php

/**
 * Wishlist Page - Entry Point
 * Routes to WishlistController and renders wishlist view
 */

require_once 'data.php';
require_once 'app/controllers/WishlistController.php';

session_start();

// Initialize controller
$wishlistController = new WishlistController($products);

// Handle POST actions (toggle wishlist)
$wishlistController->handleAction();

// Get data for view
$wishlistProducts = $wishlistController->getWishlistProducts();
$isEmpty = $wishlistController->isEmpty();

// Render view
require_once 'app/views/wishlist/index.php';
