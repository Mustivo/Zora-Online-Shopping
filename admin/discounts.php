<?php
require_once 'includes/header.php';
<<<<<<< HEAD

$query = mysqli_query($conn, "SELECT c.*, p.name as product_name FROM coupons c LEFT JOIN products p ON c.product_id = p.id ORDER BY c.created_at DESC");

$products_query = mysqli_query($conn, "SELECT id, name FROM products ORDER BY name ASC");
$products = [];
while ($p = mysqli_fetch_assoc($products_query)) {
    $products[$p['id']] = $p['name'];
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="admin-page-title"><i class="fas fa-ticket-alt me-2 text-accent"></i> Discounts & Coupons</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal"><i class="fas fa-plus"></i> New Coupon</button>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 12px;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-ticket-alt me-2 text-primary"></i> New Coupon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="actions.php" method="POST">
            <input type="hidden" name="action" value="add_coupon">
            <input type="hidden" name="product_id" value="">
            <div class="mb-3">
                <label class="form-label">Coupon Code</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. SUMMER2026" required style="text-transform: uppercase;">
            </div>
            <div class="mb-3">
                <label class="form-label">Discount Type</label>
                <select name="discount_type" class="form-select" required>
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (RFW)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Discount Value</label>
                <input type="number" name="discount_value" class="form-control" placeholder="10" min="1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Expiry Date (Optional)</label>
                <input type="date" name="expiry_date" class="form-control">
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fas fa-save me-1"></i> Save Coupon</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="admin-card p-4">
    <table class="table admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Discount</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($query) == 0): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No coupons created yet.</td></tr>
            <?php else: $row_count = 1; while($row = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                <td class="fw-bold"><span class="badge bg-dark px-3 py-2 fs-6"><?= htmlspecialchars($row['code']) ?></span></td>
                <td class="text-success fw-bold">
                    <?= $row['discount_type'] == 'percentage' ? $row['discount_value'] . '%' : number_format($row['discount_value'], 0) . ' RFW' ?>
                </td>
                <td class="text-muted"><?= $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : 'Never' ?></td>
                <td>
                    <?php 
                    $is_expired = false;
                    if ($row['expiry_date'] && strtotime($row['expiry_date']) < strtotime(date('Y-m-d'))) {
                        $is_expired = true;
                    }
                    ?>
                    <?php if($is_expired): ?>
                        <span class="badge bg-secondary">Expired</span>
                    <?php elseif($row['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Disabled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form action="actions.php" method="POST" class="d-inline" onsubmit="return customConfirm(event, 'Toggle status for this coupon?')">
                        <input type="hidden" name="action" value="toggle_coupon">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="status" value="<?= $row['is_active'] ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $row['is_active'] ? 'Disable' : 'Enable' ?>" <?= $is_expired ? 'disabled' : '' ?>><i class="fas fa-power-off"></i></button>
                    </form>
                    <form action="actions.php" method="POST" class="d-inline" onsubmit="return customConfirm(event, 'Delete coupon permanently?')">
                        <input type="hidden" name="action" value="delete_coupon">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelector('input[name="code"]').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});
</script>
=======
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title">Discounts & Coupons</h2>
</div>

<div class="admin-card p-5 text-center mt-4">
    <i class="fas fa-tools fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
    <h3 class="mt-3">Under Construction</h3>
    <p class="text-muted">The Discounts & Coupons module is currently being built. Check back soon!</p>
</div>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
