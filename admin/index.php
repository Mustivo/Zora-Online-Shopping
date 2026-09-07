<?php
require_once 'includes/header.php';

// 1-4: Top KPIs
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'] ?? 0;
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as c FROM orders WHERE status != 'Cancelled'"))['c'] ?? 0;

$total_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as c FROM products"))['c'] ?? 0;
$total_out_of_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE stock <= 0"))['c'] ?? 0;

// 5. Recent Orders
$recent_orders = mysqli_query($conn, "SELECT id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");

// 6. Sales Chart Data
$sales_query = mysqli_query($conn, "SELECT DATE_FORMAT(created_at, '%b') as m, SUM(total_amount) as t FROM orders WHERE status != 'Cancelled' GROUP BY MONTH(created_at), m ORDER BY MAX(created_at) DESC LIMIT 6");
$chart_labels = [];
$chart_data = [];
while($row = mysqli_fetch_assoc($sales_query)) {
    $chart_labels[] = $row['m'];
    $chart_data[] = (float)$row['t'];
}
$chart_labels = array_reverse($chart_labels);
$chart_data = array_reverse($chart_data);

// 7. Low Stock Products
$low_stock = mysqli_query($conn, "SELECT name, stock, image FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");

// 8. Pending Deliveries
$pending_deliveries = mysqli_query($conn, "SELECT id, order_number, total_amount, status, created_at FROM orders WHERE status IN ('Pending', 'Processing') ORDER BY created_at ASC LIMIT 5");

// 9. Notifications (Dynamic Feed)
$notifs = [];
$n_orders = mysqli_query($conn, "SELECT id, order_number, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
while($r = mysqli_fetch_assoc($n_orders)) {
    $notifs[] = ['type' => 'order', 'icon' => 'fas fa-shopping-cart text-primary', 'msg' => "New order " . ($r['order_number'] ?: "#{$r['id']}") . " placed.", 'time' => $r['created_at']];
}
$n_users = mysqli_query($conn, "SELECT first_name, last_name, created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5");
while($r = mysqli_fetch_assoc($n_users)) {
    $notifs[] = ['type' => 'user', 'icon' => 'fas fa-user-plus text-success', 'msg' => "New user {$r['first_name']} registered.", 'time' => $r['created_at']];
}
usort($notifs, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
$notifs = array_slice($notifs, 0, 5);

// 11. Recent Customers
$recent_customers = mysqli_query($conn, "SELECT first_name, last_name, email, created_at FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5");

// 12. Top Selling Products
$top_selling = mysqli_query($conn, "SELECT p.name, p.image, SUM(oi.quantity) as sold FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5");

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        if ($diff->d > 0) return $diff->d . "d ago";
        if ($diff->h > 0) return $diff->h . "h ago";
        if ($diff->i > 0) return $diff->i . "m ago";
        return "Just now";
    }
}
if (!function_exists('status_color')) {
    function status_color($status) {
        switch(strtolower($status)) {
            case 'pending': return 'warning';
            case 'processing': return 'info';
            case 'shipped': return 'primary';
            case 'delivered': return 'success';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
.dashboard-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    height: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.kpi-card {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    padding: 1.5rem;
    background: var(--card);
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    position: relative;
    overflow: hidden;
}
.kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; width: 100%; height: 4px;
    background: var(--primary);
    opacity: 0.8;
}
.kpi-card.accent::after { background: var(--accent); }
.kpi-icon {
    width: 60px; height: 60px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
}
.kpi-icon.primary { background: rgba(1, 42, 94, 0.1); color: var(--primary); }
.kpi-icon.accent { background: rgba(251, 124, 0, 0.1); color: var(--accent); }
.kpi-icon.success { background: rgba(76, 175, 125, 0.1); color: var(--success); }
.kpi-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
.kpi-value { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--primary); line-height: 1; margin-bottom: 0.2rem; }
.kpi-label { font-size: 0.75rem; color: var(--text3); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

.section-head { font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 1.2rem; border-bottom: 2px solid rgba(1, 42, 94, 0.1); padding-bottom: 0.5rem; }

.list-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
.list-item:last-child { border-bottom: none; }
.list-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: var(--bg3); }
.list-info { flex: 1; }
.list-title { font-size: 0.85rem; font-weight: 600; color: var(--primary); margin-bottom: 2px; }
.list-sub { font-size: 0.75rem; color: var(--text3); }

.quick-action-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; background: var(--bg3); border: 1px solid var(--border); border-radius: 12px; padding: 1.2rem; color: var(--primary); text-decoration: none; transition: all 0.2s; font-weight: 600; font-size: 0.8rem; text-align: center; }
.quick-action-btn i { font-size: 1.5rem; color: var(--accent); }
.quick-action-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); transform: translateY(-3px); }
.quick-action-btn:hover i { color: #fff; }
</style>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-tachometer-alt me-2 text-accent"></i> Dashboard Overview</h2>
</div>

<!-- 1-4: TOP KPIs -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="fas fa-box"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_products) ?></div>
                <div class="kpi-label">Total Products</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card accent">
            <div class="kpi-icon accent"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_orders) ?></div>
                <div class="kpi-label">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="fas fa-users"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_customers) ?></div>
                <div class="kpi-label">Total Customers</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card accent">
            <div class="kpi-icon info"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_revenue, 0) ?></div>
                <div class="kpi-label">Total Revenue (RFW)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 6. SALES CHART -->
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="section-head">Revenue Analytics</div>
            <div id="salesChart" style="min-height: 300px;"></div>
        </div>
    </div>
    <!-- 10. QUICK ACTIONS -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="section-head">Quick Actions</div>
            <div class="row g-3">
                <div class="col-6"><a href="products.php" class="quick-action-btn"><i class="fas fa-plus-circle"></i> Add Product</a></div>
                <div class="col-6"><a href="categories.php" class="quick-action-btn"><i class="fas fa-tags"></i> Add Category</a></div>
                <div class="col-6"><a href="orders.php" class="quick-action-btn"><i class="fas fa-eye"></i> View Orders</a></div>
                <div class="col-6"><a href="settings.php" class="quick-action-btn"><i class="fas fa-cog"></i> Settings</a></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 5. RECENT ORDERS -->
    <div class="col-lg-8">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <div class="section-head border-0 mb-0 pb-0">Recent Orders</div>
                <a href="orders.php" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table  align-middle">
                    <thead class="text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <tr><th>#</th><th>Order ID</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                            <?php $row_count = 1; while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                                <td class="fw-bold" style="color: var(--primary);">#ORD-<?= sprintf('%04d', $order['id']) ?></td>
                                <td style="font-size: 0.85rem;"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td class="fw-bold" style="color: var(--accent);"><?= number_format($order['total_amount'], 2) ?> FRW</td>
                                <td><span class="badge bg-<?= status_color($order['status']) ?> bg-opacity-25 text-<?= status_color($order['status']) ?>"><?= $order['status'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- 9. NOTIFICATIONS -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="section-head">Activity Feed</div>
            <?php if(count($notifs) > 0): ?>
                <?php foreach($notifs as $n): ?>
                <div class="list-item">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg3); display: flex; align-items: center; justify-content: center;"><i class="<?= $n['icon'] ?>"></i></div>
                    <div class="list-info">
                        <div class="list-title fw-normal" style="color: var(--text);"><?= $n['msg'] ?></div>
                        <div class="list-sub"><i class="far fa-clock me-1"></i><?= time_elapsed_string($n['time']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-bell-slash fa-2x mb-2"></i><br>No recent activity</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 12. TOP SELLING PRODUCTS -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="section-head">Top Selling Products</div>
            <?php if(mysqli_num_rows($top_selling) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($top_selling)): ?>
                <div class="list-item">
                    <img src="../uploads/<?= $p['image'] ?>" class="list-img" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <div class="list-info">
                        <div class="list-title text-truncate" style="max-width: 150px;"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="list-sub" style="color: var(--accent); fw-bold"><?= $p['sold'] ?> units sold</div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3">Not enough data.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- 7. LOW STOCK PRODUCTS -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="section-head">Low Stock Alerts</div>
            <?php if(mysqli_num_rows($low_stock) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($low_stock)): ?>
                <div class="list-item">
                    <img src="../uploads/<?= $p['image'] ?>" class="list-img" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <div class="list-info">
                        <div class="list-title text-truncate" style="max-width: 150px;"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="list-sub text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> Only <?= $p['stock'] ?> left</div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><br>Stock levels are good!</div>
            <?php endif; ?>
        </div>
    </div>
    <!-- 8. PENDING DELIVERIES -->
    <div class="col-lg-4">
        <div class="dashboard-card">
            <div class="section-head">Pending Deliveries</div>
            <?php if(mysqli_num_rows($pending_deliveries) > 0): ?>
                <?php while($o = mysqli_fetch_assoc($pending_deliveries)): ?>
                <div class="list-item">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(251, 124, 0, 0.1); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-truck"></i></div>
                    <div class="list-info">
                        <div class="list-title">Order <?= $o['order_number'] ?: '#' . $o['id'] ?></div>
                        <div class="list-sub"><?= date('M j', strtotime($o['created_at'])) ?> &bull; <span class="text-<?= status_color($o['status']) ?> fw-bold"><?= $o['status'] ?></span></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-box-open fa-2x mb-2"></i><br>No pending deliveries.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 11. RECENT CUSTOMERS -->
    <div class="col-lg-12">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <div class="section-head border-0 mb-0 pb-0">Recent Customers</div>
                <a href="customers.php" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table  align-middle mb-0">
                    <thead class="text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <tr><th>#</th><th>Name</th><th>Email</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($recent_customers) > 0): ?>
                            <?php $row_count2 = 1; while($c = mysqli_fetch_assoc($recent_customers)): ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $row_count2++ ?></td>
                                <td class="fw-bold" style="color: var(--primary);"><i class="fas fa-user-circle me-2 text-muted"></i><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td style="font-size: 0.85rem;"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No customers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const chartLabels = <?= json_encode($chart_labels) ?>;
const chartData = <?= json_encode($chart_data) ?>;

var options = {
    series: [{
        name: 'Revenue',
        data: chartData
    }],
    chart: {
        type: 'area',
        height: 320,
        fontFamily: "'DM Sans', sans-serif",
        toolbar: { show: false },
        animations: { enabled: true, easing: 'easeinout', speed: 800 }
    },
    colors: ['#012a5e'], // Zora primary color
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 90, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: chartLabels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } }
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8', fontSize: '12px' },
            formatter: (value) => { return value.toLocaleString() + ' FRW' }
        }
    },
    grid: {
        borderColor: 'var(--border)',
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 0, bottom: 0, left: 10 }
    },
    tooltip: {
        theme: 'light',
        y: { formatter: function (val) { return val.toLocaleString() + " FRW" } },
        marker: { show: false }
    },
    markers: {
        size: 5,
        colors: ['#fb7c00'], // Zora accent color
        strokeColors: '#fff',
        strokeWidth: 2,
        hover: { size: 7 }
    }
};

var chart = new ApexCharts(document.querySelector("#salesChart"), options);
chart.render();
</script>

<?php require_once 'includes/footer.php'; ?>
