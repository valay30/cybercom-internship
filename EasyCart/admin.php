<?php

/**
 * Admin Entry Point
 * Handles admin operations including import/export
 * RESTRICTED: Only accessible to admin users
 */

session_start();

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? null;

if (!$userId) {
    // Not logged in - redirect to login
    $_SESSION['error'] = 'Please login to access the admin panel.';
    $_SESSION['redirect_after_login'] = 'admin';
    header('Location: login');
    exit;
}

// Check if user is admin
require_once 'app/models/CustomerModel.php';
$customerModel = new CustomerModel();

if (!$customerModel->isAdmin($userId)) {
    // Not an admin - redirect to home with error
    $_SESSION['error'] = 'Access denied. You do not have permission to access the admin panel.';
    header('Location: index');
    exit;
}

// User is authenticated and authorized - proceed
require_once 'app/controllers/AdminController.php';

$controller = new AdminController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'export':
        $controller->exportProducts();
        break;

    case 'import':
        $controller->importProducts();
        break;

    case 'delete_user':
        $userId = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
        $controller->deleteUser($userId);
        break;

    default:
        $controller->showImportExport();
        break;
}
