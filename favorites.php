<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='container py-5 text-center'>
            <i class='fas fa-heart text-muted mb-3' style='font-size:4rem'></i>
            <h3>Please Log In</h3>
            <p class='text-muted-custom mb-4'>You need to be logged in to view your favorite products.</p>
            <button class='btn-hero text-decoration-none border-0' onclick='openAuthModal()'>Log In Now</button>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

$uid = $_SESSION['user_id'];
$wishlist_product_ids = [];
$w_q = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
if ($w_q) {
    while($w_r = mysqli_fetch_assoc($w_q)) {
        $wishlist_product_ids[] = (int)$w_r['product_id'];
    }
}

$query = "SELECT p.*, c.name as cat_name FROM products p 
          INNER JOIN wishlist w ON p.id = w.product_id 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE w.user_id = $uid 
          ORDER BY w.created_at DESC";

$result = mysqli_query($conn, $query);

if (!function_exists('render_product_card')) {
function render_product_card($p) {
    global $wishlist_product_ids;
    $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
    $fav_active = $is_fav ? 'active' : '';
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card h-100 shadow-sm d-flex flex-column" style="border-radius: 14px; overflow: hidden; background: var(--card); border: 1px solid var(--border);">
            <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none">
            <div class="product-image" style="aspect-ratio: 1 / 1; width: 100%; height: auto; position: relative; overflow: hidden; background: var(--bg3);">
              <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
                  <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center"><i class="fas fa-box text-muted" style="font-size: 2.5rem;"></i></div>
              <?php endif; ?>
              <?php if($p['stock'] <= 0): ?>
                  <div class="product-badge sale bg-danger text-white"><?= __('out_of_stock') ?></div>
              <?php endif; ?>
              <?php if(isset($p['is_new']) && $p['is_new']): ?>
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

            <?php if ($p['stock'] <= 0): ?>
                <button class="btn-add-cart w-100 d-flex align-items-center justify-content-center gap-1" style="background: #cbd5e1; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase;" disabled><i class="fas fa-shopping-cart"></i> <?= __('out_of_stock') ?></button>
            <?php else: ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="btn-add-cart w-100 text-decoration-none text-center d-flex align-items-center justify-content-center gap-2" style="background: #3b82f6; border: none; border-radius: 8px; color: white !important; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(59,130,246,0.25);"><i class="fas fa-shopping-cart" style="font-size:0.75rem;"></i> <?= __('add_to_cart') ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php
}
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <div class="section-eyebrow">Your Wishlist</div>
            <h2 class="section-title">My Favorites</h2>
        </div>
        <a href="shop.php" class="btn-hero-outline text-decoration-none">Continue Shopping</a>
    </div>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="row g-3 g-md-4">
            <?php while($product = mysqli_fetch_assoc($result)) { render_product_card($product); } ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-heart text-muted mb-3" style="font-size:4rem; opacity: 0.3;"></i>
            <h4>Your wishlist is empty</h4>
            <p class="text-muted-custom mb-4">You haven't favorited any products yet. Go find something you like!</p>
            <a href="shop.php" class="btn-hero text-decoration-none">Explore Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
