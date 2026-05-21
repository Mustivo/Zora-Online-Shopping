<?php
require_once 'includes/header.php';

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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
      <form action="actions.php" method="POST">
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
                <label class="form-label">Icon Class</label>
                <input type="text" name="icon" id="edit_cat_icon" class="form-control">
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
    document.getElementById('edit_cat_icon').value = cat.icon;
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
