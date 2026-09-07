<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to view your panel."));
    exit;
}

$user_id = $_SESSION['user_id'];
<<<<<<< HEAD
$user_info_q = mysqli_query($conn, "SELECT first_name, last_name, email, profile_picture FROM users WHERE id = $user_id");
$user_info = mysqli_fetch_assoc($user_info_q);

$query = "
    SELECT o.*, 
           p.name AS province_name, 
           d.name AS district_name, 
           s.name AS sector_name, 
           c.name AS cell_name, 
           v.name AS village_name
    FROM orders o
    LEFT JOIN rwanda_locations p ON o.shipping_province = p.id
    LEFT JOIN rwanda_locations d ON o.shipping_district = d.id
    LEFT JOIN rwanda_locations s ON o.shipping_sector = s.id
    LEFT JOIN rwanda_locations c ON o.shipping_cell = c.id
    LEFT JOIN rwanda_locations v ON o.shipping_village = v.id
    WHERE o.user_id = '$user_id' AND o.status != 'cart' 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
";
$result = mysqli_query($conn, $query);

$active_orders = [];
$history_orders = [];

if ($result) {
    while($order = mysqli_fetch_assoc($result)) {
        if (in_array($order['status'], ['Delivered', 'Cancelled'])) {
            $history_orders[] = $order;
        } else {
            $active_orders[] = $order;
        }
    }
}

// Function to render an order card
function renderOrderCard($order, $conn, $isActiveTab) {
    $order_id = $order['id'];
    $items_query = "SELECT oi.product_id, p.name, p.image AS default_image, oi.color, oi.size, SUM(oi.quantity) as quantity, oi.price, 
                    (SELECT image_path FROM product_images WHERE product_id = oi.product_id AND color_name = oi.color LIMIT 1) AS color_image 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = $order_id 
                    GROUP BY oi.product_id, oi.color, oi.size, oi.price, p.name, p.image";
    $items_result = mysqli_query($conn, $items_query);
    
    $statusClass = 'warning';
    if ($order['status'] === 'Delivered') $statusClass = 'success';
    if ($order['status'] === 'Cancelled') $statusClass = 'danger';
    if ($order['status'] === 'Shipped') $statusClass = 'info';

    $location_str = '';
    if (!empty($order['province_name'])) {
        $parts = array_filter([$order['village_name'], $order['cell_name'], $order['sector_name'], $order['district_name'], $order['province_name']]);
        $location_str = implode(', ', $parts);
    } else {
        $parts = array_filter([$order['shipping_address'], $order['shipping_city'], $order['shipping_state']], function($val) {
            return !empty($val) && $val !== 'N/A';
        });
        $location_str = implode(', ', $parts);
    }
    ?>
    <div class="card mb-4 shadow-sm border-0" style="border-radius: 16px; overflow: hidden; background: var(--bg2);">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold" style="color: var(--text1); font-size: 1.1rem;">Order <?= $order['order_number'] ?: '#' . $order['id'] ?></h5>
                <small class="text-muted-custom" style="font-size: 0.8rem;"><i class="far fa-calendar-alt me-1"></i> <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-<?= $statusClass ?> fs-6 px-3 py-1 rounded-pill" style="font-size: 0.85rem !important; font-weight: 600;">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
                <?php if ($order['status'] === 'Pending'): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editLocationModal<?= $order['id'] ?>"><i class="fas fa-edit me-1"></i><?= __('edit_location') ?></button>
                    <form action="core/actions.php" method="POST" class="m-0 p-0" onsubmit="return customConfirm(event, 'Are you sure you want to cancel this order?');">
                        <input type="hidden" name="action" value="cancel_order_user">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash me-1"></i><?= __('cancel_order') ?></button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($order['status'], ['Delivered', 'Cancelled']) && !$isActiveTab): ?>
                    <form action="core/actions.php" method="POST" class="m-0 p-0" onsubmit="return customConfirm(event, 'Are you sure you want to remove this order from your history?');">
                        <input type="hidden" name="action" value="delete_order_history">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash-alt me-1"></i><?= __('remove_order') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body px-4 pt-2 pb-3">
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
                            <h6 class="mb-1 fw-bold notranslate" style="color: var(--text1); font-size: 1rem;"><?= htmlspecialchars($item['name']) ?></h6>
                            <div class="text-muted-custom" style="font-size: 0.85rem;">
                                <span class="me-3">Qty: <strong><?= $item['quantity'] ?></strong></span>
                                <?php if(!empty($item['color'])): ?>
                                    <span class="me-3">Color: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                <?php endif; ?>
                                <?php if(!empty($item['size'])): ?>
                                    <span class="me-3">Size: <strong><?= htmlspecialchars($item['size']) ?></strong></span>
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
                    <span class="text-muted-custom d-block mb-1" style="font-size: 0.9rem;"><?= __('order_total') ?></span>
                    <h4 class="fw-bold mb-0" style="color: var(--accent);"><?= number_format($order['total_amount'], 0) ?> RFW</h4>
                </div>
                <div>
                    <span class="text-muted-custom d-block mb-1" style="font-size: 0.9rem;"><?= __('delivery_location_lbl') ?></span>
                    <span class="fw-bold" style="color: var(--text1); font-size: 0.95rem;">
                        <?= htmlspecialchars($location_str) ?>
                    </span>
                </div>
                <?php if (in_array($order['status'], ['Cancelled', 'Auto-Cancelled', 'Declined'])): ?>
                <div>
                    <span class="text-muted-custom d-block mb-1" style="font-size: 0.9rem;">Cancelled At</span>
                    <span class="fw-bold text-danger" style="font-size: 0.95rem;">
                        <i class="fas fa-times-circle me-1"></i> <?= date('M d, H:i', strtotime($order['updated_at'] ?? $order['created_at'])) ?>
                    </span>
                </div>
                <?php elseif ($order['delivery_deadline'] && $order['status'] !== 'Delivered'): 
                    $deadline_ts = strtotime($order['delivery_deadline']);
                    $diff_sec = $deadline_ts - time();
                    $days_left = floor($diff_sec / 86400);
                    $hours_left = floor(($diff_sec % 86400) / 3600);
                    
                    $custom_title = !empty($is_rw) ? get_setting('auto_cancel_title_rw') : get_setting('auto_cancel_title_en');
                    $cancel_title = !empty($custom_title) ? $custom_title : __('auto_cancel_deadline');

                    if ($diff_sec > 0) {
                        if (!empty($is_rw)) {
                            $remain_str = ($days_left > 0) ? ("Hasigaye {$days_left}d {$hours_left}h") : ("Hasigaye {$hours_left}h");
                        } else {
                            $remain_str = ($days_left > 0) ? ($days_left . 'd ' . $hours_left . 'h left') : ($hours_left . 'h left');
                        }
                    } else {
                        $remain_str = __('expired_deadline');
                    }
                ?>
                <div>
                    <span class="text-muted-custom d-block mb-1" style="font-size: 0.9rem;"><?= htmlspecialchars($cancel_title) ?></span>
                    <span class="fw-bold text-danger" style="font-size: 0.95rem;">
                        <i class="far fa-clock me-1"></i> <?= date('M d, H:i', $deadline_ts) ?>
                        <span class="badge bg-danger text-white rounded-pill px-2 py-1 ms-1" style="font-size: 0.72rem;"><?= $remain_str ?></span>
                    </span>
                </div>
                <?php endif; ?>
                <?php if ($order['status'] !== 'Cancelled'): ?>
                <a href="order_track.php?id=<?= $order['id'] ?>" class="btn-hero text-decoration-none px-4 py-2" style="font-size: 0.95rem; border-radius: 30px;">
                    <i class="fas fa-map-marker-alt me-2"></i>Track Package
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($order['status'] === 'Pending'): ?>
    <!-- Edit Location Modal -->
    <div class="modal fade" id="editLocationModal<?= $order['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content" style="background:var(--bg2);">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold">Update Location (Order <?= $order['order_number'] ?: '#' . $order['id'] ?>)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="core/actions.php" method="POST">
            <div class="modal-body">
              <input type="hidden" name="action" value="update_order_location">
              <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
              <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label-custom">Province</label>
                    <select class="form-select-custom w-100" name="shipping_province" id="sel_province_<?= $order['id'] ?>" onchange="loadUserLocations('district', this.value, <?= $order['id'] ?>)" required style="padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                        <option value="">Select Province</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">District</label>
                    <select class="form-select-custom w-100" name="shipping_district" id="sel_district_<?= $order['id'] ?>" onchange="loadUserLocations('sector', this.value, <?= $order['id'] ?>)" disabled required style="padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">Sector</label>
                    <select class="form-select-custom w-100" name="shipping_sector" id="sel_sector_<?= $order['id'] ?>" onchange="loadUserLocations('cell', this.value, <?= $order['id'] ?>)" disabled required style="padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                        <option value="">Select Sector</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">Cell</label>
                    <select class="form-select-custom w-100" name="shipping_cell" id="sel_cell_<?= $order['id'] ?>" onchange="loadUserLocations('village', this.value, <?= $order['id'] ?>)" disabled required style="padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                        <option value="">Select Cell</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label-custom">Village</label>
                    <select class="form-select-custom w-100" name="shipping_village" id="sel_village_<?= $order['id'] ?>" disabled required style="padding: 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                        <option value="">Select Village</option>
                    </select>
                </div>
              </div>
              <small class="text-muted mt-2 d-block">Note: Location updates are only allowed while the order is pending.</small>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn-primary-full mt-0 w-auto px-4 py-2" style="border-radius: 8px;">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php
}
?>

<div class="container py-5">
    <?php if(isset($_GET['msg'])): ?><div class="alert alert-success mb-4"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
    <?php if(isset($_GET['error'])): ?><div class="alert alert-danger mb-4"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

    <?php
    $discount_q = mysqli_query($conn, "SELECT id, name, price, discount_price, image, discount_expiry FROM products WHERE discount_price IS NOT NULL AND discount_price > 0 LIMIT 1");
    if ($discount_q && mysqli_num_rows($discount_q) > 0):
        $discount_prod = mysqli_fetch_assoc($discount_q);
    ?>
    <div class="alert alert-info mb-4 shadow-sm border-0 d-flex align-items-center flex-wrap gap-3" style="border-radius: 12px; background: linear-gradient(135deg, rgba(251,124,0,0.1) 0%, rgba(251,124,0,0.05) 100%); border-left: 5px solid var(--accent) !important;">
        <div class="text-accent" style="color: var(--accent);">
            <?php if (!empty($discount_prod['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($discount_prod['image']) ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <?php else: ?>
                <i class="fas fa-tags fa-2x"></i>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading fw-bold mb-1" style="color: var(--accent);">Special Offer on <?= htmlspecialchars($discount_prod['name']) ?>!</h5>
            <p class="mb-0 text-muted-custom" style="font-size: 0.95rem;">
                Get it now for just <strong style="color: var(--text);"><?= number_format($discount_prod['discount_price']) ?> RFW</strong> 
                (was <del><?= number_format($discount_prod['price']) ?> RFW</del>).
                <?php if (!empty($discount_prod['discount_expiry'])): ?>
                    <br><small class="text-danger fw-bold"><i class="far fa-clock"></i> Expires: <?= date('M d, H:i', strtotime($discount_prod['discount_expiry'])) ?></small>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <a href="product.php?id=<?= $discount_prod['id'] ?>" class="btn-hero text-decoration-none px-4 py-2" style="font-size: 0.9rem; border-radius: 30px;">Shop Now</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <div class="section-eyebrow"><?= __('welcome_back') ?>, <?= htmlspecialchars($_SESSION['first_name']) ?></div>
            <h2 class="section-title"><?= __('my_account_orders') ?></h2>
        </div>
        <a href="core/actions.php?action=logout" class="btn-hero-outline text-decoration-none"><i class="fas fa-sign-out-alt me-2"></i><?= __('logout') ?></a>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Tabs -->
            <ul class="nav nav-pills mb-4" id="orderTabs" role="tablist" style="background: var(--bg2); padding: 5px; border-radius: 12px; display: inline-flex;">
              <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-4" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-orders" type="button" role="tab" style="border-radius: 8px; color: var(--text);"><i class="fas fa-box-open me-2"></i><?= __('active_orders') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-orders" type="button" role="tab" style="border-radius: 8px; color: var(--text);"><i class="fas fa-history me-2"></i><?= __('order_history') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" style="border-radius: 8px; color: var(--text);"><i class="fas fa-bell me-2"></i><?= __('notifications') ?></button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-settings" type="button" role="tab" style="border-radius: 8px; color: var(--text);"><i class="fas fa-user-cog me-2"></i><?= __('profile_settings') ?></button>
              </li>
            </ul>

            <div class="tab-content" id="orderTabsContent">
              <!-- Active Orders Tab -->
              <div class="tab-pane fade show active" id="active-orders" role="tabpanel">
                  <?php if(count($active_orders) > 0): ?>
                      <?php foreach($active_orders as $order) renderOrderCard($order, $conn, true); ?>
                  <?php else: ?>
                      <div class="text-center py-5 admin-card">
                          <i class="fas fa-box-open text-muted mb-3" style="font-size:4rem; opacity: 0.3;"></i>
                          <h5 class="fw-bold"><?= __('no_active_orders') ?></h5>
                          <p class="text-muted-custom mb-4"><?= __('no_active_orders_desc') ?></p>
                          <a href="shop.php" class="btn-hero text-decoration-none"><?= __('start_shopping') ?></a>
                      </div>
                  <?php endif; ?>
              </div>

              <!-- History Orders Tab -->
              <div class="tab-pane fade" id="history-orders" role="tabpanel">
                  <?php if(count($history_orders) > 0): ?>
                      <?php foreach($history_orders as $order) renderOrderCard($order, $conn, false); ?>
                  <?php else: ?>
                      <div class="text-center py-5 admin-card">
                          <i class="fas fa-history text-muted mb-3" style="font-size:4rem; opacity: 0.3;"></i>
                          <h5 class="fw-bold"><?= __('no_order_history') ?></h5>
                          <p class="text-muted-custom mb-0"><?= __('no_order_history_desc') ?></p>
                      </div>
                  <?php endif; ?>
              </div>

              <!-- Notifications Tab -->
              <div class="tab-pane fade" id="notifications" role="tabpanel">
                  <?php 
                  $uid = (int)$_SESSION['user_id'];
                  $notif_q = mysqli_query($conn, "SELECT * FROM user_notifications WHERE user_id = $uid OR (user_id IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) ORDER BY created_at DESC LIMIT 20");
                  if($notif_q && mysqli_num_rows($notif_q) > 0): 
                  ?>
                      <div class="admin-card p-0">
                      <?php while($n = mysqli_fetch_assoc($notif_q)): ?>
                          <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background: <?= $n['is_read'] ? 'transparent' : 'rgba(251,124,0,0.05)' ?>;">
                              <div class="d-flex align-items-center">
                                  <div class="me-3 text-accent"><i class="fas fa-bell"></i></div>
                                  <div>
                                      <p class="mb-0 fw-bold" style="color: var(--text);"><?= html_entity_decode($n['message']) ?></p>
                                      <small class="text-muted-custom"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
                                  </div>
                              </div>
                              <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-hero-outline" style="border-radius: 20px;">View</a>
                          </div>
                      <?php endwhile; ?>
                      </div>
                  <?php else: ?>
                      <div class="text-center py-5 admin-card">
                          <i class="fas fa-bell-slash text-muted mb-3" style="font-size:4rem; opacity: 0.3;"></i>
                          <h5 class="fw-bold"><?= __('notifications') ?></h5>
                          <p class="text-muted-custom mb-0"><?= __('no_notifications_desc') ?></p>
                      </div>
                  <?php endif; ?>
              </div>

              <!-- Profile Tab -->
              <div class="tab-pane fade" id="profile-settings" role="tabpanel">
                  <div class="admin-card p-4">
                      <form action="core/actions.php" method="POST" enctype="multipart/form-data">
                          <input type="hidden" name="action" value="update_profile">
                          <div class="d-flex align-items-center mb-4 gap-4 flex-wrap">
                              <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background: var(--bg3); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                  <?php if(!empty($user_info['profile_picture'])): ?>
                                      <img src="uploads/<?= htmlspecialchars($user_info['profile_picture']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                  <?php else: ?>
                                      <i class="fas fa-user fa-3x text-muted"></i>
                                  <?php endif; ?>
                              </div>
                              <div>
                                  <label class="form-label-custom mb-1"><?= __('profile_picture_lbl') ?></label>
                                  <input type="file" name="profile_picture" class="form-control" accept="image/*" style="background: var(--bg1); color: var(--text); border-color: var(--border);">
                              </div>
                          </div>
                          
                          <div class="row g-3">
                              <div class="col-md-6">
                                  <label class="form-label-custom"><?= __('first_name') ?></label>
                                  <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user_info['first_name']) ?>" style="background: var(--bg1); color: var(--text); border-color: var(--border);" required>
                              </div>
                              <div class="col-md-6">
                                  <label class="form-label-custom"><?= __('last_name') ?></label>
                                  <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user_info['last_name']) ?>" style="background: var(--bg1); color: var(--text); border-color: var(--border);" required>
                              </div>
                              <div class="col-md-12">
                                  <label class="form-label-custom"><?= __('email_readonly') ?></label>
                                  <input type="email" class="form-control" value="<?= htmlspecialchars($user_info['email']) ?>" style="background: var(--bg1); color: var(--text); border-color: var(--border);" readonly disabled>
                              </div>
                              <div class="col-md-12">
                                  <label class="form-label-custom"><?= __('new_password_lbl') ?></label>
                                  <div class="password-toggle-wrap">
                                      <input type="password" name="new_password" class="form-control" style="background: var(--bg1); color: var(--text); border-color: var(--border);">
                                      <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                                          <i class="fas fa-eye"></i>
                                      </button>
                                  </div>
                              </div>
                              <div class="col-12 mt-4">
                                  <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">
                                      <?= !empty($user_info['profile_picture']) ? __('update_info') : __('save_changes') ?>
                                  </button>
                              </div>
                          </div>
                      </form>
                  </div>
              </div>

            </div>

            <!-- Recommended Products Grid Section (5 Cards per row across screen width) -->
            <?php
            $rec_prods_q = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5");
            if ($rec_prods_q && mysqli_num_rows($rec_prods_q) > 0):
            ?>
            <div class="mt-5 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="section-eyebrow"><?= __('discover_more') ?></div>
                        <h3 class="section-title mb-0" style="font-size: 1.5rem;"><?= __('recommended_for_you') ?></h3>
                    </div>
                    <a href="shop.php" class="btn-hero-outline text-decoration-none py-2 px-3" style="font-size: 0.85rem;"><?= __('view_all') ?></a>
                </div>
                <div class="row g-3 g-md-4">
                    <?php while($prod = mysqli_fetch_assoc($rec_prods_q)): 
                        $p_id = (int)$prod['id'];
                        $prod_imgs = [];
                        if (!empty($prod['image'])) $prod_imgs[] = $prod['image'];
                        $p_imgs_q = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $p_id ORDER BY created_at ASC");
                        if ($p_imgs_q) {
                            while ($pi = mysqli_fetch_assoc($p_imgs_q)) {
                                if (!empty($pi['image_path']) && !in_array($pi['image_path'], $prod_imgs)) {
                                    $prod_imgs[] = $pi['image_path'];
                                }
                            }
                        }
                        $has_gallery = count($prod_imgs) > 1;
                    ?>
                    <div class="col-6 col-sm-4 col-md-3 col-xl-custom-5">
                      <div class="product-card h-100 shadow-sm d-flex flex-column" style="border-radius: 14px; overflow: hidden; background: var(--card); border: 1px solid var(--border); aspect-ratio: 1 / 1; position: relative;">
                        <a href="product.php?id=<?= $prod['id'] ?>" class="text-decoration-none d-block position-relative" style="height: 62%; overflow: hidden; background: var(--bg3);">
                            <div class="product-image position-relative" style="width: 100%; height: 100%;">
                              <?php if(!empty($prod_imgs) && file_exists('uploads/' . $prod_imgs[0])): ?>
                                  <img id="card_img_<?= $p_id ?>" src="uploads/<?= htmlspecialchars($prod_imgs[0]) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover; transition: opacity 0.2s ease;">
                              <?php else: ?>
                                  <div class="w-100 h-100 d-flex align-items-center justify-content-center"><i class="fas fa-box text-muted" style="font-size: 2.5rem;"></i></div>
                              <?php endif; ?>

                              <?php if ($has_gallery): ?>
                                  <button type="button" class="card-gallery-btn prev" onclick="event.preventDefault(); event.stopPropagation(); shiftCardImage(<?= $p_id ?>, -1);" title="Previous Image"><i class="fas fa-chevron-left"></i></button>
                                  <button type="button" class="card-gallery-btn next" onclick="event.preventDefault(); event.stopPropagation(); shiftCardImage(<?= $p_id ?>, 1);" title="Next Image"><i class="fas fa-chevron-right"></i></button>
                                  <div class="card-gallery-indicator position-absolute bottom-0 start-50 translate-middle-x mb-1" id="card_indicator_<?= $p_id ?>" style="font-size: 0.65rem; background: rgba(0,0,0,0.6); color: #fff; padding: 1px 6px; border-radius: 10px; pointer-events: none; z-index: 5;">
                                      1/<?= count($prod_imgs) ?>
                                  </div>
                                  <script>
                                  (function() {
                                      window.cardImages = window.cardImages || {};
                                      window.cardImageIndex = window.cardImageIndex || {};
                                      window.cardImages[<?= $p_id ?>] = <?= json_encode($prod_imgs) ?>;
                                      window.cardImageIndex[<?= $p_id ?>] = 0;
                                  })();
                                  </script>
                              <?php endif; ?>

                              <?php if($prod['stock'] <= 0): ?>
                                  <div class="product-badge sale bg-danger text-white"><?= __('out_of_stock') ?></div>
                              <?php endif; ?>
                              <?php if(isset($prod['is_new']) && $prod['is_new']): ?>
                                  <div class="product-badge bg-success text-white" style="left:auto;right:10px;">NEW</div>
                              <?php endif; ?>
                            </div>
                        </a>
                        <div class="product-body d-flex flex-column justify-content-between flex-grow-1" style="padding: 0.5rem 0.75rem; height: 38%;">
                          <div>
                            <div class="product-category" style="font-size: 0.65rem; color: var(--accent); font-weight: 700; text-transform: uppercase; line-height: 1.1;"><?= htmlspecialchars($prod['cat_name'] ?? 'Uncategorized') ?></div>
                            <a href="product.php?id=<?= $prod['id'] ?>" class="text-decoration-none"><div class="product-name notranslate text-truncate" style="font-size: 0.82rem; font-weight: 700; color: var(--text); margin: 2px 0;"><?= htmlspecialchars($prod['name']) ?></div></a>
                          </div>

                          <div class="d-flex justify-content-between align-items-center pt-1 mt-auto">
                            <?php if (!empty($prod['discount_price']) && $prod['discount_price'] > 0): ?>
                                <div class="product-price" style="line-height: 1.1;">
                                    <span class="text-danger fw-bold" style="font-size: 0.85rem;"><?= number_format($prod['discount_price'], 0) ?> RFW</span>
                                    <del class="text-muted d-block" style="font-size: 0.68rem;"><?= number_format($prod['price'], 0) ?> RFW</del>
                                </div>
                            <?php else: ?>
                                <div class="product-price" style="font-size: 0.85rem; font-weight: 700; color: #3b82f6;"><?= number_format($prod['price'], 0) ?> RFW</div>
                            <?php endif; ?>
                            <?php
                            global $store_order_status;
                            $btn_disabled = ($prod['stock'] <= 0 || $store_order_status === 'disable') ? 'disabled' : '';
                            ?>
                            <?php if ($btn_disabled): ?>
                                <button class="btn-add-cart rounded-circle d-flex align-items-center justify-content-center m-0" style="width:32px; height:32px; padding:0; background: #cbd5e1; border:none;" disabled><i class="fas fa-plus text-white" style="font-size:0.75rem;"></i></button>
                            <?php else: ?>
                                <a href="product.php?id=<?= $prod['id'] ?>" class="btn-add-cart rounded-circle d-flex align-items-center justify-content-center m-0 text-decoration-none" style="width:32px; height:32px; padding:0; background: #3b82f6; color: white; border:none; box-shadow: 0 2px 6px rgba(59,130,246,0.3);" title="<?= __('add_to_cart') ?>"><i class="fas fa-plus" style="font-size:0.75rem;"></i></a>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        </div>
    </div>
</div>

<<<<<<< HEAD
<style>
#orderTabs .nav-link.active {
    background-color: var(--accent);
    color: white !important;
}

.card-gallery-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    color: #0f172a;
    border: 1px solid rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    z-index: 6;
    cursor: pointer;
    opacity: 0.85;
    transition: all 0.2s ease;
}
.card-gallery-btn:hover {
    opacity: 1;
    background: var(--accent);
    color: #fff;
    transform: translateY(-50%) scale(1.15);
}
.card-gallery-btn.prev {
    left: 6px;
}
.card-gallery-btn.next {
    right: 6px;
}
[data-theme="dark"] .card-gallery-btn {
    background: rgba(30, 41, 59, 0.9);
    color: #f8fafc;
    border-color: rgba(255,255,255,0.15);
}
[data-theme="dark"] .card-gallery-btn:hover {
    background: var(--accent);
    color: #fff;
}
</style>

<script>
function shiftCardImage(productId, direction) {
    if (!window.cardImages || !window.cardImages[productId]) return;
    const imgs = window.cardImages[productId];
    if (imgs.length <= 1) return;
    
    let curIdx = window.cardImageIndex[productId] || 0;
    curIdx += direction;
    if (curIdx < 0) curIdx = imgs.length - 1;
    if (curIdx >= imgs.length) curIdx = 0;
    
    window.cardImageIndex[productId] = curIdx;
    
    const imgEl = document.getElementById('card_img_' + productId);
    if (imgEl) {
        imgEl.style.opacity = '0.4';
        setTimeout(() => {
            imgEl.src = 'uploads/' + imgs[curIdx];
            imgEl.style.opacity = '1';
        }, 120);
    }
    
    const indEl = document.getElementById('card_indicator_' + productId);
    if (indEl) {
        indEl.innerText = (curIdx + 1) + '/' + imgs.length;
    }
}
async function loadUserLocations(type, parent_id, order_id) {
    const types = ['province', 'district', 'sector', 'cell', 'village'];
    const idx = types.indexOf(type);
    
    for (let i = idx; i < types.length; i++) {
        const el = document.getElementById('sel_' + types[i] + '_' + order_id);
        if (el) {
            el.innerHTML = '<option value="">Select ' + types[i].charAt(0).toUpperCase() + types[i].slice(1) + '</option>';
            if (i > idx) el.disabled = true;
        }
    }
    
    if (type !== 'province' && !parent_id) return;
    
    const targetEl = document.getElementById('sel_' + type + '_' + order_id);
    if (!targetEl) return;
    
    targetEl.disabled = true;
    targetEl.innerHTML = '<option value="">Loading...</option>';
    
    try {
        const res = await fetch(`api_locations.php?type=${type}&parent_id=${parent_id || ''}`);
        const json = await res.json();
        
        targetEl.innerHTML = '<option value="">Select ' + type.charAt(0).toUpperCase() + type.slice(1) + '</option>';
        if (json.status === 'success') {
            json.data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                targetEl.appendChild(opt);
            });
            targetEl.disabled = false;
        } else {
            throw new Error(json.message || 'Error from API');
        }
    } catch (e) {
        console.error('Failed to load locations', e);
        targetEl.innerHTML = '<option value="">Error loading</option>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal[id^="editLocationModal"]');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function(e) {
            const orderId = this.id.replace('editLocationModal', '');
            const provSelect = document.getElementById('sel_province_' + orderId);
            if (provSelect && provSelect.options.length <= 1) {
                loadUserLocations('province', '', orderId);
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        const tabEl = document.querySelector(`button[data-bs-target="#${tab}"]`);
        if (tabEl) {
            const bootstrapTab = new bootstrap.Tab(tabEl);
            bootstrapTab.show();
        }
    }
});
</script>

=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
