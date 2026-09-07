<?php
require_once 'includes/header.php';

// Search and filter parameters
$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? clean_input($conn, $_GET['status']) : '';
$date_filter = isset($_GET['date_filter']) ? clean_input($conn, $_GET['date_filter']) : '';

// Build Query
$where_clauses = ["o.deleted_at IS NOT NULL"];

if (!empty($search)) {
    $where_clauses[] = "(o.id LIKE '%$search%' OR o.order_number LIKE '%$search%' OR o.shipping_name LIKE '%$search%' OR o.shipping_phone LIKE '%$search%' OR o.guest_email LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $where_clauses[] = "o.status = '$status_filter'";
}

if (!empty($date_filter)) {
    if ($date_filter === 'today') {
        $where_clauses[] = "DATE(o.deleted_at) = CURDATE()";
    } elseif ($date_filter === 'week') {
        $where_clauses[] = "o.deleted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($date_filter === 'month') {
        $where_clauses[] = "o.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

$where_sql = implode(" AND ", $where_clauses);

$orders_query = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name, u.email as user_email, d.first_name as del_fname, d.last_name as del_lname 
                                      FROM orders o 
                                      LEFT JOIN users u ON o.user_id = u.id 
                                      LEFT JOIN users d ON o.delivered_by = d.id 
                                      WHERE $where_sql 
                                      ORDER BY o.deleted_at DESC");

// Fetch order items to embed in view modal
$order_items = [];
$items_query = mysqli_query($conn, "SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id");
if ($items_query) {
    while($item = mysqli_fetch_assoc($items_query)) {
        $order_items[$item['order_id']][] = $item;
    }
}

// Trash Stats
$trash_count_q = mysqli_query($conn, "SELECT COUNT(*) as c, COALESCE(SUM(total_amount), 0) as total FROM orders WHERE deleted_at IS NOT NULL");
$trash_stats = mysqli_fetch_assoc($trash_count_q);
$total_trashed = $trash_stats['c'] ?? 0;
$total_trashed_amount = $trash_stats['total'] ?? 0;

$trashed_this_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE deleted_at IS NOT NULL AND MONTH(deleted_at) = MONTH(CURRENT_DATE()) AND YEAR(deleted_at) = YEAR(CURRENT_DATE())"))['c'] ?? 0;
?>

<div class="admin-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="admin-page-title"><i class="fas fa-trash-alt me-2 text-accent"></i> Orders Trash & Recovery</h2>
        <p class="text-muted small mb-0">Search, restore, or permanently purge deleted customer orders.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="orders.php" class="btn btn-outline-primary shadow-sm">
            <i class="fas fa-shopping-cart me-1"></i> Active Orders
        </a>
        <?php if($total_trashed > 0): ?>
            <form action="actions.php" method="POST" class="d-inline m-0" onsubmit="return customConfirm(event, 'Restore all <?= $total_trashed ?> deleted orders back to active status?')">
                <input type="hidden" name="action" value="restore_all_trash">
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="fas fa-undo-alt me-1"></i> Restore All
                </button>
            </form>
            <form action="actions.php" method="POST" class="d-inline m-0" onsubmit="return customConfirm(event, 'PERMANENTLY DELETE all <?= $total_trashed ?> orders in trash? This cannot be undone!')">
                <input type="hidden" name="action" value="empty_trash">
                <button type="submit" class="btn btn-danger shadow-sm">
                    <i class="fas fa-dumpster me-1"></i> Empty Trash
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3">
    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- STATS CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="kpi-card accent">
            <div class="kpi-icon accent"><i class="fas fa-trash-alt"></i></div>
            <div>
                <div class="kpi-value text-accent"><?= number_format($total_trashed) ?></div>
                <div class="kpi-label">Orders in Trash</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_trashed_amount, 0) ?> RFW</div>
                <div class="kpi-label">Total Trashed Value</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon info"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="kpi-value text-info"><?= number_format($trashed_this_month) ?></div>
                <div class="kpi-label">Deleted This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH AND FILTER BAR -->
<div class="admin-card p-4 mb-4">
    <form method="GET" action="trash.php" class="row g-3 align-items-end" id="trashSearchForm">
        <div class="col-md-5">
            <label class="form-label fw-bold small text-uppercase text-primary" style="font-size: 0.75rem;"><i class="fas fa-search me-1"></i> Search Deleted Orders</label>
            <div class="position-relative">
                <input type="text" name="search" id="liveTrashSearch" class="form-control" style="font-size: 0.85rem; padding-right: 35px;" placeholder="Search Order #, Customer, Phone, Email..." value="<?= htmlspecialchars($search) ?>" autocomplete="off" oninput="filterTrashTable(this.value)">
                <i class="fas fa-search position-absolute text-muted" style="right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem; pointer-events: none;"></i>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small text-uppercase text-primary" style="font-size: 0.75rem;"><i class="fas fa-tag me-1"></i> Status</label>
            <select name="status" class="form-select" style="font-size: 0.85rem;" onchange="document.getElementById('trashSearchForm').submit()">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $status_filter == 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $status_filter == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $status_filter == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="Declined" <?= $status_filter == 'Declined' ? 'selected' : '' ?>>Declined</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small text-uppercase text-primary" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i> Deleted Time</label>
            <select name="date_filter" class="form-select" style="font-size: 0.85rem;" onchange="document.getElementById('trashSearchForm').submit()">
                <option value="">All Time</option>
                <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $date_filter == 'week' ? 'selected' : '' ?>>Past 7 Days</option>
                <option value="month" <?= $date_filter == 'month' ? 'selected' : '' ?>>Past 30 Days</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100" style="font-size: 0.85rem;"><i class="fas fa-filter me-1"></i> Filter</button>
            <?php if(!empty($search) || !empty($status_filter) || !empty($date_filter)): ?>
                <a href="trash.php" class="btn btn-outline-secondary" style="font-size: 0.85rem;" title="Reset Filters"><i class="fas fa-undo"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TRASH ORDERS TABLE -->
<form action="actions.php" method="POST" id="bulkTrashForm">
<div id="bulkTrashBar" class="admin-card p-3 mb-3 d-none align-items-center justify-content-between bg-primary bg-opacity-10 border border-primary">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-primary fs-5"></i>
        <span class="fw-bold"><span id="selectedTrashCount">0</span> order(s) selected in trash</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="submit" name="action" value="bulk_restore_orders" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-undo-alt me-1"></i> Restore Selected
        </button>
        <button type="submit" name="action" value="bulk_permanent_delete_orders" class="btn btn-sm btn-danger shadow-sm" onclick="return customConfirm(event, 'PERMANENTLY delete selected orders? This CANNOT be undone!')">
            <i class="fas fa-trash-alt me-1"></i> Delete Permanently
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedTrash()">
            Deselect All
        </button>
    </div>
</div>

<div class="admin-card overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0" id="trashTable">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="selectAllTrash" onchange="toggleSelectAllTrash(this)" title="Select All Trash Orders">
                    </th>
                    <th style="width: 50px;">#</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Original Status</th>
                    <th>Date Deleted</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_count = 1;
                $orders_found = mysqli_num_rows($orders_query);
                if ($orders_found > 0):
                    while($o = mysqli_fetch_assoc($orders_query)): 
                        $c_name = $o['user_id'] ? ($o['first_name'] . ' ' . $o['last_name']) : $o['shipping_name'];
                        $c_email = $o['user_id'] ? $o['user_email'] : $o['guest_email'];
                        $c_phone = $o['shipping_phone'];
                        $items_for_this_order = $order_items[$o['id']] ?? [];
                        $status_badge_class = 'bg-secondary';
                        if ($o['status'] === 'Delivered') $status_badge_class = 'bg-success';
                        elseif ($o['status'] === 'Pending') $status_badge_class = 'bg-warning text-dark';
                        elseif ($o['status'] === 'Processing') $status_badge_class = 'bg-info text-dark';
                        elseif ($o['status'] === 'Shipped') $status_badge_class = 'bg-primary';
                        elseif ($o['status'] === 'Cancelled' || $o['status'] === 'Declined') $status_badge_class = 'bg-danger';
                ?>
                <tr class="trash-order-row" data-search="<?= strtolower(htmlspecialchars($o['order_number'] . ' ' . $o['id'] . ' ' . $c_name . ' ' . $c_email . ' ' . $c_phone)) ?>">
                    <td>
                        <input type="checkbox" class="form-check-input trash-row-chk" name="order_ids[]" value="<?= $o['id'] ?>" onchange="updateBulkTrashToolbar()">
                    </td>
                    <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                    <td>
                        <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold" onclick='viewOrder(<?= json_encode($items_for_this_order) ?>, <?= json_encode($o) ?>)'>
                            <span class="badge bg-secondary px-2 py-1" style="font-size: 0.82rem;"><?= $o['order_number'] ?: '#' . $o['id'] ?></span>
                        </button>
                    </td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($c_name) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($c_email ?: $c_phone) ?></div>
                    </td>
                    <td class="fw-bold text-primary"><?= number_format($o['total_amount'], 0) ?> RFW</td>
                    <td>
                        <span class="badge <?= $status_badge_class ?>"><?= htmlspecialchars($o['status']) ?></span>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?= date('M d, Y', strtotime($o['deleted_at'])) ?></div>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= date('H:i A', strtotime($o['deleted_at'])) ?></div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            <!-- View Button -->
                            <button type="button" class="btn btn-sm btn-outline-primary" title="View Order Items" onclick='viewOrder(<?= json_encode($items_for_this_order) ?>, <?= json_encode($o) ?>)'>
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Restore Button -->
                            <form action="actions.php" method="POST" class="d-inline m-0">
                                <input type="hidden" name="action" value="restore_order">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success text-nowrap shadow-sm" title="Restore back to Active Orders">
                                    <i class="fas fa-undo-alt me-1"></i> Restore
                                </button>
                            </form>
                            <!-- Delete Permanently Button -->
                            <form action="actions.php" method="POST" class="d-inline m-0" onsubmit="return customConfirm(event, 'Delete order <?= $o['order_number'] ?: '#' . $o['id'] ?> permanently? This action CANNOT be undone.')">
                                <input type="hidden" name="action" value="permanent_delete_order">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Delete Permanently">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-trash-alt fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                        <h5>No Deleted Orders in Trash</h5>
                        <p class="small mb-0">When orders are deleted from the Active Orders page, they will appear here so you can search, view, and restore them anytime.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</form>

<!-- VIEW ORDER MODAL -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background:var(--bg2); color:var(--text); border-radius: 14px;">
        <div class="modal-header border-0 pb-0" style="padding: 1.5rem;">
          <h5 class="modal-title fw-bold fs-4"><i class="fas fa-file-invoice me-2 text-primary"></i> Order Details: <span id="vo_id" class="text-primary"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <div class="row mb-4 rounded p-3 mx-0" style="background: var(--bg3) !important; border: 1px solid var(--border);">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Customer Information</h6>
                    <div class="d-flex align-items-center mb-1">
                        <i class="fas fa-user text-primary me-2" style="width:16px;"></i> <strong id="vo_customer"></strong>
                    </div>
                    <div class="d-flex align-items-center mb-1 text-muted">
                        <i class="fas fa-envelope me-2" style="width:16px;"></i> <span id="vo_email"></span>
                    </div>
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-phone me-2" style="width:16px;"></i> <span id="vo_phone"></span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end border-start-md" style="border-color: var(--border) !important;">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Order Summary</h6>
                    <div class="fs-4 fw-bold text-primary mb-1"><span id="vo_total"></span> RFW</div>
                    <div class="mb-1"><span class="badge" id="vo_status"></span></div>
                    <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Placed: <span id="vo_date"></span></div>
                    <div class="text-danger small mt-1"><i class="fas fa-trash-alt me-1"></i> Deleted: <span id="vo_deleted_date"></span></div>
                </div>
            </div>
            
            <div class="card border-0 mb-4 shadow-sm" style="background: var(--bg3); border: 1px solid var(--border) !important;">
                <div class="card-body p-3">
                    <h6 class="card-title text-uppercase text-primary fw-bold mb-2" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="fas fa-shipping-fast me-2"></i>Shipping & Delivery Destination</h6>
                    <div id="vo_shipping" style="font-size: 0.95rem; line-height: 1.6; color: var(--text);"></div>
                </div>
            </div>
            
            <h6 class="text-uppercase text-primary fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="fas fa-box-open me-2"></i>Ordered Items</h6>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead style="background: var(--bg3);">
                        <tr><th>#</th><th>Product</th><th>Price</th><th>Qty</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody id="vo_items"></tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer border-0" style="padding: 1.5rem;">
          <form id="modalRestoreForm" action="actions.php" method="POST" class="d-inline m-0 me-auto">
              <input type="hidden" name="action" value="restore_order">
              <input type="hidden" name="order_id" id="modalRestoreId" value="">
              <button type="submit" class="btn btn-success px-4"><i class="fas fa-undo-alt me-1"></i> Restore This Order</button>
          </form>
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>

<script>
function toggleSelectAllTrash(master) {
    document.querySelectorAll('.trash-row-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkTrashToolbar();
}

function updateBulkTrashToolbar() {
    const checked = document.querySelectorAll('.trash-row-chk:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkTrashBar');
    const countSpan = document.getElementById('selectedTrashCount');
    const master = document.getElementById('selectAllTrash');
    
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

function clearSelectedTrash() {
    document.querySelectorAll('.trash-row-chk').forEach(cb => { cb.checked = false; });
    const master = document.getElementById('selectAllTrash');
    if (master) master.checked = false;
    updateBulkTrashToolbar();
}
function filterTrashTable(query) {
    const q = query.trim().toLowerCase();
    const rows = document.querySelectorAll('.trash-order-row');
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search') || '';
        if (!q || searchText.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function viewOrder(items, order) {
    document.getElementById('vo_id').innerText = order.order_number ? order.order_number : ('#' + order.id);
    document.getElementById('modalRestoreId').value = order.id;
    
    let c_name = order.user_id ? (order.first_name + ' ' + order.last_name) : order.shipping_name;
    let c_email = order.user_id ? order.user_email : order.guest_email;
    document.getElementById('vo_customer').innerText = c_name || 'Guest';
    document.getElementById('vo_email').innerText = c_email || 'N/A';
    document.getElementById('vo_phone').innerText = order.shipping_phone || 'N/A';
    document.getElementById('vo_date').innerText = order.created_at;
    document.getElementById('vo_deleted_date').innerText = order.deleted_at || 'Recently';
    document.getElementById('vo_total').innerText = new Intl.NumberFormat().format(order.total_amount);
    
    let gateStr = order.shipping_gate ? ('<b>Street / Gate:</b> ' + escapeHtml(order.shipping_gate) + '<br>') : '';
    let addr = '<div class="d-flex align-items-start mb-2"><i class="fas fa-user text-muted mt-1 me-2" style="width:16px;"></i> <span><strong>' + escapeHtml(order.shipping_name) + '</strong></span></div>' + 
               '<div class="d-flex align-items-start mb-2"><i class="fas fa-map-marker-alt text-muted mt-1 me-2" style="width:16px;"></i> <span>' + escapeHtml(order.shipping_address || '') + '<br>' + 
               gateStr + 
               escapeHtml(order.shipping_city || '') + ', ' + escapeHtml(order.shipping_state || '') + '<br>' + 
               escapeHtml(order.shipping_country || '') + '</span></div>' +
               '<div class="d-flex align-items-start"><i class="fas fa-truck text-muted mt-1 me-2" style="width:16px;"></i> <span>Method: <strong>' + escapeHtml(order.shipping_method || 'Standard') + '</strong></span></div>';
    document.getElementById('vo_shipping').innerHTML = addr;
    
    let html = '';
    let itemsSubtotal = 0;
    if (items && items.length > 0) {
        items.forEach((item, index) => {
            let sub = parseFloat(item.price) * parseInt(item.quantity);
            itemsSubtotal += sub;
            let name = escapeHtml(item.product_name) || 'Unknown Product';
            let img = item.image ? '../uploads/' + item.image : '../uploads/default_product.jpg';
            
            let options = [];
            if (item.color && item.color !== '') options.push('Color: <strong>' + escapeHtml(item.color) + '</strong>');
            if (item.size && item.size !== '') options.push('Size: <strong>' + escapeHtml(item.size) + '</strong>');
            let optionsHtml = options.length > 0 ? `<br><small class="text-muted">${options.join(' &bull; ')}</small>` : '';
            
            html += `<tr>
                <td class="text-muted fw-bold">(${index + 1})</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${img}" alt="${name}" class="rounded me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                            <div class="fw-bold">${name}</div>
                            ${optionsHtml}
                        </div>
                    </div>
                </td>
                <td class="text-muted">${new Intl.NumberFormat().format(item.price)} RFW</td>
                <td><span class="badge bg-light text-dark border px-2 py-1">${item.quantity}</span></td>
                <td class="text-end fw-bold text-primary">${new Intl.NumberFormat().format(sub)} RFW</td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center text-muted py-3">No individual items recorded for this order.</td></tr>';
    }
    
    document.getElementById('vo_items').innerHTML = html;
    
    // Status Badge Color
    let statusBadge = document.getElementById('vo_status');
    statusBadge.innerText = order.status;
    statusBadge.className = 'badge px-3 py-2 ';
    if(order.status === 'Pending') statusBadge.classList.add('bg-warning', 'text-dark');
    else if(order.status === 'Processing') statusBadge.classList.add('bg-info', 'text-dark');
    else if(order.status === 'Shipped') statusBadge.classList.add('bg-primary');
    else if(order.status === 'Delivered') statusBadge.classList.add('bg-success');
    else if(order.status === 'Cancelled' || order.status === 'Declined') statusBadge.classList.add('bg-danger');
    else statusBadge.classList.add('bg-secondary');

    new bootstrap.Modal(document.getElementById('viewOrderModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
