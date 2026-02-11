/**
 * Dashboard JavaScript
 * Handles chart rendering and dynamic data updates
 */

document.addEventListener('DOMContentLoaded', function () {
    initializeChart();
});

/**
 * Initialize the order chart
 */
function initializeChart() {
    const canvas = document.getElementById('orderChart');
    if (!canvas) return;

    // Get chart data from PHP (embedded in page)
    const chartDataElement = document.getElementById('chartData');
    let chartData;

    if (chartDataElement) {
        chartData = JSON.parse(chartDataElement.textContent);
    } else {
        // Fallback: fetch via AJAX
        fetchChartData();
        return;
    }

    renderChart(chartData);
}

/**
 * Fetch chart data via AJAX
 */
function fetchChartData() {
    fetch('dashboard?ajax=true&action=get_chart_data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderChart(data.chartData);
            }
        })
        .catch(error => {
            console.error('Error fetching chart data:', error);
        });
}

/**
 * Render the chart using Chart.js
 */
function renderChart(chartData) {
    const ctx = document.getElementById('orderChart');
    if (!ctx) return;

    // Format dates for better display
    const labels = chartData.labels.map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Order Amount (₹)',
                data: chartData.data,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#4f46e5',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            family: "'Inter', sans-serif"
                        },
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        family: "'Inter', sans-serif"
                    },
                    bodyFont: {
                        size: 13,
                        family: "'Inter', sans-serif"
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function (context) {
                            return '₹' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '₹' + value.toLocaleString();
                        },
                        font: {
                            size: 12,
                            family: "'Inter', sans-serif"
                        },
                        color: '#64748b'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12,
                            family: "'Inter', sans-serif"
                        },
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 0
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

/**
 * Refresh dashboard stats (optional feature)
 */
function refreshStats() {
    fetch('dashboard?ajax=true&action=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.stats);
            }
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
        });
}

/**
 * Update stats display with new data
 */
function updateStatsDisplay(stats) {
    // Update total orders
    const totalOrdersEl = document.querySelector('.stat-card:nth-child(1) .stat-value');
    if (totalOrdersEl) {
        totalOrdersEl.textContent = stats.total_orders;
    }

    // Update total spent
    const totalSpentEl = document.querySelector('.stat-card:nth-child(2) .stat-value');
    if (totalSpentEl) {
        totalSpentEl.textContent = '₹' + parseFloat(stats.total_spent).toFixed(2);
    }

    // Update average order value
    const avgOrderEl = document.querySelector('.stat-card:nth-child(3) .stat-value');
    if (avgOrderEl) {
        avgOrderEl.textContent = '₹' + parseFloat(stats.avg_order_value).toFixed(2);
    }
}
