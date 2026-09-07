<?php
<<<<<<< HEAD
require_once '../core/config.php';

// Date filtering logic
$start_date = isset($_GET['start_date']) ? clean_input($conn, $_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? clean_input($conn, $_GET['end_date']) : date('Y-m-d');
$status_filter = isset($_GET['status_filter']) ? clean_input($conn, $_GET['status_filter']) : '';

$date_condition = "DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
if (!empty($status_filter)) {
    $date_condition .= " AND LOWER(status) = LOWER('$status_filter')";
} else {
    $date_condition .= " AND LOWER(status) != 'cancelled'";
}

// Export Excel logic
if (isset($_GET['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Sales_Report_" . $start_date . "_to_" . $end_date . ".xls");
    
    $logo_url = BASE_URL . "uploads/logo.png";
    
    echo '<style>
        table { font-family: "Times New Roman", Times, serif; border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background-color: #2563eb; color: white; font-weight: bold; font-size: 11pt; }
        td { font-size: 11pt; color: #000; }
        .total-row td { font-weight: bold; font-size: 12pt; background-color: #f1f3f5; color: #0b3d6e; }
        .total-amount { color: #2563eb; }
    </style>';
    
    echo '<table>';
    echo '<tr><th colspan="9" style="text-align: center; padding: 15px; background-color: white; border-bottom: 3px solid #2563eb;"><img src="' . $logo_url . '" style="height: 50px;"><br><br><span style="font-size: 16pt; color: #0b3d6e;">ZORA SALES REPORT</span><br><span style="font-size: 11pt; font-weight: normal; color: #555;">' . $start_date . ' to ' . $end_date . ($status_filter ? ' (' . $status_filter . ')' : ' (All Statuses)') . '</span></th></tr>';
    echo '<tr><th>Order ID</th><th>Date</th><th>Customer Name</th><th>Contact</th><th>Payment Method</th><th>Product</th><th>Quantity</th><th>Price (RFW)</th><th>Total (RFW)</th></tr>';
    
    $export_sql = "SELECT o.id, o.created_at, o.shipping_name, o.shipping_phone, o.payment_method, COALESCE(p.name, CONCAT('Product #', oi.product_id)) as product_name, oi.quantity, oi.price, (oi.quantity * oi.price) as subtotal FROM order_items oi JOIN orders o ON oi.order_id = o.id LEFT JOIN products p ON oi.product_id = p.id WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
    if (!empty($status_filter)) {
        $export_sql .= " AND LOWER(o.status) = LOWER('$status_filter')";
    } else {
        $export_sql .= " AND LOWER(o.status) != 'cancelled'";
    }
    $export_sql .= " ORDER BY o.created_at DESC";
    
    $export_q = mysqli_query($conn, $export_sql);
    $grand_total = 0;
    while ($row = mysqli_fetch_assoc($export_q)) {
        $grand_total += $row['subtotal'];
        echo '<tr>';
        echo '<td>#'.$row['id'].'</td>';
        echo '<td>'.date('Y-m-d H:i', strtotime($row['created_at'])).'</td>';
        echo '<td>'.$row['shipping_name'].'</td>';
        echo '<td>'.$row['shipping_phone'].'</td>';
        echo '<td>'.$row['payment_method'].'</td>';
        echo '<td>'.$row['product_name'].'</td>';
        echo '<td>'.$row['quantity'].'</td>';
        echo '<td>'.$row['price'].'</td>';
        echo '<td>'.$row['subtotal'].'</td>';
        echo '</tr>';
    }
    echo '<tr class="total-row"><td colspan="8" style="text-align:right;">Grand Total:</td><td class="total-amount">'.number_format($grand_total, 0).'</td></tr>';
    echo '</table>';
    exit;
}

require_once 'includes/header.php';

// Calculate filtered stats
$total_sales_q = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled' AND $date_condition");
$total_sales = mysqli_fetch_assoc($total_sales_q)['total'] ?? 0;

$total_orders_q = mysqli_query($conn, "SELECT COUNT(id) as count FROM orders WHERE $date_condition");
$total_orders = mysqli_fetch_assoc($total_orders_q)['count'] ?? 0;

$total_customers_q = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM orders WHERE user_id IS NOT NULL AND $date_condition");
$total_customers = mysqli_fetch_assoc($total_customers_q)['count'] ?? 0;

// Daily sales for the chart based on filter
$sales_data = [];
$labels = [];
$chart_q = mysqli_query($conn, "SELECT DATE(created_at) as d, SUM(total_amount) as t FROM orders WHERE status != 'Cancelled' AND $date_condition GROUP BY DATE(created_at) ORDER BY d ASC");
while ($row = mysqli_fetch_assoc($chart_q)) {
    $labels[] = date('M d', strtotime($row['d']));
    $sales_data[] = (float)$row['t'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-chart-line me-2 text-accent"></i> Reports & Analytics</h2>
</div>

<!-- Filter & Export Form -->
<div class="admin-card p-4 mb-4" data-html2canvas-ignore="true">
    <form method="GET" action="reports.php" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label-custom">Start Date</label>
            <input type="date" name="start_date" class="form-input" value="<?= htmlspecialchars($start_date) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">End Date</label>
            <input type="date" name="end_date" class="form-input" value="<?= htmlspecialchars($end_date) ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label-custom">Status</label>
            <select name="status_filter" class="form-select">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $status_filter == 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $status_filter == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $status_filter == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-5 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Filter</button>
            <button type="submit" name="export_excel" value="1" class="btn btn-success"><i class="fas fa-file-excel me-2"></i>Export Excel</button>
            <button type="button" onclick="exportPDF()" class="btn btn-danger"><i class="fas fa-file-pdf me-2"></i>Export PDF</button>
        </div>
    </form>
</div>

<div id="pdf-content">

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-card p-4 text-center">
            <div class="text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Revenue (Filtered)</div>
            <h3 class="fw-bold text-success m-0"><?= number_format($total_sales, 0) ?> RFW</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-4 text-center">
            <div class="text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Orders (Filtered)</div>
            <h3 class="fw-bold text-primary m-0"><?= number_format($total_orders, 0) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-4 text-center">
            <div class="text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Active Customers (Filtered)</div>
            <h3 class="fw-bold text-info m-0"><?= number_format($total_customers, 0) ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Daily Sales Chart -->
    <div class="col-md-8">
        <div class="admin-card p-4 h-100">
            <h5 class="mb-4">Sales Trend (<?= date('M d', strtotime($start_date)) ?> - <?= date('M d', strtotime($end_date)) ?>)</h5>
            <?php if (empty($sales_data)): ?>
                <div class="text-center text-muted py-5"><i class="fas fa-chart-bar fa-3x mb-3 text-light"></i><br>No sales data for this period.</div>
            <?php else: ?>
                <div id="reportChart" style="min-height: 300px;"></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Top Selling Products Filtered -->
    <div class="col-md-4">
        <div class="admin-card p-4 h-100">
            <h5 class="mb-4">Top Products (Filtered)</h5>
            <ul class="list-group list-group-flush">
                <?php 
                $top_q = mysqli_query($conn, "SELECT COALESCE(p.name, CONCAT('Product #', oi.product_id)) as name, SUM(oi.quantity) as sold FROM order_items oi JOIN orders o ON oi.order_id = o.id LEFT JOIN products p ON oi.product_id = p.id WHERE LOWER(o.status) != 'cancelled' AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date' GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5");
                if (mysqli_num_rows($top_q) > 0):
                    $rank = 1;
                    while($tp = mysqli_fetch_assoc($top_q)):
                        $iconColor = 'text-muted';
                        $iconClass = 'fa-box';
                        $badgeBg = 'background: var(--bg3); color: var(--text);';
                        $textColor = 'var(--text)';
                        
                        if ($rank == 1) {
                            $iconColor = '';
                            $iconClass = 'fa-crown';
                            $badgeBg = 'background: linear-gradient(45deg, #FFD700, #FFA500); color: #000; box-shadow: 0 4px 10px rgba(255,215,0,0.3);'; // Gold
                            $textColor = '#FFD700';
                        } elseif ($rank == 2) {
                            $iconColor = '';
                            $iconClass = 'fa-medal';
                            $badgeBg = 'background: linear-gradient(45deg, #E0E0E0, #9E9E9E); color: #000; box-shadow: 0 4px 10px rgba(224,224,224,0.3);'; // Silver
                            $textColor = '#E0E0E0';
                        } elseif ($rank == 3) {
                            $iconColor = '';
                            $iconClass = 'fa-award';
                            $badgeBg = 'background: linear-gradient(45deg, #CD7F32, #A0522D); color: #fff; box-shadow: 0 4px 10px rgba(205,127,50,0.3);'; // Bronze
                            $textColor = '#CD7F32';
                        }
                ?>
                <li class="list-group-item bg-transparent px-0 border-0 mb-3 rounded" style="transition: all 0.2s; <?php if($rank==1) echo 'background: rgba(255, 215, 0, 0.05) !important; border-left: 3px solid #FFD700 !important; padding-left: 12px !important; margin-left: -12px; margin-right: -12px; padding-right: 12px;'; ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-truncate fw-bold" style="max-width: 180px; color: <?= $textColor ?>;">
                            <i class="fas <?= $iconClass ?> me-2 <?= $iconColor ?>" <?php if($rank<=3) echo 'style="color: '.$textColor.';"'; ?>></i>
                            <?= htmlspecialchars($tp['name']) ?>
                        </span>
                        <span class="badge rounded-pill" style="<?= $badgeBg ?> font-weight: 700; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.2);"><?= $tp['sold'] ?> sold</span>
                    </div>
                </li>
                <?php 
                        $rank++;
                    endwhile; 
                else: 
                ?>
                    <li class="list-group-item bg-transparent px-0 border-0 text-muted">No products sold in this period.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php
// Fetch records for the table
$table_sql = "SELECT o.id, o.created_at, o.shipping_name, o.shipping_phone, o.payment_method, COALESCE(p.name, CONCAT('Product #', oi.product_id)) as product_name, oi.id as order_item_id, oi.quantity, oi.price, (oi.quantity * oi.price) as subtotal FROM order_items oi JOIN orders o ON oi.order_id = o.id LEFT JOIN products p ON oi.product_id = p.id WHERE o.deleted_at IS NULL AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
// We allow seeing Cancelled if explicitly filtered, otherwise hide Cancelled by default
if (!empty($status_filter)) {
    $table_sql .= " AND LOWER(o.status) = LOWER('$status_filter')";
} else {
    $table_sql .= " AND LOWER(o.status) != 'cancelled'";
}
$table_sql .= " ORDER BY o.created_at DESC";

$orders_q = mysqli_query($conn, $table_sql);
$grand_total = 0;
?>
<div class="row mt-4">
    <div class="col-12">
        <form action="actions.php" method="POST" id="bulkReportsForm">
        <input type="hidden" name="action" value="bulk_delete_orders">
        <input type="hidden" name="redirect" value="reports.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status_filter=<?= urlencode($status_filter) ?>">
        
        <div id="bulkReportsBar" class="admin-card p-3 mb-3 d-none align-items-center justify-content-between bg-primary bg-opacity-10 border border-primary" data-html2canvas-ignore="true">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-primary fs-5"></i>
                <span class="fw-bold"><span id="reportsSelectedCount">0</span> record(s) selected</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-danger shadow-sm px-3" onclick="return customConfirm(event, 'Move selected orders to trash?')">
                    <i class="fas fa-trash-alt me-1"></i> Move Selected to Trash
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedReports()">
                    Deselect All
                </button>
            </div>
        </div>

        <div class="admin-card p-4" id="pdf-table-export">
            <div style="display:none;" id="pdf-header" class="mb-4 text-center">
                <img src="../uploads/logo.png" style="height: 50px; margin-bottom: 10px;">
                <h2 style="color: #0b3d6e; font-family: 'Times New Roman', Times, serif;">ZORA Sales Report</h2>
                <p style="font-family: 'Times New Roman', Times, serif;"><strong>Period:</strong> <?= date('M d, Y', strtotime($start_date)) ?> to <?= date('M d, Y', strtotime($end_date)) ?> <?= $status_filter ? ' (' . $status_filter . ')' : '' ?></p>
                <hr style="border-top: 3px solid #2563eb;">
            </div>
            <h5 class="mb-4" id="table-title">Order Records (Filtered)</h5>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="width: 40px;" data-html2canvas-ignore="true">
                                <input type="checkbox" class="form-check-input" id="selectAllReports" onchange="toggleSelectAllReports(this)" title="Select All Rows">
                            </th>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Payment Method</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price (RFW)</th>
                            <th>Total (RFW)</th>
                            <th class="text-end" style="width: 60px;" data-html2canvas-ignore="true">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($orders_q) > 0): ?>
                            <?php $row_count = 1; while($row = mysqli_fetch_assoc($orders_q)): 
                                $grand_total += $row['subtotal'];
                            ?>
                            <tr>
                                <td data-html2canvas-ignore="true">
                                    <input type="checkbox" class="form-check-input report-row-chk" name="order_ids[]" value="<?= $row['id'] ?>" onchange="updateReportsBulkToolbar()">
                                </td>
                                <td><?= $row_count++ ?></td>
                                <td>#<?= $row['id'] ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                                <td><?= htmlspecialchars($row['shipping_name']) ?></td>
                                <td><?= htmlspecialchars($row['shipping_phone']) ?></td>
                                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= number_format($row['price']) ?></td>
                                <td class="fw-bold"><?= number_format($row['subtotal']) ?></td>
                                <td class="text-end" data-html2canvas-ignore="true">
                                    <form action="actions.php" method="POST" class="d-inline m-0" onsubmit="return customConfirm(event, 'Move order #<?= $row['id'] ?> to trash?')">
                                        <input type="hidden" name="action" value="delete_order">
                                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="redirect" value="reports.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status_filter=<?= urlencode($status_filter) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Move Order to Trash"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="table-active">
                                <td colspan="10" class="text-end fw-bold" style="font-size: 1.1rem;">Grand Total:</td>
                                <td class="fw-bold" style="color: var(--primary); font-size: 1.1rem;"><?= number_format($grand_total) ?> RFW</td>
                                <td data-html2canvas-ignore="true"></td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="12" class="text-center text-muted">No records found for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </form>
    </div>
</div>

</div> <!-- End pdf-content -->

<style>
.pdf-export-mode table {
    font-family: 'Times New Roman', Times, serif !important;
    border: 1px solid #ddd !important;
}
.pdf-export-mode th, .pdf-export-mode td {
    font-family: 'Times New Roman', Times, serif !important;
    font-size: 9pt !important;
    padding: 4px !important;
    border: 1px solid #ddd !important;
    white-space: nowrap !important;
}
.pdf-export-mode th {
    background-color: #2563eb !important;
    color: white !important;
    -webkit-print-color-adjust: exact;
}
.pdf-export-mode .text-primary {
    color: #2563eb !important;
}
</style>
<script>
<?php if (!empty($sales_data)): ?>
const options = {
    series: [{
        name: 'Daily Revenue (RFW)',
        data: <?= json_encode($sales_data) ?>
    }],
    chart: {
        type: 'area',
        height: 300,
        fontFamily: "'DM Sans', sans-serif",
        toolbar: { show: false },
        animations: {
            enabled: false
        }
    },
    colors: ['#012a5e'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.1,
            stops: [0, 90, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: <?= json_encode($labels) ?>,
        tooltip: { enabled: false }
    },
    yaxis: {
        labels: {
            formatter: function (value) {
                return value.toLocaleString() + ' RFW';
            }
        }
    },
    tooltip: {
        y: {
            formatter: function(value) {
                return value.toLocaleString() + ' RFW';
            }
        }
    }
};

const chart = new ApexCharts(document.querySelector("#reportChart"), options);
chart.render();
<?php endif; ?>

function exportPDF() {
    const element = document.getElementById('pdf-table-export');
    const header = document.getElementById('pdf-header');
    const title = document.getElementById('table-title');
    
    // Temporarily show the header for the PDF
    if (header) header.style.display = 'block';
    if (title) title.style.display = 'none';
    
    element.classList.add('pdf-export-mode');
    
    const opt = {
      margin:       [10, 10, 10, 10],
      filename:     'Sales_Report_<?= $start_date ?>_to_<?= $end_date ?>.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' } // landscape is better for data tables
    };

        html2pdf().set(opt).from(element).save().then(() => {
        // Hide the header again
        if (header) header.style.display = 'none';
        if (title) title.style.display = 'block';
        element.classList.remove('pdf-export-mode');
    });
}

function toggleSelectAllReports(master) {
    document.querySelectorAll('.report-row-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateReportsBulkToolbar();
}

function updateReportsBulkToolbar() {
    const checked = document.querySelectorAll('.report-row-chk:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkReportsBar');
    const countSpan = document.getElementById('reportsSelectedCount');
    const master = document.getElementById('selectAllReports');
    
    countSpan.innerText = count;
    if (count > 0) {
        bar.classList.remove('d-none');
        bar.classList.add('d-flex');
    } else {
        bar.classList.add('d-none');
        bar.classList.remove('d-flex');
        if (master) master.checked = false;
    }
}

function clearSelectedReports() {
    document.querySelectorAll('.report-row-chk').forEach(cb => { cb.checked = false; });
    const master = document.getElementById('selectAllReports');
    if (master) master.checked = false;
    updateReportsBulkToolbar();
}
</script>

=======
require_once 'includes/header.php';
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title">Reports & Analytics</h2>
</div>

<div class="admin-card p-5 text-center mt-4">
    <i class="fas fa-tools fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
    <h3 class="mt-3">Under Construction</h3>
    <p class="text-muted">The Reports & Analytics module is currently being built. Check back soon!</p>
</div>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
