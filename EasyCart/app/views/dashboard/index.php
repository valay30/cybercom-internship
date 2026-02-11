<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EasyCart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>My Dashboard</h1>
            <p class="subtitle">Track your orders and spending</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Total Orders Card -->
            <div class="stat-card">
                <div class="stat-icon orders-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Orders</p>
                    <h2 class="stat-value"><?php echo $stats['total_orders']; ?></h2>
                </div>
            </div>

            <!-- Total Spent Card -->
            <div class="stat-card">
                <div class="stat-icon spent-icon">
                    <i class="fa-solid fa-indian-rupee-sign" style="font-size: 24px;"></i>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Total Amount Spent</p>
                    <h2 class="stat-value">₹<?php echo number_format($stats['total_spent'], 2); ?></h2>
                </div>
            </div>

            <!-- Average Order Value Card -->
            <div class="stat-card">
                <div class="stat-icon avg-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <p class="stat-label">Average Order Value</p>
                    <h2 class="stat-value">₹<?php echo number_format($stats['avg_order_value'], 2); ?></h2>
                </div>
            </div>


        </div>

        <!-- Chart Section -->
        <div class="chart-section">
            <div class="chart-header">
                <div>
                    <h2>Order Amount vs Order Date</h2>
                    <p class="chart-subtitle">Track your spending over time</p>
                </div>
                <div class="chart-controls">
                    <div class="select-wrapper">
                        <i class="fa-solid fa-calendar-days select-icon"></i>
                        <select id="chartFilter" onchange="toggleDateInputs()" class="chart-filter-select">
                            <option value="month">Last 30 Days</option>
                            <option value="year">Last 12 Months</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div id="customDateInputs" class="custom-date-container" style="display:none;">
                        <div class="date-input-group">
                            <input type="date" id="startDate" class="date-input" placeholder="Start Date">
                        </div>
                        <span class="date-separator">to</span>
                        <div class="date-input-group">
                            <input type="date" id="endDate" class="date-input" placeholder="End Date">
                        </div>
                        <button onclick="applyCustomFilter()" class="apply-btn">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="orderChart"></canvas>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="orders" class="view-all-link">View All →</a>
            </div>
            <div class="orders-table-container">
                <?php if (!empty($stats['recent_orders'])): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_orders'] as $order): ?>
                                <tr>
                                    <td class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo $order['item_count'] ?? 0; ?> items</td>
                                    <td class="order-total">₹<?php echo number_format($order['grand_total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $order['order_status'] ?? 'pending'; ?>">
                                            <?php echo ucfirst($order['order_status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <h3>No Orders Yet</h3>
                        <p>Start shopping to see your orders here</p>
                        <a href="plp" class="btn-primary">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../common/footer.php'; ?>

    <!-- Embed chart data for JavaScript -->
    <script id="chartData" type="application/json">
        <?php echo json_encode($chartData); ?>
    </script>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>