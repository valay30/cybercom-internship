<?php

/**
 * Home Page - Entry Point
 * Routes to HomeController and renders home view
 */

require_once 'data.php';
require_once 'app/controllers/HomeController.php';

session_start();

// Initialize controller
$homeController = new HomeController($products, $categories, $brands);

// Get data for view
$viewData = $homeController->getViewData();
extract($viewData);

// Render view
require_once 'app/views/home/index.php';
