<?php

/**
 * DashboardController
 * Handles user dashboard with order statistics and visualizations
 */

require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class DashboardController
{
    private $orderModel;
    private $customerModel;
    private $customerId;
    private $stats;
    private $chartData;

    public function __construct()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: login?redirect=dashboard');
            exit;
        }

        $this->customerId = $_SESSION['user_id'];
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();

        $this->loadDashboardData();
    }

    /**
     * Load all dashboard data
     */
    private function loadDashboardData()
    {
        $this->stats = $this->calculateStats();
        $this->chartData = $this->getChartData();
    }

    /**
     * Calculate dashboard statistics
     * @return array
     */
    private function calculateStats(): array
    {
        try {
            $orders = $this->orderModel->getUserOrders($this->customerId);

            $totalOrders = count($orders);
            $totalSpent = 0;
            $avgOrderValue = 0;
            $recentOrders = array_slice($orders, 0, 5);

            // Calculate total spent
            foreach ($orders as $order) {
                $totalSpent += floatval($order['grand_total']);
            }

            // Calculate average order value
            if ($totalOrders > 0) {
                $avgOrderValue = $totalSpent / $totalOrders;
            }


            return [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'avg_order_value' => $avgOrderValue,
                'recent_orders' => $recentOrders
            ];
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_spent' => 0,
                'avg_order_value' => 0,
                'recent_orders' => []
            ];
        }
    }

    /**
     * Get chart data for order amount vs date
     * @return array
     */
    private function getChartData(): array
    {
        try {
            $orders = $this->orderModel->getUserOrders($this->customerId);

            $chartData = [];

            // Group orders by date
            foreach ($orders as $order) {
                $date = date('Y-m-d', strtotime($order['created_at']));
                $amount = floatval($order['grand_total']);

                if (!isset($chartData[$date])) {
                    $chartData[$date] = 0;
                }

                $chartData[$date] += $amount;
            }

            // Sort by date
            ksort($chartData);

            // Format for Chart.js
            $labels = array_keys($chartData);
            $data = array_values($chartData);

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (Exception $e) {
            error_log("Chart data error: " . $e->getMessage());
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }

    /**
     * Handle AJAX request for chart data
     */
    public function handleAjax()
    {
        if (!isset($_GET['ajax']) || $_GET['ajax'] !== 'true') {
            return;
        }

        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_chart_data':
                $this->sendChartData();
                break;
            case 'get_stats':
                $this->sendStats();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        exit;
    }

    /**
     * Send chart data as JSON
     */
    private function sendChartData()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'chartData' => $this->chartData
        ]);
    }

    /**
     * Send stats as JSON
     */
    private function sendStats()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stats' => $this->stats
        ]);
    }

    /**
     * Get data for view
     * @return array
     */
    public function getViewData(): array
    {
        return [
            'stats' => $this->stats,
            'chartData' => $this->chartData,
            'customerId' => $this->customerId
        ];
    }
}
