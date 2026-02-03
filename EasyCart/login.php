<?php

/**
 * Login Page - Entry Point
 * Routes to AuthController and renders login view
 */

require_once 'app/controllers/AuthController.php';

session_start();

// Initialize controller
$authController = new AuthController();

// Handle form submissions (login/signup)
$authController->handleAction();

// Get data for view
$viewData = $authController->getViewData();
extract($viewData);

// Render view
require_once 'app/views/auth/login.php';
