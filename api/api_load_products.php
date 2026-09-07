<?php
// require_once 'core/config.php';
require_once dirname(__DIR__) . '/core/config.php';
// require_once dirname(__DIR__) . '/includes/header.php';



header('Content-Type: application/json');

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($offset < 0) $offset = 0;
if ($limit <= 0 || $limit > 50) $limit = 10;

// Get wishlist items for current user (correct table name is 'wishlist')
$wishlist_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $wl_q = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
    if ($wl_q) {
        while ($wl_row = mysqli_fetch_assoc($wl_q)) {
            $wishlist_product_ids[] = (int)$wl_row['product_id'];
        }
    }
}

// Store order status
$store_order_status = get_setting('store_order_status', 'enable');
$store_order_message = get_setting('store_order_message', 'Orders Disabled');

// Total count
$total_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products");
$total_row = $total_q ? mysqli_fetch_assoc($total_q) : null;
$total_products = (int)($total_row['cnt'] ?? 0);

// Fetch products
$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC LIMIT $offset, $limit";
$res = mysqli_query($conn, $query);

$html = '';
$count = 0;

if (!function_exists('render_api_product_card')) {
    function render_api_product_card($p, $wishlist_ids, $store_status, $store_msg) {
        $is_fav = in_array((int)$p['id'], $wishlist_ids);
        $fav_active = $is_fav ? 'active' : '';
        
        $btn_disabled = ($p['stock'] <= 0 || $store_status === 'disable') ? 'disabled' : '';
        if ($store_status === 'disable') {
            $btn_text = htmlspecialchars($store_msg);
        } else {
            $btn_text = __('add_to_cart');
        }

        ob_start();
        ?>
        <div class="col-6 col-sm-4 col-md-3 col-xl-custom-5">
          <div class="product-card h-100 shadow-sm d-flex flex-column" style="border-radius: 14px; overflow: hidden; background: var(--card); border: 1px solid var(--border);">
            <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none">
                <div class="product-image" style="aspect-ratio: 1 / 1; width: 100%; height: auto; position: relative; overflow: hidden; background: var(--bg3);">
                  <?php if(!empty($p['image']) && file_exists(__DIR__ . '/uploads/' . $p['image'])): ?>
                      <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                  <?php else: ?>
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center"><i class="fas fa-box text-muted" style="font-size: 2.5rem;"></i></div>
                  <?php endif; ?>
                  <?php if($p['stock'] <= 0): ?>
                      <div class="product-badge sale bg-danger text-white"><?= __('out_of_stock') ?></div>
                  <?php endif; ?>
                  <?php if(!empty($p['is_new'])): ?>
                      <div class="product-badge bg-success text-white" style="left:auto;right:10px;"><?= __('new_badge') ?></div>
                  <?php endif; ?>
                  <div class="product-wishlist <?= $fav_active ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this); event.preventDefault(); event.stopPropagation();"><i class="fas fa-heart"></i></div>
                </div>
            </a>
            <div class="product-body d-flex flex-column justify-content-between flex-grow-1" style="padding: 0.75rem 0.85rem;">
              <div>
                <div class="product-category" style="font-size: 0.68rem; color: var(--accent); font-weight: 700; text-transform: uppercase; line-height: 1.1; margin-bottom: 2px;"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
                <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none"><div class="product-name notranslate text-truncate" style="font-size: 0.88rem; font-weight: 700; color: var(--text); margin-bottom: 4px;"><?= htmlspecialchars($p['name']) ?></div></a>
              </div>

              <div class="mt-auto pt-1">
                <div class="mb-2">
                  <?php if (!empty($p['discount_price']) && $p['discount_price'] > 0): ?>
                      <div class="product-price" style="line-height: 1.1;">
                          <span class="text-danger fw-bold" style="font-size: 0.92rem;"><?= number_format($p['discount_price'], 0) ?> RFW</span>
                          <del class="text-muted ms-1" style="font-size: 0.7rem;"><?= number_format($p['price'], 0) ?> RFW</del>
                      </div>
                  <?php else: ?>
                      <div class="product-price" style="font-size: 0.92rem; font-weight: 800; color: #3b82f6;"><?= number_format($p['price'], 0) ?> RFW</div>
                  <?php endif; ?>
                </div>

                <?php if ($btn_disabled): ?>
                    <button class="btn-add-cart w-100 d-flex align-items-center justify-content-center gap-1" style="background: #cbd5e1; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase;" disabled><i class="fas fa-shopping-cart"></i> <?= $btn_text ?></button>
                <?php else: ?>
                    <a href="product.php?id=<?= $p['id'] ?>" class="btn-add-cart w-100 text-decoration-none text-center d-flex align-items-center justify-content-center gap-2" style="background: #3b82f6; border: none; border-radius: 8px; color: white !important; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(59,130,246,0.25);"><i class="fas fa-shopping-cart" style="font-size:0.75rem;"></i> <?= $btn_text ?></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if ($res) {
    while ($p = mysqli_fetch_assoc($res)) {
        $count++;
        $html .= render_api_product_card($p, $wishlist_product_ids, $store_order_status, $store_order_message);
    }
}

$has_more = ($offset + $count) < $total_products;

echo json_encode([
    'status' => 'success',
    'html' => $html,
    'count' => $count,
    'next_offset' => $offset + $count,
    'has_more' => $has_more,
    'total' => $total_products
]);
