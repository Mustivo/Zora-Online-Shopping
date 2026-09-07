<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to view your orders."));
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo "<div class='container py-5'><h3 class='text-danger'>Order not found or access denied.</h3></div>";
    require_once 'includes/footer.php';
    exit;
}

$order = mysqli_fetch_assoc($result);

// Fetch order items
$items_query = "SELECT oi.*, p.name, p.image AS default_image, pi.image_path AS color_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                LEFT JOIN product_images pi ON oi.product_id = pi.product_id AND oi.color = pi.color_name 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Order Tracking: #<?= $order['id'] ?></h2>
        <a href="user_panel.php" class="btn-hero-outline text-decoration-none">Back to My Account</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card h-100">
                <div class="admin-card-header"><span class="admin-card-title">Order Items</span></div>
                <div class="cart-items p-3">
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                    <div class="cart-item">
                        <div class="cart-item-img">
                            <?php 
                            $display_image = !empty($item['color_image']) ? $item['color_image'] : $item['default_image'];
                            if(!empty($display_image) && file_exists('uploads/' . $display_image)): 
                            ?>
                                <img src="uploads/<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center" style="border-radius:8px;"><i class="fas fa-box text-muted"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-item-price"><?= number_format($item['price'], 0) ?> RFW</div>
                            <div class="text-muted-custom mt-1" style="font-size: 0.8rem;">
                                Qty: <?= $item['quantity'] ?>
                                <?php if(!empty($item['color'])): ?>
                                    | Color: <?= htmlspecialchars($item['color']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="order-summary-card h-100">
                <div class="admin-card-title mb-3">Order Status</div>
                
                <div class="mb-4">
                    <h1 class="text-<?= $order['status'] === 'Delivered' ? 'success' : ($order['status'] === 'Cancelled' ? 'danger' : 'warning') ?> fw-bold">
                        <?= htmlspecialchars($order['status']) ?>
                    </h1>
                    <p class="text-muted-custom small">Date Placed: <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                </div>

                <div class="admin-card-title mb-3">Shipping Info</div>
                <div class="text-muted-custom small mb-4">
                    <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                    <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> <?= htmlspecialchars($order['shipping_zip']) ?><br>
                    <?= htmlspecialchars($order['shipping_country']) ?>
                </div>

                <div class="admin-card-title mb-3">Summary</div>
                <div class="order-row"><span class="text-muted-custom">Payment</span><span><?= htmlspecialchars($order['payment_method']) ?></span></div>
                <div class="order-row"><span class="text-muted-custom">Shipping</span><span class="gold"><?= htmlspecialchars($order['shipping_method']) ?></span></div>
                <div class="order-row total"><span>Total</span><span><?= number_format($order['total_amount'], 0) ?> RFW</span></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
