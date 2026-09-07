<?php
require_once 'includes/header.php';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = clean_input($conn, $_POST['name']);
        $price = (float)$_POST['price'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0) {
            $sql = "UPDATE delivery_methods SET name='$name', price='$price', is_active='$is_active' WHERE id=$id";
        } else {
            $sql = "INSERT INTO delivery_methods (name, price, is_active) VALUES ('$name', '$price', '$is_active')";
        }
        mysqli_query($conn, $sql);
        echo "<script>window.location.href='delivery_methods.php?msg=" . urlencode("Saved successfully") . "';</script>";
        exit;
    }
    
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM delivery_methods WHERE id=$id");
        echo "<script>window.location.href='delivery_methods.php?msg=" . urlencode("Deleted successfully") . "';</script>";
        exit;
    }
}

$query = "SELECT * FROM delivery_methods ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$methods = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)){
        $methods[] = $row;
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-truck me-2 text-accent"></i> Delivery Methods</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editMethod(0, '', 0, 1)">
        <i class="fas fa-plus me-2"></i>Add Method
    </button>
</div>

<div class="admin-card p-4">
    <table class="table admin-table align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Name</th>
                <th>Price (RFW)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $row_count = 1; foreach($methods as $m): ?>
            <tr>
                <td class="text-muted fw-bold"><?= $row_count++ ?></td>
                <td class="text-muted fw-bold">#<?= $m['id'] ?></td>
                <td class="fw-bold"><?= htmlspecialchars($m['name']) ?></td>
                <td class="text-success fw-bold"><?= number_format($m['price'], 0) ?></td>
                <td>
                    <?php if($m['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-info me-2" onclick="editMethod(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['name'])) ?>', <?= $m['price'] ?>, <?= $m['is_active'] ?>)" data-bs-toggle="modal" data-bs-target="#editModal">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="" method="POST" class="d-inline" onsubmit="return customConfirm(event, 'Delete this delivery method?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($methods)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">No delivery methods found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="modalTitle">Add Delivery Method</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST">
      <div class="modal-body">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" id="methodId" value="0">
          
          <div class="mb-3">
              <label class="form-label">Method Name</label>
              <input type="text" class="form-control" name="name" id="methodName" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Price (RFW)</label>
              <input type="number" class="form-control" name="price" id="methodPrice" step="0.01" required>
          </div>
          <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" name="is_active" id="methodActive" value="1" checked>
              <label class="form-check-label">Active</label>
          </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
function editMethod(id, name, price, isActive) {
    document.getElementById('methodId').value = id;
    document.getElementById('methodName').value = name;
    document.getElementById('methodPrice').value = price;
    document.getElementById('methodActive').checked = (isActive == 1);
    document.getElementById('modalTitle').innerText = id > 0 ? 'Edit Delivery Method' : 'Add Delivery Method';
}
</script>

<?php require_once 'includes/footer.php'; ?>
