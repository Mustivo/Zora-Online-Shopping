<?php
require_once 'includes/header.php';

<<<<<<< HEAD
$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$whereClause = "";
if (!empty($search)) {
    $whereClause = "WHERE p.name LIKE '%$search%' OR p.tags LIKE '%$search%' ";
}
$products_query = mysqli_query($conn, "SELECT p.*, c.name as cat_name, (SELECT SUM(oi.quantity) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.product_id = p.id AND o.status = 'delivered') as delivered_qty FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause ORDER BY p.id DESC");
=======
$products_query = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
$cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = mysqli_fetch_assoc($cats_query)) {
    $categories[] = $c;
}
?>
<<<<<<< HEAD
<!-- Include Tagify -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<style>
/* Custom Tagify Styling to match theme */
.tagify {
    --tags-border-color: var(--border);
    --tags-hover-border-color: var(--accent);
    --tags-focus-border-color: var(--accent);
    --tag-bg: var(--bg3);
    --tag-hover: var(--bg1);
    --tag-text-color: var(--text);
    --tag-text-color--edit: var(--text);
    --tag-pad: 0.3rem 0.5rem;
    --tag-inset-shadow-size: 1.1em;
    --tag-invalid-color: #ff3e1d;
    --tag-invalid-bg: rgba(255, 62, 29, 0.5);
    background: var(--bg2);
    border-radius: 6px;
    padding: 0;
}
.tagify__input {
    color: var(--text);
}
.tagify__tag > div::before {
    box-shadow: 0 0 0 var(--tag-inset-shadow-size) var(--tag-bg) inset;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="admin-page-title"><i class="fas fa-box me-2 text-accent"></i> Products</h2>
    <a href="add_product.php" class="btn btn-primary">Add Product</a>
</div>

<form action="actions.php" method="POST" id="bulkProductsForm">
<input type="hidden" name="action" value="bulk_delete_products">

<div id="bulkProductsBar" class="admin-card p-3 mb-3 d-none align-items-center justify-content-between bg-primary bg-opacity-10 border border-primary">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-primary fs-5"></i>
        <span class="fw-bold"><span id="productsSelectedCount">0</span> product(s) selected</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-sm btn-danger shadow-sm px-3" onclick="return customConfirm(event, 'Permanently delete selected products and their images?')">
            <i class="fas fa-trash-alt me-1"></i> Delete Selected
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedProducts()">
            Deselect All
        </button>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive border-0">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" class="form-check-input" id="selectAllProducts" onchange="toggleSelectAllProducts(this)" title="Select All Products">
                    </th>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $row_count = 1; mysqli_data_seek($products_query, 0); while($p = mysqli_fetch_assoc($products_query)): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input product-row-chk" name="product_ids[]" value="<?= $p['id'] ?>" onchange="updateBulkProductsToolbar()">
                    </td>
                    <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                    <td>
                        <?php if($p['image'] && file_exists("../uploads/" . $p['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img-preview" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                            <div class="product-img-preview d-flex align-items-center justify-content-center" style="background: var(--bg3); width:50px;height:50px;border-radius:8px"><i class="fas fa-box" style="color: var(--text3);"></i></div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                    <td><span class="badge" style="background: var(--bg3); color: var(--text); border: 1px solid var(--border);"><?= htmlspecialchars($p['cat_name'] ?? 'None') ?></span></td>
                    <td class="text-primary fw-bold"><?= number_format($p['price'], 0) ?> RFW</td>
                    <td>
                        <?php if($p['stock'] > 0): ?>
                            <span class="status-badge delivered"><?= number_format($p['stock']) ?> in stock</span>
                        <?php else: ?>
                            <span class="status-badge cancelled" title="Delivered <?= (int)$p['delivered_qty'] ?> units">Out of Stock (0)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <a href="product_colors.php?id=<?= $p['id'] ?>" class="admin-action-btn" title="Update Product"><i class="fas fa-edit text-info"></i></a>
                            <form action="actions.php" method="POST" class="m-0" onsubmit="return customConfirm(event, 'Delete this product permanently?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="admin-action-btn danger"><i class="fas fa-trash text-danger"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</form>

<script>
function toggleSelectAllProducts(master) {
    document.querySelectorAll('.product-row-chk').forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkProductsToolbar();
}

function updateBulkProductsToolbar() {
    const checked = document.querySelectorAll('.product-row-chk:checked');
    const count = checked.length;
    const bar = document.getElementById('bulkProductsBar');
    const countSpan = document.getElementById('productsSelectedCount');
    const master = document.getElementById('selectAllProducts');
    
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

function clearSelectedProducts() {
    document.querySelectorAll('.product-row-chk').forEach(cb => { cb.checked = false; });
    const master = document.getElementById('selectAllProducts');
    if (master) master.checked = false;
    updateBulkProductsToolbar();
}

// Initialize Tagify if element exists
const addTagsEl = document.getElementById('add_tags');
if (addTagsEl) {
    new Tagify(addTagsEl, {
        delimiters: ",|\\n|\\r|\\t|;|•|•|- |#",
        trim: true,
        duplicates: false,
        transformTag: function(tagData) {
            let val = tagData.value || '';
            val = val.replace(/^[\s\d\.\-\*•#–—]+/, '');
            val = val.replace(/^["'`]|["'`]$/g, '');
            tagData.value = val.trim();
        }
    });
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
}
</script>

<?php require_once 'includes/footer.php'; ?>
