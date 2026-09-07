<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to view your panel."));
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
        <div>
            <div class="section-eyebrow">Welcome Back, <?= htmlspecialchars($_SESSION['first_name']) ?></div>
            <h2 class="section-title">My Account & Orders</h2>
        </div>
        <a href="core/actions.php?action=logout" class="btn-hero-outline text-decoration-none"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($order = mysqli_fetch_assoc($result)): ?>
                    <div class="card mb-4 shadow-sm border-0" style="border-radius: 16px; overflow: hidden; background: var(--bg2);">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-1 fw-bold" style="color: var(--text1); font-size: 1.1rem;">Order #<?= $order['id'] ?></h5>
                                <small class="text-muted-custom" style="font-size: 0.8rem;"><i class="far fa-calendar-alt me-1"></i> <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?= $order['status'] === 'Delivered' ? 'success' : ($order['status'] === 'Cancelled' ? 'danger' : 'warning') ?> fs-6 px-3 py-1 rounded-pill" style="font-size: 0.85rem !important; font-weight: 600;">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body px-4 pt-2 pb-3">
                            <?php
                            $order_id = $order['id'];
                            $items_query = "SELECT oi.*, p.name, p.image AS default_image, pi.image_path AS color_image 
                                            FROM order_items oi 
                                            JOIN products p ON oi.product_id = p.id 
                                            LEFT JOIN product_images pi ON oi.product_id = pi.product_id AND oi.color = pi.color_name 
                                            WHERE oi.order_id = $order_id";
                            $items_result = mysqli_query($conn, $items_query);
                            ?>
                            <div class="order-items-list mt-3">
                                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom" style="border-color: var(--border) !important;">
                                        <div style="width: 70px; height: 70px; flex-shrink: 0; margin-right: 15px; background: #fff; border-radius: 12px; padding: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                            <?php 
                                            $display_image = !empty($item['color_image']) ? $item['color_image'] : $item['default_image'];
                                            if(!empty($display_image) && file_exists('uploads/' . $display_image)): 
                                            ?>
                                                <img src="uploads/<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light rounded"><i class="fas fa-box text-muted"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold" style="color: var(--text1); font-size: 1rem;"><?= htmlspecialchars($item['name']) ?></h6>
                                            <div class="text-muted-custom" style="font-size: 0.85rem;">
                                                <span class="me-3">Qty: <strong><?= $item['quantity'] ?></strong></span>
                                                <?php if(!empty($item['color'])): ?>
                                                    <span class="me-3">Color: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                                <?php endif; ?>
                                                <span>Price: <?= number_format($item['price'], 0) ?> RFW</span>
                                            </div>
                                        </div>
                                        <div class="fw-bold ms-3 text-end" style="color: var(--text1); font-size: 1rem; white-space: nowrap;">
                                            <?= number_format($item['quantity'] * $item['price'], 0) ?> RFW
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-2 flex-wrap gap-3">
                                <div>
                                    <span class="text-muted-custom d-block mb-1" style="font-size: 0.9rem;">Order Total</span>
                                    <h4 class="fw-bold mb-0" style="color: var(--accent);"><?= number_format($order['total_amount'], 0) ?> RFW</h4>
                                </div>
                                <a href="order_track.php?id=<?= $order['id'] ?>" class="btn-hero text-decoration-none px-4 py-2" style="font-size: 0.95rem; border-radius: 30px;">
                                    <i class="fas fa-map-marker-alt me-2"></i>Track Package
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 admin-card">
                    <i class="fas fa-shopping-bag text-muted mb-3" style="font-size:4rem; opacity: 0.3;"></i>
                    <h4 class="fw-bold">No Orders Yet</h4>
                    <p class="text-muted-custom mb-4">You haven't placed any orders with us. Start exploring our collection!</p>
                    <a href="shop.php" class="btn-hero text-decoration-none">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
