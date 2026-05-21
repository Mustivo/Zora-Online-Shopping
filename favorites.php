<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='container py-5 text-center'>
            <i class='fas fa-heart text-muted mb-3' style='font-size:4rem'></i>
            <h3>Please Log In</h3>
            <p class='text-muted-custom mb-4'>You need to be logged in to view your favorite products.</p>
            <button class='btn-hero text-decoration-none' onclick='openAuthModal()'>Log In Now</button>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

$uid = $_SESSION['user_id'];
$query = "SELECT p.*, c.name as cat_name FROM products p 
          INNER JOIN wishlist w ON p.id = w.product_id 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE w.user_id = $uid 
          ORDER BY w.created_at DESC";

$result = mysqli_query($conn, $query);

// Reusable product card function from index.php
function render_product_card($p) {
    global $wishlist_product_ids;
    $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
    $fav_active = $is_fav ? 'active' : '';
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card h-100">
            <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none">
            <div class="product-image">
              <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
                  <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                  <i class="fas fa-box text-muted"></i>
              <?php endif; ?>
              <?php if($p['stock'] <= 0): ?>
                  <div class="product-badge sale bg-danger text-white">Out of Stock</div>
              <?php endif; ?>
              <?php if(isset($p['is_new']) && $p['is_new']): ?>
                  <div class="product-badge bg-success text-white" style="left:auto;right:12px;">NEW</div>
              <?php endif; ?>
              <div class="product-wishlist <?= $fav_active ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this); event.preventDefault(); event.stopPropagation();"><i class="fas fa-heart"></i></div>
            </div>
        </a>
        <div class="product-body d-flex flex-column" style="height: calc(100% - 220px);">
          <div class="product-category"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
          <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none"><div class="product-name"><?= htmlspecialchars($p['name']) ?></div></a>
          <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
            <div class="product-price"><?= number_format($p['price'], 0) ?> RFW</div>
            <form action="core/actions.php" method="POST" class="m-0">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-dark rounded-circle" style="width:36px;height:36px;padding:0;" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>><i class="fas fa-plus"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php
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
