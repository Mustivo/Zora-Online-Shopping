<?php
require_once 'includes/header.php';

// Ensure payments table exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(100) NOT NULL DEFAULT 'Cash on Delivery',
    transaction_id VARCHAR(100) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Paid',
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Auto-sync any delivered orders into payments table
mysqli_query($conn, "INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status, payment_date, created_at)
SELECT id, user_id, total_amount, COALESCE(payment_method, 'Cash on Delivery'), CONCAT('TXN-', LPAD(id, 6, '0')), 'Paid', created_at, created_at
FROM orders 
WHERE LOWER(status) = 'delivered'
ON DUPLICATE KEY UPDATE amount = VALUES(amount), payment_method = VALUES(payment_method), status = 'Paid'");

// Filters
$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$method_filter = isset($_GET['method']) ? clean_input($conn, $_GET['method']) : '';
$start_date = isset($_GET['start_date']) ? clean_input($conn, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? clean_input($conn, $_GET['end_date']) : '';

$where = ["p.status = 'Paid'"];

if (!empty($search)) {
    $where[] = "(p.transaction_id LIKE '%$search%' OR p.order_id LIKE '%$search%' OR o.shipping_name LIKE '%$search%' OR o.shipping_phone LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";
}
if (!empty($method_filter)) {
    $where[] = "p.payment_method = '$method_filter'";
}
if (!empty($start_date) && !empty($end_date)) {
    $where[] = "DATE(p.payment_date) BETWEEN '$start_date' AND '$end_date'";
}

$where_clause = implode(' AND ', $where);

// KPI Stats
$total_collected_q = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) as total, COUNT(id) as count, COALESCE(AVG(amount), 0) as avg_amt FROM payments WHERE status = 'Paid'");
$kpi_data = mysqli_fetch_assoc($total_collected_q);
$total_revenue = (float)($kpi_data['total'] ?? 0);
$total_paid_count = (int)($kpi_data['count'] ?? 0);
$avg_payment = (float)($kpi_data['avg_amt'] ?? 0);

$momo_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE status = 'Paid' AND (payment_method LIKE '%momo%' OR payment_method LIKE '%mobile%')"))['c'] ?? 0;
$card_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE status = 'Paid' AND (payment_method LIKE '%card%' OR payment_method LIKE '%visa%' OR payment_method LIKE '%master%')"))['c'] ?? 0;
$cash_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM payments WHERE status = 'Paid' AND (payment_method LIKE '%cash%' OR payment_method LIKE '%delivery%')"))['c'] ?? 0;

// Query payments list
$sql = "SELECT p.*, o.shipping_name, o.shipping_phone, o.guest_email, u.first_name, u.last_name, u.email as user_email 
        FROM payments p 
        LEFT JOIN orders o ON p.order_id = o.id 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE $where_clause 
        ORDER BY p.payment_date DESC";
$query = mysqli_query($conn, $sql);
?>

<div class="admin-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="admin-page-title"><i class="fas fa-credit-card me-2 text-accent"></i> Completed Payments</h2>
        <p class="text-muted small mb-0">Payments recorded automatically from delivered customer orders.</p>
    </div>
</div>

<!-- KPI SUMMARY CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card success">
            <div class="kpi-icon success"><i class="fas fa-coins"></i></div>
            <div>
                <div class="kpi-value text-success"><?= number_format($total_revenue, 0) ?> <span style="font-size: 0.9rem;">RFW</span></div>
                <div class="kpi-label">Total Revenue Collected</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card primary">
            <div class="kpi-icon primary"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="kpi-value text-primary"><?= number_format($total_paid_count) ?></div>
                <div class="kpi-label">Paid & Delivered Orders</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card accent">
            <div class="kpi-icon accent"><i class="fas fa-chart-pie"></i></div>
            <div>
                <div class="kpi-value text-accent"><?= number_format($avg_payment, 0) ?> <span style="font-size: 0.9rem;">RFW</span></div>
                <div class="kpi-label">Average Order Value</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card info">
            <div class="kpi-icon info"><i class="fas fa-mobile-alt"></i></div>
            <div>
                <div class="kpi-value text-info"><?= $momo_count ?> MoMo / <?= $cash_count ?> Cash</div>
                <div class="kpi-label">Payment Channels</div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="admin-card p-3 mb-4">
    <form method="GET" action="payments.php" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search Txn ID, Order #, Customer..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="method" class="form-select">
                <option value="">All Payment Methods</option>
                <option value="Momo Pay" <?= $method_filter == 'Momo Pay' ? 'selected' : '' ?>>MoMo Pay</option>
                <option value="Card Payment" <?= $method_filter == 'Card Payment' ? 'selected' : '' ?>>Card Payment</option>
                <option value="Cash on Delivery" <?= $method_filter == 'Cash on Delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" placeholder="Start Date">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" placeholder="End Date">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
            <?php if (!empty($search) || !empty($method_filter) || !empty($start_date)): ?>
                <a href="payments.php" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- PAYMENTS TABLE -->
<div class="admin-card overflow-hidden">
    <div class="table-responsive border-0">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Txn Reference</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Method</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($query && mysqli_num_rows($query) > 0):
                    while($row = mysqli_fetch_assoc($query)): 
                        $customer = $row['first_name'] ? ($row['first_name'] . ' ' . $row['last_name']) : ($row['shipping_name'] ?: ($row['guest_email'] ?: 'Customer'));
                        $phone = $row['shipping_phone'] ?: '';
                        
                        $method = $row['payment_method'];
                        $method_icon = 'fa-money-bill-wave';
                        $method_badge = 'bg-light text-dark border';
                        if (stripos($method, 'momo') !== false || stripos($method, 'mobile') !== false) {
                            $method_icon = 'fa-mobile-alt text-warning';
                            $method_badge = 'bg-warning text-dark';
                        } elseif (stripos($method, 'card') !== false) {
                            $method_icon = 'fa-credit-card text-info';
                            $method_badge = 'bg-info text-white';
                        }
                ?>
                <tr>
                    <td class="fw-bold" style="color: var(--primary);">
                        <code><?= htmlspecialchars($row['transaction_id'] ?: 'TXN-' . str_pad($row['id'], 6, '0', STR_PAD_LEFT)) ?></code>
                    </td>
                    <td>
                        <a href="orders.php?search=<?= $row['order_id'] ?>" class="badge bg-secondary text-white text-decoration-none">
                            #<?= htmlspecialchars($row['order_id']) ?>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold shadow-sm" style="width:32px;height:32px;font-size:0.85rem">
                                <?= strtoupper(substr($customer, 0, 1)) ?>
                            </div> 
                            <div>
                                <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($customer) ?></span>
                                <?php if($phone): ?><small class="text-muted"><?= htmlspecialchars($phone) ?></small><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $method_badge ?> px-2 py-1">
                            <i class="fas <?= $method_icon ?> me-1"></i> <?= htmlspecialchars($method) ?>
                        </span>
                    </td>
                    <td class="fw-bold fs-6 text-success">
                        <?= number_format($row['amount'], 0) ?> RFW
                    </td>
                    <td>
                        <span class="badge bg-success px-2 py-1 shadow-sm">
                            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td class="text-muted small">
                        <?= date('M d, Y H:i', strtotime($row['payment_date'])) ?>
                    </td>
                    <td class="text-end">
                        <a href="orders.php?search=<?= $row['order_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Order Details">
                            <i class="fas fa-eye me-1"></i> View Order
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-receipt fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                        <h5>No payment records found</h5>
                        <p class="small mb-0">Payments are automatically recorded when order status is marked as <strong>Delivered</strong>.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
