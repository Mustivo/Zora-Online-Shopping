<?php
require_once 'includes/header.php';
<<<<<<< HEAD

// Inventory summary calculations
$total_products_inv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'] ?? 0;
$total_in_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(stock), 0) as c FROM products"))['c'] ?? 0;
$total_stock_out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(oi.quantity), 0) as c FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.status = 'delivered'"))['c'] ?? 0;
$total_out_of_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE stock <= 0"))['c'] ?? 0;

$query = mysqli_query($conn, "SELECT p.id, p.name, p.image, p.stock, c.name as cat_name, (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.product_id = p.id AND o.status = 'delivered') as delivered_qty FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.stock ASC");
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-warehouse me-2 text-accent"></i> Inventory Management</h2>
</div>

<!-- INVENTORY KPI CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon primary"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($total_products_inv) ?></div>
                <div class="kpi-label">Total Products</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon success"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="kpi-value" style="color: #10b981;"><?= number_format($total_in_stock) ?></div>
                <div class="kpi-label">In Stock (Remaining)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card accent">
            <div class="kpi-icon accent"><i class="fas fa-truck-loading"></i></div>
            <div>
                <div class="kpi-value" style="color: var(--accent);"><?= number_format($total_stock_out) ?></div>
                <div class="kpi-label">Stock Out (Sold Units)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card accent">
            <div class="kpi-icon info" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="kpi-value" style="color: #ef4444;"><?= number_format($total_out_of_stock) ?></div>
                <div class="kpi-label">Out of Stock Items</div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card p-4">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Remaining Stock</th>
                    <th>Units Sold</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $row_count = 1; while($row = mysqli_fetch_assoc($query)): 
                    $stock = (int)$row['stock'];
                    $delivered = (int)($row['delivered_qty'] ?? 0);
                    if ($stock <= 0) {
                        $status_badge = '<span class="status-badge cancelled">Out of Stock (0)</span>';
                    } elseif ($stock < 10) {
                        $status_badge = '<span class="status-badge pending">Low Stock ('.$stock.' left)</span>';
                    } else {
                        $status_badge = '<span class="status-badge delivered">In Stock ('.$stock.')</span>';
                    }
                ?>
                <tr>
                    <td class="text-muted"><?= $row_count++ ?></td>
                    <td>
                        <?php if (!empty($row['image']) && file_exists("../uploads/" . $row['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="Product" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px; border-radius: 6px;"><i class="fas fa-box"></i></div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                    <td><span class="badge" style="background: var(--bg3); color: var(--text); border: 1px solid var(--border);"><?= htmlspecialchars($row['cat_name'] ?? 'Uncategorized') ?></span></td>
                    <td class="fw-bold fs-6 <?= $stock <= 0 ? 'text-danger' : ($stock < 10 ? 'text-warning' : 'text-success') ?>">
                        <?= number_format($stock) ?>
                    </td>
                    <td class="fw-bold text-muted">
                        <?= number_format($delivered) ?> sold
                    </td>
                    <td><?= $status_badge ?></td>
                    <td>
                        <a href="product_colors.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i> Update</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

=======
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title">Inventory Management</h2>
</div>

<div class="admin-card p-5 text-center mt-4">
    <i class="fas fa-tools fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
    <h3 class="mt-3">Under Construction</h3>
    <p class="text-muted">The Inventory Management module is currently being built. Check back soon!</p>
</div>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
