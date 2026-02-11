<?php

/**
 * Dashboard Page - Entry Point
 * Routes to DashboardController and renders dashboard view
 */

require_once 'app/controllers/DashboardController.php';

session_start();

// Initialize controller
$dashboardController = new DashboardController();

// Handle AJAX requests
$dashboardController->handleAjax();

// Get data for view
$viewData = $dashboardController->getViewData();
extract($viewData);

// Render View
include 'app/views/dashboard/index.php';
