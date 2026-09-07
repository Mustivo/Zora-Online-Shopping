<?php
require_once 'includes/header.php';

$query = mysqli_query($conn, "SELECT * FROM users WHERE role IN ('admin', 'store_manager') ORDER BY created_at ASC");
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-user-shield me-2 text-accent"></i> Roles & Permissions</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="fas fa-user-plus"></i> Add Staff</button>
</div>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success border-0 shadow-sm rounded-3"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 12px;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i> Add Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="actions.php" method="POST">
            <input type="hidden" name="action" value="add_staff">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="password-toggle-wrap">
                    <input type="password" name="password" class="form-control" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="store_manager">Store Manager</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold">Add Staff</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="admin-card p-4">
    <table class="table admin-table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-accent d-flex align-items-center justify-content-center text-white fw-bold" style="width:35px;height:35px;">
                            <?= strtoupper(substr($row['first_name'], 0, 1)) ?>
                        </div>
                        <div class="fw-bold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                    </div>
                </td>
                <td class="text-muted"><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <span class="badge bg-<?= $row['role'] == 'admin' ? 'primary' : 'info' ?>">
                        <?= $row['role'] == 'admin' ? 'Administrator' : 'Store Manager' ?>
                    </span>
                </td>
                <td class="text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editStaff(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Staff"><i class="fas fa-edit"></i></button>
                    <?php if($row['id'] != $_SESSION['user_id']): ?>
                    <form action="actions.php" method="POST" class="d-inline" onsubmit="return customConfirm(event, 'Delete this user?')">
                        <input type="hidden" name="action" value="revoke_staff">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php else: ?>
                        <span class="badge bg-secondary">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 12px;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2 text-primary"></i> Edit Staff Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="actions.php" method="POST">
            <input type="hidden" name="action" value="update_staff">
            <input type="hidden" name="id" id="edit_staff_id">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" id="edit_staff_fname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" id="edit_staff_lname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_staff_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" id="edit_staff_role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="store_manager">Store Manager</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                <div class="password-toggle-wrap">
                    <input type="password" name="password" class="form-control">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function editStaff(staff) {
    document.getElementById('edit_staff_id').value = staff.id;
    document.getElementById('edit_staff_fname').value = staff.first_name;
    document.getElementById('edit_staff_lname').value = staff.last_name;
    document.getElementById('edit_staff_email').value = staff.email;
    if(document.getElementById('edit_staff_role')) {
        document.getElementById('edit_staff_role').value = staff.role;
    }
    
    let modalEl = document.getElementById('editStaffModal');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if(!modal) { modal = new bootstrap.Modal(modalEl); }
    modal.show();
}
</script>

<div class="admin-card p-4 mt-4">
    <h4 class="mb-3"><i class="fas fa-paint-roller me-2 text-accent"></i> Site Appearance</h4>
    <form action="actions.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_appearance">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
<?php
                $bg_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_background_image'");
                $current_bg = ($bg_query && mysqli_num_rows($bg_query) > 0) ? mysqli_fetch_assoc($bg_query)['setting_value'] : '';
                ?>
                <label class="form-label fw-bold"><i class="fas fa-image me-1 text-accent"></i> Hero Background Image</label>
                <?php if (!empty($current_bg) && file_exists('../uploads/' . $current_bg)): ?>
                    <div class="mb-2 position-relative d-inline-block">
                        <img src="../uploads/<?= htmlspecialchars($current_bg) ?>" alt="Hero Background" style="height: 55px; width: 110px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);">
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="remove_bg_image" value="1" id="remove_bg_image">
                            <label class="form-check-label text-danger small fw-bold" for="remove_bg_image">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="bg_image" class="form-control" accept="image/*">
                <?php if($current_bg): ?>
                    <small class="text-muted d-block mt-1">Current: <code><?= htmlspecialchars($current_bg) ?></code></small>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Website Logo</label>
                <input type="file" name="logo_image" class="form-control" accept="image/*">
                <?php if(file_exists('../uploads/logo.png')): ?>
                    <small class="text-muted d-block mt-1">Current: logo.png</small>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Font Family</label>
<?php
                $font_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_font_family'");
                $current_font = ($font_query && mysqli_num_rows($font_query) > 0) ? htmlspecialchars_decode(mysqli_fetch_assoc($font_query)['setting_value']) : "'DM Sans', sans-serif";
                
                $fonts = [
                    "'DM Sans', sans-serif" => "DM Sans (Default)",
                    "'Roboto', sans-serif" => "Roboto",
                    "'Open Sans', sans-serif" => "Open Sans",
                    "'Montserrat', sans-serif" => "Montserrat",
                    "'Playfair Display', serif" => "Playfair Display",
                    "'Inter', sans-serif" => "Inter"
                ];
                ?>
                <select name="font_family" class="form-select">
                    <?php foreach($fonts as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= ($current_font === $val) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Save</button>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
