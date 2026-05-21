<?php
require_once 'includes/header.php';

$orders_query = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");

// Fetch order items to embed
$order_items = [];
$items_query = mysqli_query($conn, "SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id");
while($item = mysqli_fetch_assoc($items_query)) {
    $order_items[$item['order_id']][] = $item;
}
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
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

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
        </div>
    </div>
  </div>
</div>

<script>
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
    document.getElementById('vo_id').innerText = order.id;
    document.getElementById('vo_customer').innerText = order.first_name + ' ' + order.last_name;
    document.getElementById('vo_date').innerText = order.created_at;
    document.getElementById('vo_total').innerText = parseFloat(order.total_amount).toFixed(2);
    document.getElementById('vo_status').innerText = order.status;
    
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
    
    new bootstrap.Modal(document.getElementById('viewOrderModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
