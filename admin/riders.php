<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store_manager'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role = 'rider'");
    header("Location: riders.php?msg=" . urlencode("Rider removed successfully."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_rider') {
    $first_name = clean_input($conn, $_POST['first_name']);
    $last_name = clean_input($conn, $_POST['last_name']);
    $email = clean_input($conn, $_POST['email']);
    $phone = clean_input($conn, $_POST['phone'] ?? '');
    $raw_password = $_POST['password'];
    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        mysqli_query($conn, "INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES ('$first_name', '$last_name', '$email', '$phone', '$password', 'rider')");
        
        // Send email to new rider
        require_once __DIR__ . '/../core/mailer.php';
        $subject = "Welcome to Zora - Delivery Rider Account";
        $login_link = BASE_URL . "admin/login.php";
        
        $htmlBody = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>";
        $htmlBody .= "<div style='background-color: #0b3d6e; padding: 20px; text-align: center; color: white;'>";
        $htmlBody .= "<h1 style='margin: 0; font-size: 24px;'>Zora Online Shopping Rwanda</h1>";
        $htmlBody .= "</div>";
        $htmlBody .= "<div style='padding: 20px;'>";
        $htmlBody .= "<h2 style='color: #0b3d6e;'>Hello $first_name $last_name,</h2>";
        $htmlBody .= "<p>Your <strong>Delivery Rider</strong> account has been created successfully.</p>";
        $htmlBody .= "<p>Here are your login credentials:</p>";
        $htmlBody .= "<div style='background-color: #f1f3f5; padding: 15px; border-radius: 6px; margin: 15px 0;'>";
        $htmlBody .= "<p style='margin: 0;'><strong>Username/Email:</strong> $email</p>";
        $htmlBody .= "<p style='margin: 0;'><strong>Password:</strong> $raw_password</p>";
        $htmlBody .= "</div>";
        $htmlBody .= "<p style='text-align: center; margin-top: 30px;'>";
        $htmlBody .= "<a href='$login_link' style='background-color: #ff9900; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to your Panel</a>";
        $htmlBody .= "</p>";
        $htmlBody .= "</div>";
        $htmlBody .= "<div style='background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>";
        $htmlBody .= "<p>If you have any questions, please contact the main administrator.</p>";
        $htmlBody .= "</div></div>";
        
        sendMail($email, "$first_name $last_name", $subject, $htmlBody);

        header("Location: riders.php?msg=" . urlencode("Rider added successfully and email sent."));
        exit;
    }
}

require_once 'includes/header.php';

$query = mysqli_query($conn, "SELECT id, first_name, last_name, email, phone, created_at, (SELECT COUNT(id) FROM orders WHERE rider_id = users.id) as order_count FROM users WHERE role = 'rider' ORDER BY created_at DESC");
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-motorcycle me-2 text-accent"></i> Delivery Riders</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRiderModal"><i class="fas fa-plus"></i> Add Rider</button>
</div>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="admin-card overflow-hidden">
    <div class="table-responsive border-0">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Rider ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Assigned Orders</th>
                    <th>Joined Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($query) == 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No riders registered yet.</td></tr>
                <?php else: $row_count = 1; while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
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
                    <td style="font-size:0.85rem">
                        <div class="text-muted"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($row['email']) ?></div>
                        <?php if(!empty($row['phone'])): ?>
                        <div class="text-muted mt-1"><i class="fas fa-phone-alt me-1"></i> <?= htmlspecialchars($row['phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border rounded-pill px-3 py-1 shadow-sm"><i class="fas fa-box me-1 text-primary"></i> <?= $row['order_count'] ?></span></td>
                    <td class="text-muted" style="font-size:0.85rem"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" onclick="editRider(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Rider"><i class="fas fa-edit"></i></button>
                        <a href="riders.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger shadow-sm rounded-pill px-3" onclick="return customConfirm(event, 'Remove this rider?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Rider Modal -->
<div class="modal fade" id="addRiderModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content border-0 shadow-lg" method="POST" action="riders.php">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Add Delivery Rider</h5>
        <button type="button" class="btn-close shadow-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="add_rider">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email (Login ID)</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 078...">
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
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fas fa-plus"></i> Add Rider</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Rider Modal -->
<div class="modal fade" id="editRiderModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content border-0 shadow-lg" method="POST" action="actions.php">
      <input type="hidden" name="action" value="update_staff">
      <input type="hidden" name="id" id="edit_rider_id">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Delivery Rider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">First Name</label>
                <input type="text" name="first_name" id="edit_rider_fname" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Last Name</label>
                <input type="text" name="last_name" id="edit_rider_lname" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Email</label>
                <input type="email" name="email" id="edit_rider_email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Phone Number</label>
                <input type="text" name="phone" id="edit_rider_phone" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label text-muted small fw-bold">New Password <span class="fw-normal">(Leave blank to keep)</span></label>
                <div class="password-toggle-wrap">
                    <input type="password" name="password" class="form-control">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function editRider(rider) {
    document.getElementById('edit_rider_id').value = rider.id;
    document.getElementById('edit_rider_fname').value = rider.first_name;
    document.getElementById('edit_rider_lname').value = rider.last_name;
    document.getElementById('edit_rider_email').value = rider.email;
    document.getElementById('edit_rider_phone').value = rider.phone || '';
    
    let modalEl = document.getElementById('editRiderModal');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if(!modal) { modal = new bootstrap.Modal(modalEl); }
    modal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
