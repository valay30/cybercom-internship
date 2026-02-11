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
     * Get chart data filtered by date range
     */
    private function getChartDataByFilter($filter, $startDate = null, $endDate = null)
    {
        try {
            require_once __DIR__ . '/../core/QueryBuilder.php';
            require_once __DIR__ . '/../../config/database.php';

            $qb = new QueryBuilder();
            $query = $qb->table('sales_order')
                ->where('customer_id', $this->customerId);

            if ($filter === 'month') {
                // Last 30 days
                $query->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')));
            } elseif ($filter === 'year') {
                // Last 12 months (grouped by month)
                $query->where('created_at', '>=', date('Y-m-d', strtotime('-1 year')));
            } elseif ($filter === 'custom' && $startDate && $endDate) {
                $query->where('created_at', '>=', $startDate . ' 00:00:00')
                    ->where('created_at', '<=', $endDate . ' 23:59:59');
            }

            $orders = $query->orderBy('created_at', 'ASC')->get();

            $chartData = [];

            if ($filter === 'year') {
                // Initialize last 12 months with 0
                for ($i = 11; $i >= 0; $i--) {
                    $monthKey = date('Y-m', strtotime("-$i months"));
                    $chartData[$monthKey] = 0;
                }

                foreach ($orders as $order) {
                    $monthKey = date('Y-m', strtotime($order['created_at']));
                    if (isset($chartData[$monthKey])) {
                        $chartData[$monthKey] += floatval($order['grand_total']);
                    }
                }

                // Format labels nicely
                $labels = [];
                foreach (array_keys($chartData) as $ym) {
                    $labels[] = date('M Y', strtotime($ym . '-01'));
                }
                $data = array_values($chartData);
            } else {
                // Detail by day for month/custom view
                foreach ($orders as $order) {
                    $date = date('Y-m-d', strtotime($order['created_at']));
                    if (!isset($chartData[$date])) {
                        $chartData[$date] = 0;
                    }
                    $chartData[$date] += floatval($order['grand_total']);
                }

                // Sort by date just in case
                ksort($chartData);
                $labels = array_keys($chartData);
                $data = array_values($chartData);
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (Exception $e) {
            error_log("Chart filter error: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
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
                $filter = $_GET['filter'] ?? 'month';
                $start = $_GET['start'] ?? null;
                $end = $_GET['end'] ?? null;
                $data = $this->getChartDataByFilter($filter, $start, $end);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'chartData' => $data]);
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
