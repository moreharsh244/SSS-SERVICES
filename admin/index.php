<?php
include('header.php');
echo '<div class="admin-page-wrapper" style="display:flex;flex-direction:column;min-height:100vh;">';

// Get current date info
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

// Calculate Daily Revenue (completed orders only)
$daily_sql = "SELECT SUM(pprice * qty) as total, COUNT(*) as orders FROM purchase WHERE DATE(pdate) = '$today' AND delivery_status = 'delivered'";
$daily_result = mysqli_query($con, $daily_sql);
$daily_data = mysqli_fetch_assoc($daily_result);
$daily_revenue = $daily_data['total'] ?? 0;
$daily_orders = $daily_data['orders'] ?? 0;

// Calculate Weekly Revenue
$weekly_sql = "SELECT SUM(pprice * qty) as total, COUNT(*) as orders FROM purchase WHERE DATE(pdate) >= '$week_start' AND DATE(pdate) <= '$week_end' AND delivery_status = 'delivered'";
$weekly_result = mysqli_query($con, $weekly_sql);
$weekly_data = mysqli_fetch_assoc($weekly_result);
$weekly_revenue = $weekly_data['total'] ?? 0;
$weekly_orders = $weekly_data['orders'] ?? 0;

// Calculate Monthly Revenue
$monthly_sql = "SELECT SUM(pprice * qty) as total, COUNT(*) as orders FROM purchase WHERE DATE(pdate) >= '$month_start' AND DATE(pdate) <= '$month_end' AND delivery_status = 'delivered'";
$monthly_result = mysqli_query($con, $monthly_sql);
$monthly_data = mysqli_fetch_assoc($monthly_result);
$monthly_revenue = $monthly_data['total'] ?? 0;
$monthly_orders = $monthly_data['orders'] ?? 0;

// Get Pending Revenue (orders not yet delivered)
$pending_sql = "SELECT SUM(pprice * qty) as total, COUNT(*) as orders FROM purchase WHERE delivery_status != 'delivered' AND delivery_status != 'cancelled'";
$pending_result = mysqli_query($con, $pending_sql);
$pending_data = mysqli_fetch_assoc($pending_result);
$pending_revenue = $pending_data['total'] ?? 0;
$pending_orders = $pending_data['orders'] ?? 0;

// Top Selling Products (by quantity)
$top_products_sql = "SELECT p.pname, 
                            COALESCE(pr.pcompany, 'N/A') as pcompany,
                            SUM(p.qty) as total_qty, 
                            SUM(p.pprice * p.qty) as total_revenue,
                            COUNT(p.pid) as order_count
                     FROM purchase p
                     LEFT JOIN products pr ON p.prod_id = pr.pid
                     WHERE p.delivery_status = 'delivered'
                     GROUP BY p.pname, pr.pcompany
                     ORDER BY total_qty DESC
                     LIMIT 10";
$top_products_result = mysqli_query($con, $top_products_sql);
$top_products = [];
while($row = mysqli_fetch_assoc($top_products_result)){
    $top_products[] = $row;
}

// Revenue by Product
$product_revenue_sql = "SELECT p.pname, 
                               SUM(p.pprice * p.qty) as revenue,
                               SUM(p.qty) as qty
                        FROM purchase p
                        WHERE p.delivery_status = 'delivered'
                        GROUP BY p.pname
                        ORDER BY revenue DESC
                        LIMIT 8";
$product_revenue_result = mysqli_query($con, $product_revenue_sql);
$product_revenues = [];
while($row = mysqli_fetch_assoc($product_revenue_result)){
    $product_revenues[] = $row;
}

// Last 7 Days Revenue Trend
$daily_trend = [];
for($i = 6; $i >= 0; $i--){
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_label = date('M d', strtotime("-$i days"));
    $trend_sql = "SELECT COALESCE(SUM(pprice * qty), 0) as revenue FROM purchase WHERE DATE(pdate) = '$date' AND delivery_status = 'delivered'";
    $trend_result = mysqli_query($con, $trend_sql);
    $trend_data = mysqli_fetch_assoc($trend_result);
    $daily_trend[] = ['date' => $date_label, 'revenue' => $trend_data['revenue'] ?? 0];
}

// Recent Completed Orders
$recent_orders_sql = "SELECT p.pid, p.pname, p.user, p.pprice, p.qty, 
                             (p.pprice * p.qty) as total, p.pdate, p.delivery_status
                      FROM purchase p
                      WHERE p.delivery_status = 'delivered'
                      ORDER BY p.pdate DESC
                      LIMIT 10";
$recent_orders_result = mysqli_query($con, $recent_orders_sql);
$recent_orders = [];
while($row = mysqli_fetch_assoc($recent_orders_result)){
    $recent_orders[] = $row;
}
?>

<style>
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
        padding-bottom: 50px;
    }
    .stats-card {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.12);
        border: 1px solid #bfdbfe;
        height: 100%;
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(30, 64, 175, 0.18);
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
    }
    .revenue-amount {
        font-size: 2rem;
        font-weight: 800;
        margin: 0.5rem 0;
        background: linear-gradient(135deg, #7c3aed 0%, #0ea5e9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .chart-container {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.5);
        margin-bottom: 2rem;
    }
    .table-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .table thead th {
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 1rem;
    }
    .table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }
    .badge-rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .rank-1 { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; }
    .rank-2 { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); color: white; }
    .rank-3 { background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%); color: white; }
    .rank-other { background: #e2e8f0; color: #64748b; }
    .section-title {
        font-weight: 800;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: #1e293b;
    }
    .metric-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .metric-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
    }
</style>

<div class="container-fluid px-4 py-4">
    
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="display-5 fw-bold text-dark mb-2">
            <i class="bi bi-graph-up-arrow me-2"></i>Sales Analytics Dashboard
        </h1>
        <p class="text-muted mb-0">Monitor revenue, track performance, and analyze sales trends</p>
    </div>

    <!-- Revenue Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Daily Revenue -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Today's Revenue</div>
                        <div class="revenue-amount">₹<?php echo number_format($daily_revenue, 2); ?></div>
                        <div class="small text-muted">
                            <i class="bi bi-cart-check me-1"></i><?php echo $daily_orders; ?> orders
                        </div>
                    </div>
                    <div class="stats-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Revenue -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">This Week</div>
                        <div class="revenue-amount">₹<?php echo number_format($weekly_revenue, 2); ?></div>
                        <div class="small text-muted">
                            <i class="bi bi-cart-check me-1"></i><?php echo $weekly_orders; ?> orders
                        </div>
                    </div>
                    <div class="stats-icon" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">This Month</div>
                        <div class="revenue-amount">₹<?php echo number_format($monthly_revenue, 2); ?></div>
                        <div class="small text-muted">
                            <i class="bi bi-cart-check me-1"></i><?php echo $monthly_orders; ?> orders
                        </div>
                    </div>
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Revenue -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="metric-label">Pending Revenue</div>
                        <div class="revenue-amount">₹<?php echo number_format($pending_revenue, 2); ?></div>
                        <div class="small text-muted">
                            <i class="bi bi-hourglass-split me-1"></i><?php echo $pending_orders; ?> orders
                        </div>
                    </div>
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-12 col-lg-8">
            <div class="chart-container">
                <h3 class="section-title">
                    <i class="bi bi-graph-up me-2"></i>7-Day Revenue Trend
                </h3>
                <canvas id="revenueTrendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Top Products Pie Chart -->
        <div class="col-12 col-lg-4">
            <div class="chart-container">
                <h3 class="section-title">
                    <i class="bi bi-pie-chart-fill me-2"></i>Top Products
                </h3>
                <canvas id="topProductsChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Selling Products Table -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="table-container">
                <h3 class="section-title">
                    <i class="bi bi-trophy-fill me-2"></i>Best Selling Products
                </h3>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(empty($top_products)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No sales data available yet
                                    </td>
                                </tr>
                            <?php else:
                                foreach($top_products as $index => $product): 
                                $rank = $index + 1;
                                $rank_class = $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : 'rank-other'));
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge-rank <?php echo $rank_class; ?>">
                                            <?php echo $rank; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['pname']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($product['pcompany']); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-3">
                                            <?php echo number_format($product['total_qty']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹<?php echo number_format($product['total_revenue'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; 
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Completed Orders -->
        <div class="col-12 col-xl-6">
            <div class="table-container">
                <h3 class="section-title">
                    <i class="bi bi-clock-history me-2"></i>Recent Completed Orders
                </h3>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No completed orders yet
                                    </td>
                                </tr>
                            <?php else:
                                foreach($recent_orders as $order): 
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">#<?php echo $order['pid']; ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['pname']); ?></div>
                                        <div class="small text-muted">
                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($order['user']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-white rounded-pill">
                                            <?php echo $order['qty']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹<?php echo number_format($order['total'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Trend Chart
const trendData = <?php echo json_encode($daily_trend); ?>;
const trendLabels = trendData.map(d => d.date);
const trendValues = trendData.map(d => parseFloat(d.revenue));

const trendCtx = document.getElementById('revenueTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Revenue (₹)',
            data: trendValues,
            borderColor: 'rgba(102, 126, 234, 1)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(102, 126, 234, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: { size: 13, weight: '600' },
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 },
                callbacks: {
                    label: function(context) {
                        return 'Revenue: ₹' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    },
                    font: { size: 12 }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                ticks: { font: { size: 12 } },
                grid: { display: false }
            }
        }
    }
});

// Top Products Pie Chart
const productData = <?php echo json_encode($product_revenues); ?>;
const productLabels = productData.map(p => p.pname.length > 20 ? p.pname.substring(0, 20) + '...' : p.pname);
const productValues = productData.map(p => parseFloat(p.revenue));

const colorPalette = [
    'rgba(102, 126, 234, 0.8)',
    'rgba(236, 72, 153, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(16, 185, 129, 0.8)',
    'rgba(139, 92, 246, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(14, 165, 233, 0.8)',
    'rgba(251, 191, 36, 0.8)'
];

const pieCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: productLabels,
        datasets: [{
            data: productValues,
            backgroundColor: colorPalette,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    font: { size: 11 },
                    padding: 10,
                    boxWidth: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: { size: 13, weight: 'bold' },
                bodyFont: { size: 12 },
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ₹' + value.toFixed(2) + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>

<?php include(__DIR__ . '/footer.php'); ?>
</div>