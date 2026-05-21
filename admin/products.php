<?php
require_once 'includes/header.php';

$products_query = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = mysqli_fetch_assoc($cats_query)) {
    $categories[] = $c;
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Products</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addProductForm').toggleAttribute('hidden')">Add Product</button>
</div>

<div id="addProductForm" class="admin-card p-4 mb-4" hidden>
    <form action="actions.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <div class="row g-3">
            <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Product Name" required></div>
            <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
            <div class="col-md-3"><input type="number" name="stock" class="form-control" placeholder="Stock" required></div>
            <div class="col-md-6">
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-12"><textarea name="description" class="form-control" placeholder="Description" rows="3"></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-success">Save Product</button></div>
        </div>
    </form>
</div>

<table class="table admin-table">
    <thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
    <tbody>
        <?php mysqli_data_seek($products_query, 0); while($p = mysqli_fetch_assoc($products_query)): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td>
                <?php if($p['image'] && file_exists("../uploads/" . $p['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img-preview">
                <?php else: ?>
                    <div class="product-img-preview bg-secondary d-flex align-items-center justify-content-center" style="width:50px;height:50px"><i class="fas fa-box text-white"></i></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['cat_name'] ?? 'None') ?></td>
            <td>$<?= number_format($p['price'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
                <a href="product_colors.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning text-white" title="Manage Colors"><i class="fas fa-palette"></i></a>
                <button class="btn btn-sm btn-info text-white" onclick='editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'><i class="fas fa-edit"></i></button>
                <form action="actions.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
      <form action="actions.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header border-0">
          <h5 class="modal-title">Edit Product</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="edit_stock" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="edit_category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Update Image (Optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_description').value = product.description;
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
