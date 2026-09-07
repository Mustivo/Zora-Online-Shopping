<?php
require_once 'core/config.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id === 0) {
    header("Location: index.php");
    exit;
}

// Check session to allow viewing it immediately after placing
if (!isset($_SESSION['last_order_id']) || $_SESSION['last_order_id'] != $order_id) {
    header("Location: order_track.php?id=$order_id");
    exit;
}

$query = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get subtotal from order_items
$items_q = "SELECT SUM(price * quantity) as subtotal FROM order_items WHERE order_id = $order_id";
$items_res = mysqli_query($conn, $items_q);
$items_row = mysqli_fetch_assoc($items_res);
$subtotal = $items_row['subtotal'] ?? 0;

$total_amount = $order['total_amount'];
$shipping_and_other = $total_amount - $subtotal;

$ussd_amount = round($total_amount);
$ussd_code = "*182*8*1* 675349*" . $ussd_amount . "#";
$ussd_link = "tel:" . urlencode("*182*8*1*675349*" . $ussd_amount . "#"); // Actual link without space

// Determine email
$email_display = $order['guest_email'];
if (!empty($order['user_id'])) {
    $u_q = mysqli_query($conn, "SELECT email FROM users WHERE id = " . $order['user_id']);
    if ($u_q && mysqli_num_rows($u_q) > 0) {
        $u_row = mysqli_fetch_assoc($u_q);
        $email_display = $u_row['email'];
    }
}
if (empty($email_display)) {
    $email_display = "N/A";
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
        <h2 class="section-title">Thank you for shopping with us!</h2>
        <p class="text-muted-custom mb-2" style="font-size: 1.1rem;">We truly appreciate your business. Your order is now being processed.</p>
        <p class="text-muted-custom small" style="max-width: 600px; margin: 0 auto; line-height: 1.5;">
            A confirmation email has been sent to <strong><?= htmlspecialchars($email_display) ?></strong> with your product details.<br>
            You can always check the status of your order by clicking the <strong>Track Your Order</strong> button below and entering your Order Number.
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="admin-card p-4 mb-4 shadow-sm" style="background: var(--bg2);">
                <h4 class="admin-card-title mb-3">Order Details</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted-custom">Order Number:</span>
                    <span class="fw-bold"><?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted-custom">Email:</span>
                    <span><?= htmlspecialchars($email_display) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted-custom">Status:</span>
                    <span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 50px;"><?= htmlspecialchars($order['status'] ?? 'Pending') ?></span>
                </div>
            </div>

            <div class="admin-card p-4 mb-4 shadow-sm" style="background: var(--bg2);">
                <h4 class="admin-card-title mb-3">Payment Summary</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted-custom">Subtotal:</span>
                    <span><?= number_format($subtotal, 0) ?> RFW</span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-secondary">
                    <span class="text-muted-custom">Delivery Fee:</span>
                    <span><?= number_format($shipping_and_other, 0) ?> RFW</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">Total to Pay:</span>
                    <span class="fw-bold fs-4 gold"><?= number_format($total_amount, 0) ?> RFW</span>
                </div>
            </div>

            <div class="admin-card p-4 text-center shadow-sm" style="background: linear-gradient(135deg, var(--bg2), var(--bg)); border: 1px solid var(--border);">
                <div class="mb-3">
                    <i class="fas fa-mobile-alt text-warning" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-3">Pay with Mobile Money</h4>
                
                <a href="<?= $ussd_link ?>" class="btn-primary-full mb-4 py-3 fs-5" style="border-radius: 50px; display: block; max-width: 300px; margin: 0 auto;">
                    <i class="fas fa-hand-pointer me-2"></i> Click to Pay
                </a>
                
                <div class="alert mt-4 p-3" style="background-color: rgba(255,193,7,0.1); border: 1px dashed rgba(255,193,7,0.5); color: var(--text);">
                    <strong>Manual Dial Instruction:</strong><br>
                    <span class="text-muted-custom small">If the button doesn't work, manually dial:</span><br>
                    <span class="fs-5 fw-bold mt-2 d-inline-block gold"><?= htmlspecialchars($ussd_code) ?></span>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <?php $track_param = !empty($order['order_number']) ? 'order_number=' . urlencode($order['order_number']) : 'id=' . $order['id']; ?>
                <a href="order_track.php?<?= $track_param ?>" class="btn-hero-outline">Track Your Order</a>
            </div>
        </div>
    </div>
</div>

<!-- Appreciation Modal -->
<div class="modal fade" id="appreciationModal" tabindex="-1" aria-labelledby="appreciationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <div class="modal-header border-0 pb-4 pt-4 position-relative" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: white;">
        <h5 class="modal-title w-100 text-center fw-bold" id="appreciationModalLabel" style="font-size: 1.5rem; color: white;">
            <i class="fas fa-gift mb-2 d-block" style="font-size: 2.5rem;"></i>
            Thank You!
        </h5>
        <button type="button" class="btn-close btn-close-white position-absolute" style="top: 15px; right: 15px;" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4 bg-white">
        <p class="fs-5 mb-3 fw-bold text-dark">We truly appreciate your order!</p>
        <p class="text-muted mb-4" style="line-height: 1.6;">
            Your order <strong style="color: var(--primary);"><?= htmlspecialchars($order['order_number'] ?: '#'.$order['id']) ?></strong> has been successfully placed.<br><br>
            You can easily track its status using the <strong>"Track Your Order"</strong> button on this page, or through your User Panel active orders list at any time.
        </p>
        <button type="button" class="btn-primary-full px-5 py-2 fw-bold w-auto" style="border-radius: 30px;" data-bs-dismiss="modal">Got It!</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var myModal = new bootstrap.Modal(document.getElementById('appreciationModal'), {
        keyboard: false
    });
    myModal.show();
});
</script>

<?php require_once 'includes/footer.php'; ?>
