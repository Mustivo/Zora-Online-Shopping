<?php
require_once 'includes/header.php';

<<<<<<< HEAD
$cats_query = mysqli_query($conn, "SELECT c1.*, c2.name as parent_name FROM categories c1 LEFT JOIN categories c2 ON c1.parent_id = c2.id ORDER BY c1.parent_id ASC, c1.name ASC");
$categories_list = [];
while ($row = mysqli_fetch_assoc($cats_query)) {
    $categories_list[] = $row;
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="admin-page-title"><i class="fas fa-tags me-2 text-accent"></i> Categories</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="actions.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" name="action" value="add_category">
            
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Category Image (File or URL)</label>
                <input type="file" name="image" class="form-control mb-2" accept="image/*">
                <input type="url" name="image_url" class="form-control" placeholder="OR Paste Image URL here">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">No Parent (Top Level)</option>
                    <?php foreach($categories_list as $c): if(empty($c['parent_id'])): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="table-responsive">
    <table class="table admin-table align-middle">
        <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Parent Category</th><th>Action</th></tr></thead>
        <tbody>
            <?php $row_count = 1; foreach($categories_list as $c): ?>
            <tr>
                <td><?= $row_count++ ?></td>
                <td>
                    <?php if(!empty($c['image']) && file_exists('../uploads/'.$c['image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($c['image']) ?>" alt="cat" style="width: 40px; height: 40px; object-fit: contain; border-radius: 5px;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px; border-radius: 5px; font-size: 0.8rem;">None</div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($c['parent_id'])): ?>
                        <span class="text-muted ms-3">↳</span> 
                    <?php endif; ?>
                    <?= htmlspecialchars($c['name']) ?>
                </td>
                <td><?= $c['parent_name'] ? htmlspecialchars($c['parent_name']) : '<span class="badge bg-secondary">Top Level</span>' ?></td>
                <td>
                    <button class="btn btn-sm btn-info text-white" onclick='editCategory(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8") ?>)'><i class="fas fa-edit"></i></button>
                    <form action="actions.php" method="POST" class="d-inline" onsubmit="return customConfirm(event, 'Delete category? It will also delete its subcategories.')">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
=======
$cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Categories</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addCatForm').toggleAttribute('hidden')">Add Category</button>
</div>

<div id="addCatForm" class="admin-card p-4 mb-4" hidden>
    <form action="actions.php" method="POST">
        <input type="hidden" name="action" value="add_category">
        <div class="row g-3 align-items-center">
            <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Category Name" required></div>
            <div class="col-md-4"><input type="text" name="icon" class="form-control" placeholder="Icon Class (e.g. fas fa-box)"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-success w-100">Save</button></div>
        </div>
    </form>
</div>

<table class="table admin-table">
    <thead><tr><th>ID</th><th>Icon</th><th>Name</th><th>Action</th></tr></thead>
    <tbody>
        <?php mysqli_data_seek($cats_query, 0); while($c = mysqli_fetch_assoc($cats_query)): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><i class="<?= htmlspecialchars($c['icon']) ?>"></i></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td>
                <button class="btn btn-sm btn-info text-white" onclick='editCategory(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8") ?>)'><i class="fas fa-edit"></i></button>
                <form action="actions.php" method="POST" class="d-inline" onsubmit="return confirm('Delete category?')">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

<!-- Edit Category Modal -->
<div class="modal fade" id="editCatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
<<<<<<< HEAD
      <form action="actions.php" method="POST" enctype="multipart/form-data">
=======
      <form action="actions.php" method="POST">
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        <div class="modal-header border-0">
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_category">
            <input type="hidden" name="category_id" id="edit_cat_id">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" id="edit_cat_name" class="form-control" required>
            </div>
            <div class="mb-3">
<<<<<<< HEAD
                <label class="form-label">Parent Category</label>
                <select name="parent_id" id="edit_cat_parent" class="form-select">
                    <option value="">No Parent (Top Level)</option>
                    <?php foreach($categories_list as $c): if(empty($c['parent_id'])): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Category Image (File or URL - leave empty to keep current)</label>
                <input type="file" name="image" class="form-control mb-2" accept="image/*">
                <input type="url" name="image_url" class="form-control" placeholder="OR Paste Image URL here">
=======
                <label class="form-label">Icon Class</label>
                <input type="text" name="icon" id="edit_cat_icon" class="form-control">
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
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
function editCategory(cat) {
    document.getElementById('edit_cat_id').value = cat.id;
    document.getElementById('edit_cat_name').value = cat.name;
<<<<<<< HEAD
    document.getElementById('edit_cat_parent').value = cat.parent_id || '';
    
    let parentSelect = document.getElementById('edit_cat_parent');
    for (let i = 0; i < parentSelect.options.length; i++) {
        if (parentSelect.options[i].value == cat.id) {
            parentSelect.options[i].disabled = true;
        } else {
            parentSelect.options[i].disabled = false;
        }
    }
    
=======
    document.getElementById('edit_cat_icon').value = cat.icon;
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
