<?php
require_once 'includes/rider_header.php';

$rider_id = $_SESSION['user_id'];

// Get rider info
$rider_q = mysqli_query($conn, "SELECT first_name, last_name, email, profile_picture FROM users WHERE id = $rider_id");
$rider = mysqli_fetch_assoc($rider_q);

// Fixed rate per delivery for earnings
$rate_per_delivery = 1000; // 1000 RFW

// Calculate stats
$stats_q = mysqli_query($conn, "SELECT 
    COUNT(*) as total_assigned,
    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as total_delivered,
    SUM(CASE WHEN status = 'Delivered' THEN total_amount ELSE 0 END) as total_collected
    FROM orders WHERE rider_id = $rider_id AND deleted_at IS NULL");
$stats = mysqli_fetch_assoc($stats_q);

$total_earnings = $stats['total_delivered'] * $rate_per_delivery;

$query = mysqli_query($conn, "SELECT o.*, u.first_name, u.last_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.rider_id = $rider_id AND o.deleted_at IS NULL ORDER BY o.created_at DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $status = clean_input($conn, $_POST['status']);
    
    // Check if order belongs to rider
    $chk = mysqli_query($conn, "SELECT id FROM orders WHERE id = $order_id AND rider_id = $rider_id");
    if (mysqli_num_rows($chk) > 0) {
        // If rider selects Delivered, change to Delivery Requested pending Admin approval
        if ($status === 'Delivered') {
            $status = 'Delivery Requested';
        }
        
        // Handle file upload for proof
        if ($status === 'Delivery Requested' && isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                $filename = 'proof_' . $order_id . '_' . time() . '.' . $ext;
                $dest = '../uploads/' . $filename;
                if (move_uploaded_file($_FILES['proof']['tmp_name'], $dest)) {
                    mysqli_query($conn, "UPDATE orders SET delivery_proof = '$filename' WHERE id = $order_id");
                }
            }
        }
        
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
        
        header("Location: rider_dashboard.php?msg=" . urlencode("Order #$order_id status updated."));
        exit;
    }
}
?>

<style>
@media (max-width: 768px) {
    .rider-profile-card {
        flex-direction: column !important;
        text-align: center;
        padding: 1.5rem !important;
    }
    .rider-stat-card {
        padding: 1.5rem !important;
        flex-direction: column !important;
        text-align: center;
        gap: 0.75rem !important;
    }
    .rider-order-card .d-flex {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
    .rider-order-card .d-flex .rounded-circle {
        margin-bottom: 0.25rem;
    }
    .rider-order-card .input-group {
        flex-direction: column;
    }
    .rider-order-card .input-group .btn {
        width: 100%;
        border-radius: 0.375rem !important;
        margin-top: 0.5rem;
    }
    .rider-order-card .input-group .form-select {
        border-radius: 0.375rem !important;
    }
    .rider-order-card .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
}
</style>

<!-- Profile & Earnings Header -->
<div class="row mb-4 mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, var(--primary) 0%, rgba(11, 61, 110, 0.9) 100%); color: white;">
            <div class="card-body p-4 d-flex align-items-center gap-4 flex-wrap rider-profile-card">
                <div class="rounded-circle shadow" style="width:90px;height:90px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border: 3px solid rgba(255,255,255,0.2);">
                    <?php if(!empty($rider['profile_picture'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($rider['profile_picture']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="fas fa-motorcycle fa-3x" style="color:var(--primary)"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($rider['first_name'] . ' ' . $rider['last_name']) ?>!</h3>
                    <p class="mb-0 opacity-75 fs-6"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($rider['email']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 d-flex flex-row align-items-center gap-3 rider-stat-card" style="transition: transform 0.2s;">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;background:rgba(11,61,110,0.1);color:var(--primary);">
                <i class="fas fa-box-open fa-2x"></i>
            </div>
            <div>
                <p class="text-muted mb-1 small fw-bold" style="letter-spacing: 0.5px;">TOTAL ASSIGNED</p>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_assigned'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 d-flex flex-row align-items-center gap-3 rider-stat-card" style="transition: transform 0.2s;">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;background:rgba(40,167,69,0.1);color:#28a745;">
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
            <div>
                <p class="text-muted mb-1 small fw-bold" style="letter-spacing: 0.5px;">DELIVERED</p>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_delivered'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 d-flex flex-row align-items-center gap-3 rider-stat-card" style="transition: transform 0.2s;">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;background:rgba(251,124,0,0.1);color:var(--accent);">
                <i class="fas fa-wallet fa-2x"></i>
            </div>
            <div>
                <p class="text-muted mb-1 small fw-bold" style="letter-spacing: 0.5px;">MY EARNINGS</p>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_earnings) ?> <span class="fs-6 text-muted">RFW</span></h3>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-list me-2" style="color:var(--primary);"></i> Assigned Tasks</h4>
</div>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success border-0 shadow-sm rounded-3"><i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<?php if(mysqli_num_rows($query) == 0): ?>
<div class="text-center text-muted mt-5 bg-white p-5 rounded-4 shadow-sm">
    <i class="fas fa-wind fa-4x mb-3" style="color: #eee;"></i>
    <h5 class="fw-bold text-dark">You're all caught up!</h5>
    <p>You have no assigned orders right now. Check back later.</p>
</div>
<?php else: ?>
    <div class="row g-4">
    <?php while($o = mysqli_fetch_assoc($query)): ?>
        <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden rider-order-card" style="transition: transform 0.2s;">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-3">
                    <h6 class="m-0 fw-bold text-dark">Order #<?= $o['order_number'] ?: $o['id'] ?></h6>
                    <span class="badge bg-<?= $o['status'] == 'Delivered' ? 'success' : ($o['status'] == 'Pending' ? 'secondary' : 'primary') ?> rounded-pill px-3 py-2 shadow-sm"><?= $o['status'] ?></span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-primary shadow-sm" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark fs-5"><?= htmlspecialchars($o['shipping_name']) ?></span>
                            <a href="tel:<?= htmlspecialchars($o['shipping_phone']) ?>" class="text-decoration-none text-muted fw-medium"><i class="fas fa-phone-alt me-2 text-primary"></i><?= htmlspecialchars($o['shipping_phone']) ?></a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 text-danger shadow-sm" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-bold" style="letter-spacing: 0.5px;">DELIVERY ADDRESS</span>
                            <span class="d-block text-dark mt-1">
                                <?= htmlspecialchars($o['shipping_address']) ?><br>
                                <?= $o['shipping_gate'] ? '<span class="badge bg-light text-dark border border-secondary my-1"><i class="fas fa-door-open me-1 text-primary"></i> Gate: ' . htmlspecialchars($o['shipping_gate']) . '</span><br>' : '' ?>
                                <?= htmlspecialchars($o['shipping_city'] . ', ' . $o['shipping_state']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 mb-4 shadow-sm border">
                        <span class="text-muted fw-bold small" style="letter-spacing: 0.5px;">TOTAL TO COLLECT</span>
                        <span class="fw-bold fs-5" style="color:var(--accent);"><?= number_format($o['total_amount'], 0) ?> RFW</span>
                    </div>
                    
                    <form method="POST" action="rider_dashboard.php" enctype="multipart/form-data" class="d-flex flex-column gap-3 mt-auto">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                              <select name="status" class="form-select status-select fw-bold text-dark border-0 bg-light" id="status_<?= $o['id'] ?>" onchange="checkProof(this, <?= $o['id'] ?>)" <?= $o['status'] === 'Delivered' ? 'disabled' : '' ?>>
                                  <?php if (!in_array($o['status'], ['Delivered', 'Delivery Requested', 'Failed'])): ?>
                                      <option value="<?= htmlspecialchars($o['status']) ?>" selected><?= htmlspecialchars($o['status']) ?> (Current)</option>
                                  <?php endif; ?>
                                  <?php if ($o['status'] === 'Delivery Requested'): ?>
                                      <option value="Delivery Requested" selected>Delivery Requested (Pending Approval)</option>
                                  <?php endif; ?>
                                  <option value="Delivered" <?= $o['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                                  <option value="Failed" <?= $o['status']=='Failed'?'selected':'' ?>>Failed</option>
                              </select>
                              <button type="submit" class="btn btn-primary px-4 update-btn" <?= $o['status'] === 'Delivered' ? 'disabled' : '' ?>><i class="fas fa-check"></i></button>
                        </div>
                        
                        <div id="proof_div_<?= $o['id'] ?>" style="display: <?= $o['status'] == 'Delivered' ? 'block' : 'none' ?>;" class="p-3 bg-light rounded-3 border mt-2">
                            <label class="form-label small fw-bold text-dark mb-2"><i class="fas fa-camera me-2 text-primary"></i>Proof of Delivery (Photo/Signature)</label>
                            <input type="file" name="proof" class="form-control bg-white shadow-sm form-control-sm border-0">
                            <?php if ($o['delivery_proof']): ?>
                            <div class="mt-2 small d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success fs-5"></i> 
                                <span class="text-dark fw-bold">Uploaded</span>
                                <a href="../uploads/<?= htmlspecialchars($o['delivery_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill ms-auto py-0 px-3 fw-bold">View Proof</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
<?php endif; ?>

<script>
function checkProof(selectElem, id) {
    if (selectElem.value === 'Delivered') {
        document.getElementById('proof_div_' + id).style.display = 'block';
    } else {
        document.getElementById('proof_div_' + id).style.display = 'none';
    }
}
// Init all on load
document.querySelectorAll('.status-select').forEach(function(el) {
    checkProof(el, el.id.split('_')[1]);
});

// Add hover effect to cards
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        if(card.style.transition) card.style.transform = 'translateY(-2px)';
    });
    card.addEventListener('mouseleave', () => {
        if(card.style.transition) card.style.transform = 'translateY(0)';
    });
});
</script>

<?php require_once 'includes/rider_footer.php'; ?>
