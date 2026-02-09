<?php

/**
 * Admin Entry Point
 * Handles admin operations including import/export
 */

session_start();

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

    default:
        $controller->showImportExport();
        break;
}
