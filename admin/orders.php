<?php
require_once 'includes/header.php';

<<<<<<< HEAD
$orders_query = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name, u.email as user_email, d.first_name as del_fname, d.last_name as del_lname FROM orders o LEFT JOIN users u ON o.user_id = u.id LEFT JOIN users d ON o.delivered_by = d.id WHERE o.deleted_at IS NULL ORDER BY o.created_at DESC");

// Fetch Riders
$riders_query = mysqli_query($conn, "SELECT id, first_name, last_name FROM users WHERE role = 'rider'");
$riders = [];
while($r = mysqli_fetch_assoc($riders_query)) {
    $riders[] = $r;
}
=======
$orders_query = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

// Fetch order items to embed
$order_items = [];
$items_query = mysqli_query($conn, "SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id");
while($item = mysqli_fetch_assoc($items_query)) {
    $order_items[$item['order_id']][] = $item;
}
<<<<<<< HEAD
$settings_q = mysqli_query($conn, "SELECT * FROM settings");
$global_settings = [];
while($row = mysqli_fetch_assoc($settings_q)) {
    $global_settings[$row['setting_key']] = $row['setting_value'];
}
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $global_settings;
        return htmlspecialchars($global_settings[$key] ?? $default);
    }
}

// Dashboard Metrics
$total_orders_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders");
$total_orders = mysqli_fetch_assoc($total_orders_q)['cnt'] ?? 0;

$total_revenue_q = mysqli_query($conn, "SELECT SUM(total_amount) as rev FROM orders WHERE status != 'Cancelled'");
$total_revenue = mysqli_fetch_assoc($total_revenue_q)['rev'] ?? 0;

// Economical Analysis (Monthly Growth)
$this_month_q = mysqli_query($conn, "SELECT SUM(total_amount) as rev FROM orders WHERE status != 'Cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$this_month_rev = mysqli_fetch_assoc($this_month_q)['rev'] ?? 0;

$last_month_q = mysqli_query($conn, "SELECT SUM(total_amount) as rev FROM orders WHERE status != 'Cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
$last_month_rev = mysqli_fetch_assoc($last_month_q)['rev'] ?? 0;

$growth_percent = 0;
if ($last_month_rev > 0) {
    $growth_percent = (($this_month_rev - $last_month_rev) / $last_month_rev) * 100;
} else if ($this_month_rev > 0) {
    $growth_percent = 100;
}
$global_delivery_days = (int)get_setting('global_delivery_days', 0);
$show_deadline = ($global_delivery_days > 0 || get_setting('store_order_status') === 'schedule');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title m-0"><i class="fas fa-shopping-cart me-2 text-accent"></i> Orders Management</h2>
</div>

<!-- Dashboard Cards -->
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="admin-card p-4 h-100 d-flex align-items-center">
            <div class="me-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="fas fa-shopping-bag text-primary fs-3"></i>
            </div>
            <div>
                <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem; letter-spacing:1px;">Total Orders Placed</h6>
                <h3 class="m-0 fw-bold"><?= number_format($total_orders) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="admin-card p-4 h-100 d-flex align-items-center">
            <div class="me-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="fas fa-chart-line text-success fs-3"></i>
            </div>
            <div>
                <h6 class="text-muted text-uppercase mb-1" style="font-size:0.8rem; letter-spacing:1px;">Total Revenue</h6>
                <h3 class="m-0 fw-bold"><?= number_format($total_revenue, 0) ?> RFW</h3>
                <div class="mt-1" style="font-size: 0.85rem;">
                    <?php if($growth_percent >= 0): ?>
                        <span class="text-success fw-bold"><i class="fas fa-arrow-up"></i> <?= number_format($growth_percent, 1) ?>%</span> <span class="text-muted">vs last month</span>
                    <?php else: ?>
                        <span class="text-danger fw-bold"><i class="fas fa-arrow-down"></i> <?= number_format(abs($growth_percent), 1) ?>%</span> <span class="text-muted">vs last month</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Global Order Settings -->
<div class="admin-card p-4 mb-4" style="border-left: 4px solid var(--primary);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 text-primary"><i class="fas fa-cogs me-2"></i> Global Order Control & Translations</h5>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#translationFields" aria-expanded="true">
            <i class="fas fa-language me-1"></i> Toggle Language Content (EN / RW)
        </button>
    </div>
    
    <form action="actions.php" method="POST">
        <input type="hidden" name="action" value="update_order_settings">
        
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Store Order Status</label>
                <select name="settings[store_order_status]" class="form-select" id="store_order_status" onchange="toggleOrderSettings()">
                    <option value="enable" <?= get_setting('store_order_status') == 'enable' ? 'selected' : '' ?>>Enable (Normal)</option>
                    <option value="disable" <?= get_setting('store_order_status') == 'disable' ? 'selected' : '' ?>>Disable Ordering</option>
                    <option value="schedule" <?= get_setting('store_order_status') == 'schedule' ? 'selected' : '' ?>>Auto Follow Schedule</option>
                </select>
            </div>
            <div class="col-md-2" id="delay_col" style="<?= get_setting('store_order_status') == 'schedule' ? '' : 'display:none;' ?>">
                <label class="form-label fw-bold">Wait Period (Hours)</label>
                <input type="number" name="settings[store_order_delay_hours]" class="form-control" value="<?= get_setting('store_order_delay_hours', '12') ?>" min="1">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Auto-Cancel Order (Days)</label>
                <input type="number" name="settings[global_delivery_days]" class="form-control" value="<?= get_setting('global_delivery_days', '0') ?>" min="0" placeholder="0 = Disable">
            </div>
            <div class="col-md-3" id="reason_col" style="<?= get_setting('store_order_status') != 'enable' ? '' : 'display:none;' ?>">
                <label class="form-label fw-bold">Admin Note (Problem Faced)</label>
                <input type="text" name="settings[store_order_problem_faced]" class="form-control" value="<?= get_setting('store_order_problem_faced', '') ?>" placeholder="e.g. Stock taking, holiday">
            </div>
            <div class="col-md-2 ms-auto">
                <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-save me-1"></i> Save Control</button>
            </div>
        </div>

        <!-- Bilingual Translation & Notice Messages -->
        <div class="collapse show" id="translationFields">
            <div class="p-3 rounded-3 mt-2" style="background: var(--bg2); border: 1px solid var(--border);">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-color: var(--border) !important;">
                    <div>
                        <strong style="color: var(--text);"><i class="fas fa-language text-primary me-2"></i> Auto-Cancel & Store Notice Translations</strong>
                        <div class="text-muted small">Customize messages displayed on order tracking, customer account, and store banners in English and Kinyarwanda.</div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- English Section -->
                    <div class="col-md-6 border-end-md" style="border-color: var(--border) !important;">
                        <h6 class="fw-bold text-primary mb-2" style="font-size: 0.85rem;"><i class="fas fa-globe me-1"></i> English Content (EN)</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Auto-Cancel Title (EN)</label>
                            <input type="text" name="settings[auto_cancel_title_en]" class="form-control form-control-sm" value="<?= get_setting('auto_cancel_title_en', 'Auto-Cancel Deadline') ?>" placeholder="Auto-Cancel Deadline">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Auto-Cancel Notice / Description (EN)</label>
                            <textarea name="settings[auto_cancel_message_en]" class="form-control form-control-sm" rows="2" placeholder="Orders not delivered before this deadline will be automatically cancelled."><?= get_setting('auto_cancel_message_en', 'Orders not delivered before this deadline will be automatically cancelled.') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Store Disabled / Closed Message (EN)</label>
                            <input type="text" name="settings[store_order_message]" class="form-control form-control-sm" value="<?= get_setting('store_order_message', 'Ordering is temporarily disabled.') ?>" placeholder="Ordering is temporarily disabled.">
                        </div>
                    </div>

                    <!-- Kinyarwanda Section -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-2" style="font-size: 0.85rem;"><i class="fas fa-globe-africa me-1"></i> Kinyarwanda Content (RW)</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Auto-Cancel Title (RW)</label>
                            <input type="text" name="settings[auto_cancel_title_rw]" class="form-control form-control-sm" value="<?= get_setting('auto_cancel_title_rw', 'Igihe Ntarengwa cyo Guhagarika Komande') ?>" placeholder="Igihe Ntarengwa cyo Guhagarika Komande">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Auto-Cancel Notice / Description (RW)</label>
                            <textarea name="settings[auto_cancel_message_rw]" class="form-control form-control-sm" rows="2" placeholder="Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora."><?= get_setting('auto_cancel_message_rw', "Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora.") ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Store Disabled / Closed Message (RW)</label>
                            <input type="text" name="settings[store_order_message_rw]" class="form-control form-control-sm" value="<?= get_setting('store_order_message_rw', 'Gutumiza ibicuruzwa byahagaze by\'agateganyo.') ?>" placeholder="Gutumiza ibicuruzwa byahagaze by'agateganyo.">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleOrderSettings() {
    const status = document.getElementById('store_order_status').value;
    const delayCol = document.getElementById('delay_col');
    const reasonCol = document.getElementById('reason_col');
    
    if (delayCol) delayCol.style.display = (status === 'schedule') ? 'block' : 'none';
    if (reasonCol) reasonCol.style.display = (status !== 'enable') ? 'block' : 'none';
    
    const delayInput = delayCol ? delayCol.querySelector('input') : null;
    const reasonInput = reasonCol ? reasonCol.querySelector('input') : null;
    
    if (delayInput) delayInput.required = (status === 'schedule');
    if (reasonInput) reasonInput.required = (status !== 'enable');
}
document.addEventListener("DOMContentLoaded", function() { toggleOrderSettings(); });
</script>

<!-- Bulk Action Toolbar -->
<form action="actions.php" method="POST" id="bulkOrdersForm">
<input type="hidden" name="action" value="bulk_delete_orders">
<input type="hidden" name="redirect" value="orders.php">

<div id="bulkOrdersBar" class="admin-card p-3 mb-3 d-none align-items-center justify-content-between bg-primary bg-opacity-10 border border-primary">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-primary fs-5"></i>
        <span class="fw-bold"><span id="selectedOrdersCount">0</span> order(s) selected</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-sm btn-danger shadow-sm px-3" onclick="return customConfirm(event, 'Move selected orders to trash?')">
            <i class="fas fa-trash-alt me-1"></i> Move Selected to Trash
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedOrders()">
            Deselect All
        </button>
    </div>
</div>

<div class="table-responsive">
<table class="table admin-table mt-3">
    <thead>
        <tr>
            <th style="width: 40px;">
                <input type="checkbox" class="form-check-input" id="selectAllOrders" onchange="toggleSelectAllOrders(this)" title="Select All Orders">
            </th>
            <th>#</th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Rider</th>
            <?php if ($show_deadline): ?><th>Deadline</th><?php endif; ?>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $row_count = 1; mysqli_data_seek($orders_query, 0); while($o = mysqli_fetch_assoc($orders_query)): ?>
        <?php 
            $c_name = $o['user_id'] ? ($o['first_name'] . ' ' . $o['last_name']) : $o['shipping_name'];
            $c_email = $o['user_id'] ? $o['user_email'] : $o['guest_email'];
        ?>
        <tr>
            <td>
                <input type="checkbox" class="form-check-input order-row-chk" name="order_ids[]" value="<?= $o['id'] ?>" onchange="updateBulkOrdersToolbar()">
            </td>
            <td><?= $row_count++ ?></td>
            <td><span class="badge bg-secondary"><?= $o['order_number'] ?: '#' . $o['id'] ?></span></td>
            <td>
                <strong><?= htmlspecialchars($c_name) ?></strong>
            </td>
            <td class="fw-bold text-primary"><?= number_format($o['total_amount'], 0) ?> RFW</td>
            <td>
                <?= $o['status'] ?>
                <?php if ($o['status'] === 'Delivered' && $o['del_fname']): ?>
                    <br><small class="text-success fw-bold">Approved by <?= htmlspecialchars($o['del_fname'] . ' ' . $o['del_lname']) ?></small>
                <?php endif; ?>
            </td>
            <td>
                <form action="actions.php" method="POST" class="m-0">
                    <input type="hidden" name="action" value="assign_rider">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <select name="rider_id" class="form-select form-select-sm" style="width:110px;" onchange="this.form.submit()">
                        <option value="">Unassigned</option>
                        <?php foreach($riders as $rider): ?>
                        <option value="<?= $rider['id'] ?>" <?= $o['rider_id'] == $rider['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rider['first_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <?php if ($show_deadline): ?>
            <td>
                <?php if (in_array($o['status'], ['Cancelled', 'Auto-Cancelled', 'Declined'])): ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-times-circle me-1"></i>Cancelled</span>
                <?php elseif ($o['delivery_deadline']): ?>
                    <?php
                        $deadline_ts = strtotime($o['delivery_deadline']);
                        $diff_sec = $deadline_ts - time();
                        $deadline_formatted = date('M d, H:i', $deadline_ts);
                    ?>
                    <div class="fw-bold" style="font-size: 0.9rem;"><?= $deadline_formatted ?></div>
                    <?php if (in_array($o['status'], ['Delivered'])): ?>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success" style="font-size:0.75rem;"><i class="fas fa-check-circle me-1"></i>Delivered</span>
                    <?php elseif ($diff_sec > 0): ?>
                        <?php
                            $rem_days = floor($diff_sec / 86400);
                            $rem_hours = floor(($diff_sec % 86400) / 3600);
                            $rem_str = $rem_days > 0 ? "{$rem_days}d {$rem_hours}h left" : "{$rem_hours}h left";
                        ?>
                        <span class="badge bg-warning bg-opacity-25 text-dark border border-warning" style="font-size:0.75rem;"><i class="fas fa-hourglass-half me-1"></i><?= $rem_str ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger" style="font-size:0.75rem;"><i class="fas fa-exclamation-triangle me-1"></i>Expired</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3" onclick='viewOrder(<?= json_encode($order_items[$o['id']] ?? []) ?>, <?= json_encode($o) ?>)'><i class="fas fa-file-invoice"></i></button>
                    <?php if(!empty($o['delivery_proof'])): ?>
                    <a href="../uploads/<?= htmlspecialchars($o['delivery_proof']) ?>" target="_blank" class="btn btn-sm btn-success shadow-sm rounded-pill px-3" title="View Delivery Proof"><i class="fas fa-camera"></i></a>
                    <?php endif; ?>
                    <form action="actions.php" method="POST" class="d-inline d-flex align-items-center gap-1 m-0">
                        <input type="hidden" name="action" value="update_order">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" class="form-select form-select-sm" style="width:110px;" onchange="this.form.submit()">
                            <option <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
                            <option <?= $o['status']=='Processing'?'selected':'' ?>>Processing</option>
                            <option <?= $o['status']=='Shipped'?'selected':'' ?>>Shipped</option>
                            <option value="Delivery Requested" <?= $o['status']=='Delivery Requested'?'selected':'' ?>>Delivery Req.</option>
                            <option <?= $o['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                            <option <?= $o['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                            <option <?= $o['status']=='Declined'?'selected':'' ?>>Declined</option>
                            <option <?= $o['status']=='Failed'?'selected':'' ?>>Failed</option>
                        </select>
                    </form>
                    <form action="actions.php" method="POST" class="d-inline m-0" onsubmit="return customConfirm(event, 'Move this order to trash?')">
                        <input type="hidden" name="action" value="delete_order">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Move to Trash"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </div>
=======
?>
<h2>Orders</h2>
<table class="table admin-table mt-3">
    <thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
        <?php mysqli_data_seek($orders_query, 0); while($o = mysqli_fetch_assoc($orders_query)): ?>
        <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
            <td>$<?= number_format($o['total_amount'], 2) ?></td>
            <td><?= $o['status'] ?></td>
            <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
            <td>
                <button class="btn btn-sm btn-info text-white" onclick='viewOrder(<?= json_encode($order_items[$o['id']] ?? []) ?>, <?= json_encode($o) ?>)'><i class="fas fa-eye"></i></button>
                <form action="actions.php" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="update_order">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                        <option <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option <?= $o['status']=='Processing'?'selected':'' ?>>Processing</option>
                        <option <?= $o['status']=='Shipped'?'selected':'' ?>>Shipped</option>
                        <option <?= $o['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                        <option <?= $o['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                    </select>
                </form>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<<<<<<< HEAD
</div>
</form>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="background:var(--bg2); color:var(--text); border-radius: 12px;">
        <div class="modal-header border-0 pb-0" style="padding: 1.5rem;">
          <h5 class="modal-title fw-bold fs-4"><i class="fas fa-file-invoice me-2 text-primary"></i> Order <span id="vo_id" class="text-primary"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="filter: var(--close-filter);"></button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <div class="row mb-4 bg-light rounded p-3 mx-0" style="background: var(--bg3) !important;">
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
                    <div class="fs-5 fw-bold text-primary mb-1"><span id="vo_total"></span> RFW</div>
                    <div class="mb-1"><span class="badge bg-secondary" id="vo_status"></span></div>
                    <div class="text-muted" style="font-size: 0.9rem;"><i class="far fa-calendar-alt me-1"></i> <span id="vo_date"></span></div>
                    <div id="vo_deadline_container" class="mt-1 d-none"></div>
                </div>
            </div>
            
            <div class="card border-0 mb-4 shadow-sm" style="background: var(--bg3);">
                <div class="card-body p-3">
                    <h6 class="card-title text-uppercase text-primary fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="fas fa-shipping-fast me-2"></i>Shipping Details</h6>
                    <div id="vo_shipping" style="font-size: 0.95rem; line-height: 1.6; color: var(--text);"></div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-uppercase text-primary fw-bold m-0" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="fas fa-box-open me-2"></i>Ordered Items</h6>
                <button type="button" id="btnDeleteSelectedItems" class="btn btn-sm btn-danger d-none shadow-sm" onclick="deleteSelectedOrderItems()">
                    <i class="fas fa-trash-alt me-1"></i> Delete Selected (<span id="modalSelectedItemsCount">0</span>)
                </button>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead style="background: var(--bg3);">
                        <tr>
                            <th style="width: 35px;">
                                <input type="checkbox" class="form-check-input" id="selectAllModalItems" onchange="toggleSelectAllModalItems(this)" title="Select All Items">
                            </th>
                            <th>#</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="vo_items"></tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer border-0" style="padding: 1.5rem;">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
=======

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
        <div class="modal-header border-0">
          <h5 class="modal-title">Order #<span id="vo_id"></span> Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Customer:</strong> <span id="vo_customer"></span><br>
                    <strong>Date:</strong> <span id="vo_date"></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <strong>Total:</strong> $<span id="vo_total"></span><br>
                    <strong>Status:</strong> <span id="vo_status"></span>
                </div>
            </div>
            <h6>Shipping Address</h6>
            <div class="mb-3" id="vo_shipping"></div>
            
            <h6>Items</h6>
            <table class="table admin-table table-sm">
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody id="vo_items"></tbody>
            </table>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        </div>
    </div>
  </div>
</div>

<script>
<<<<<<< HEAD
let currentViewingOrderId = null;

function toggleSelectAllOrders(master) {
    document.querySelectorAll('.order-row-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkOrdersToolbar();
}

function updateBulkOrdersToolbar() {
    const checked = document.querySelectorAll('.order-row-chk:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkOrdersBar');
    const countSpan = document.getElementById('selectedOrdersCount');
    const master = document.getElementById('selectAllOrders');
    
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

function clearSelectedOrders() {
    document.querySelectorAll('.order-row-chk').forEach(cb => { cb.checked = false; });
    const master = document.getElementById('selectAllOrders');
    if (master) master.checked = false;
    updateBulkOrdersToolbar();
}

function toggleSelectAllModalItems(master) {
    document.querySelectorAll('.modal-item-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateModalItemsToolbar();
}

function updateModalItemsToolbar() {
    const checked = document.querySelectorAll('.modal-item-chk:checked');
    const count = checked.length;
    const btn = document.getElementById('btnDeleteSelectedItems');
    const countSpan = document.getElementById('modalSelectedItemsCount');
    const master = document.getElementById('selectAllModalItems');
    
    countSpan.innerText = count;
    if (count > 0) {
        btn.classList.remove('d-none');
    } else {
        btn.classList.add('d-none');
        if (master) master.checked = false;
    }
}

function deleteOrderItemRow(itemId, orderId) {
    if (!confirm('Are you sure you want to remove this product row from this order?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_order_item');
    formData.append('item_id', itemId);
    formData.append('order_id', orderId);
    
    fetch('actions.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        window.location.reload();
    })
    .catch(() => {
        window.location.reload();
    });
}

function deleteSelectedOrderItems() {
    const checked = document.querySelectorAll('.modal-item-chk:checked');
    if (checked.length === 0) return;
    if (!confirm('Are you sure you want to remove ' + checked.length + ' selected product(s) from this order?')) return;
    
    const formData = new FormData();
    formData.append('action', 'bulk_delete_order_items');
    formData.append('order_id', currentViewingOrderId);
    checked.forEach(cb => {
        formData.append('item_ids[]', cb.value);
    });
    
    fetch('actions.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        window.location.reload();
    })
    .catch(() => {
        window.location.reload();
    });
}

=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

<<<<<<< HEAD
function showLocation(address, city, state, zip, country) {
    let locStr = "Shipping Location:\n\n";
    if (address) locStr += "Address: " + address + "\n";
    if (city) locStr += "City: " + city + "\n";
    if (state) locStr += "State: " + state + "\n";
    if (zip) locStr += "ZIP: " + zip + "\n";
    if (country) locStr += "Country: " + country + "\n";
    customAlert(locStr);
}

function viewOrder(items, order) {
    currentViewingOrderId = order.id;
    document.getElementById('vo_id').innerText = order.order_number ? order.order_number : ('#' + order.id);
    let c_name = order.user_id ? (order.first_name + ' ' + order.last_name) : order.shipping_name;
    let c_email = order.user_id ? order.user_email : order.guest_email;
    document.getElementById('vo_customer').innerText = c_name;
    document.getElementById('vo_email').innerText = c_email || 'N/A';
    document.getElementById('vo_phone').innerText = order.shipping_phone || 'N/A';
=======
function viewOrder(items, order) {
    document.getElementById('vo_id').innerText = order.id;
    document.getElementById('vo_customer').innerText = order.first_name + ' ' + order.last_name;
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    document.getElementById('vo_date').innerText = order.created_at;
    document.getElementById('vo_total').innerText = parseFloat(order.total_amount).toFixed(2);
    document.getElementById('vo_status').innerText = order.status;
    
<<<<<<< HEAD
    let gateStr = order.shipping_gate ? escapeHtml(order.shipping_gate) + '<br>' : '';
    let addr = '<div class="d-flex align-items-start mb-2"><i class="fas fa-user text-muted mt-1 me-2" style="width:16px;"></i> <span><strong>' + escapeHtml(order.shipping_name) + '</strong></span></div>' + 
               '<div class="d-flex align-items-start mb-2"><i class="fas fa-map-marker-alt text-muted mt-1 me-2" style="width:16px;"></i> <span>' + escapeHtml(order.shipping_address) + '<br>' + 
               gateStr + 
               escapeHtml(order.shipping_city) + ', ' + escapeHtml(order.shipping_state) + ' ' + escapeHtml(order.shipping_zip) + '<br>' + 
               escapeHtml(order.shipping_country) + '</span></div>' +
               '<div class="d-flex align-items-start"><i class="fas fa-truck text-muted mt-1 me-2" style="width:16px;"></i> <span>Method: <strong>' + escapeHtml(order.shipping_method) + '</strong></span></div>';
    document.getElementById('vo_shipping').innerHTML = addr;
    
    let html = '';
    let itemsSubtotal = 0;
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
            <td>
                <input type="checkbox" class="form-check-input modal-item-chk" value="${item.id}" onchange="updateModalItemsToolbar()">
            </td>
            <td class="text-muted fw-bold">(${index + 1})</td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="${img}" alt="${name}" class="rounded me-3 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                    <div>
                        <div class="fw-bold">${name}</div>
                        ${optionsHtml}
                    </div>
                </div>
            </td>
            <td class="text-muted">${parseFloat(item.price).toFixed(0)} RFW</td>
            <td><span class="badge bg-light text-dark border px-2 py-1">${item.quantity}</span></td>
            <td class="text-end fw-bold text-primary">${sub.toFixed(0)} RFW</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete this product from order" onclick="deleteOrderItemRow(${item.id}, ${order.id})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>`;
    });
    
    let shippingOther = parseFloat(order.total_amount) - itemsSubtotal;
    if (shippingOther < 0) shippingOther = 0; // In case of massive discounts
    
    html += `<tr style="border-top: 2px solid var(--border);">
        <td colspan="5" class="text-end fw-bold text-muted">Subtotal</td>
        <td class="text-end fw-bold text-muted">${itemsSubtotal.toFixed(0)} RFW</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="5" class="text-end fw-bold text-muted">Delivery Fee</td>
        <td class="text-end fw-bold text-muted">${shippingOther.toFixed(0)} RFW</td>
        <td></td>
    </tr>
    <tr style="background: var(--bg2);">
        <td colspan="5" class="text-end fw-bold text-primary" style="font-size: 1.1rem; border-bottom: none;">Total</td>
        <td class="text-end fw-bold text-primary" style="font-size: 1.1rem; border-bottom: none;">${parseFloat(order.total_amount).toFixed(0)} RFW</td>
        <td></td>
    </tr>`;
    
    document.getElementById('vo_items').innerHTML = html;
    
    // Reset modal selection
    const masterModal = document.getElementById('selectAllModalItems');
    if (masterModal) masterModal.checked = false;
    updateModalItemsToolbar();
    
    // Status Badge Color
    let statusBadge = document.getElementById('vo_status');
    statusBadge.innerText = order.status;
    statusBadge.className = 'badge px-3 py-2 ';
    if(order.status === 'Pending') statusBadge.classList.add('bg-warning', 'text-dark');
    else if(order.status === 'Processing') statusBadge.classList.add('bg-info', 'text-dark');
    else if(order.status === 'Shipped') statusBadge.classList.add('bg-primary');
    else if(order.status === 'Delivered') statusBadge.classList.add('bg-success');
    else if(order.status === 'Cancelled') statusBadge.classList.add('bg-danger');
    else statusBadge.classList.add('bg-secondary');

    let deadlineContainer = document.getElementById('vo_deadline_container');
    if (deadlineContainer) {
        if (order.delivery_deadline) {
            let deadlineDate = new Date(order.delivery_deadline.replace(/-/g, "/"));
            let now = new Date();
            let diffMs = deadlineDate - now;
            let formattedDeadline = order.delivery_deadline;
            
            if (['Delivered', 'Cancelled', 'Declined', 'Failed'].includes(order.status)) {
                deadlineContainer.innerHTML = '<span class="badge bg-secondary" style="font-size: 0.78rem;"><i class="fas fa-hourglass-end me-1"></i>Deadline: ' + escapeHtml(formattedDeadline) + '</span>';
            } else if (diffMs > 0) {
                let totalHours = Math.floor(diffMs / (1000 * 60 * 60));
                let days = Math.floor(totalHours / 24);
                let hours = totalHours % 24;
                let remText = days > 0 ? (days + 'd ' + hours + 'h left') : (hours + 'h left');
                deadlineContainer.innerHTML = '<span class="badge bg-warning text-dark border border-warning" style="font-size: 0.78rem;"><i class="fas fa-hourglass-half me-1"></i>Cancel: ' + escapeHtml(formattedDeadline) + ' (' + remText + ')</span>';
            } else {
                deadlineContainer.innerHTML = '<span class="badge bg-danger text-white" style="font-size: 0.78rem;"><i class="fas fa-exclamation-circle me-1"></i>Deadline Expired: ' + escapeHtml(formattedDeadline) + '</span>';
            }
            deadlineContainer.classList.remove('d-none');
        } else {
            deadlineContainer.classList.add('d-none');
        }
    }

=======
    let addr = escapeHtml(order.shipping_name) + '<br>' + 
               escapeHtml(order.shipping_address) + '<br>' + 
               escapeHtml(order.shipping_city) + ', ' + escapeHtml(order.shipping_state) + ' ' + escapeHtml(order.shipping_zip) + '<br>' + 
               escapeHtml(order.shipping_country);
    document.getElementById('vo_shipping').innerHTML = addr;
    
    let html = '';
    items.forEach(item => {
        let sub = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
        let name = escapeHtml(item.product_name) || 'Unknown Product';
        html += `<tr>
            <td>${name}</td>
            <td>$${parseFloat(item.price).toFixed(2)}</td>
            <td>${item.quantity}</td>
            <td>$${sub}</td>
        </tr>`;
    });
    document.getElementById('vo_items').innerHTML = html;
    
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    new bootstrap.Modal(document.getElementById('viewOrderModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
