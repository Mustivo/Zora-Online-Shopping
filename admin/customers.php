<?php
require_once 'includes/header.php';

$query = mysqli_query($conn, "SELECT id, first_name, last_name, email, created_at, (SELECT COUNT(id) FROM orders WHERE user_id = users.id) as order_count FROM users WHERE role = 'user' ORDER BY created_at DESC");
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-users me-2 text-accent"></i> Customer Management</h2>
</div>

<form action="actions.php" method="POST" id="bulkCustomersForm">
<input type="hidden" name="action" value="bulk_delete_customers">

<div id="bulkCustomersBar" class="admin-card p-3 mb-3 d-none align-items-center justify-content-between bg-primary bg-opacity-10 border border-primary">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-primary fs-5"></i>
        <span class="fw-bold"><span id="customersSelectedCount">0</span> customer(s) selected</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-sm btn-danger shadow-sm px-3" onclick="return customConfirm(event, 'Permanently delete selected customer accounts?')">
            <i class="fas fa-trash-alt me-1"></i> Delete Selected
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedCustomers()">
            Deselect All
        </button>
    </div>
</div>

<div class="admin-card overflow-hidden">
    <div class="table-responsive border-0">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="selectAllCustomers" onchange="toggleSelectAllCustomers(this)" title="Select All Customers">
                    </th>
                    <th>#</th>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Total Orders</th>
                    <th>Joined Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($query) == 0): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No customers registered yet.</td></tr>
                <?php else: $row_count = 1; while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input customer-row-chk" name="customer_ids[]" value="<?= $row['id'] ?>" onchange="updateBulkCustomersToolbar()">
                    </td>
                    <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                    <td class="text-muted fw-bold">#<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary fw-bold shadow-sm" style="width:32px;height:32px;font-size:0.85rem">
                                <?= strtoupper(substr($row['first_name'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></span>
                        </div>
                    </td>
                    <td class="text-muted" style="font-size:0.85rem"><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge bg-light text-dark border rounded-pill px-3 py-1 shadow-sm"><i class="fas fa-shopping-bag me-1 text-primary"></i> <?= $row['order_count'] ?></span></td>
                    <td class="text-muted" style="font-size:0.85rem"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <a href="mailto:<?= htmlspecialchars($row['email']) ?>" class="admin-action-btn" title="Email Customer"><i class="fas fa-envelope text-info"></i></a>
                            <form action="actions.php" method="POST" class="m-0" onsubmit="return customConfirm(event, 'Are you sure you want to delete this customer account permanently?')">
                                <input type="hidden" name="action" value="delete_customer">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="admin-action-btn danger"><i class="fas fa-trash text-danger"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</form>

<script>
function toggleSelectAllCustomers(master) {
    document.querySelectorAll('.customer-row-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkCustomersToolbar();
}

function updateBulkCustomersToolbar() {
    const checked = document.querySelectorAll('.customer-row-chk:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkCustomersBar');
    const countSpan = document.getElementById('customersSelectedCount');
    const master = document.getElementById('selectAllCustomers');
    
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

function clearSelectedCustomers() {
    document.querySelectorAll('.customer-row-chk').forEach(cb => { cb.checked = false; });
    const master = document.getElementById('selectAllCustomers');
    if (master) master.checked = false;
    updateBulkCustomersToolbar();
}
</script>

<?php require_once 'includes/footer.php'; ?>
