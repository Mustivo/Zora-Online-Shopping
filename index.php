<?php
require_once 'core/config.php';
require_once 'includes/header.php';

$wishlist_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_res = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
    while ($w_row = mysqli_fetch_assoc($w_res)) {
        $wishlist_product_ids[] = $w_row['product_id'];
    }
}

// Render Product Card Function
function render_product_card($p) {
    global $wishlist_product_ids;
    $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
    $fav_active = $is_fav ? 'active' : '';
    ob_start();
    ?>
    <div class="col-6 col-md-3">
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
          <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none flex-grow-1"><div class="product-name"><?= htmlspecialchars($p['name']) ?></div></a>
          <div class="product-price mt-auto"><?= number_format($p['price'], 0) ?> RFW</div>
          <form action="core/actions.php" method="POST" class="mt-2">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn-add-cart w-100" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
          </form>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid-lines"></div>
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <div style="animation:fadeUp 0.8s ease">
          <h1 class="hero-title mb-4">Welcome to<br><span>Zora Online Shopping</span></h1>
          <p class="hero-sub mb-4">Explore curated products selected for excellence. Quality guaranteed, delivered straight to your door by ZORA.</p>
          <div class="d-flex gap-3 flex-wrap mb-5">
            <a href="shop.php" class="btn-hero text-decoration-none">Shop Now <i class="fas fa-arrow-right ms-2"></i></a>
            <a href="#categories" class="btn-hero-outline text-decoration-none">Browse Categories</a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-block">
        <div class="hero-visual">
          <div class="hero-circle">
            <div class="hero-product-cards">
              <div class="hero-mini-card"><div class="icon">🎧</div><div class="name">Headphones</div></div>
              <div class="hero-mini-card"><div class="icon">⌚</div><div class="name">Watches</div></div>
              <div class="hero-mini-card"><div class="icon">👕</div><div class="name">Fashion</div></div>
              <div class="hero-mini-card"><div class="icon">⚽</div><div class="name">Sports</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TRENDING SEARCH -->
<section class="py-5" style="background:var(--bg3)">
    <div class="container px-1 px-md-2 text-center">
        <span class="text-muted-custom font-weight-bold me-3 text-sm text-uppercase letter-spacing-1">Trending Searches:</span>
        <div class="d-inline-flex gap-2 flex-wrap justify-content-center mt-2 mt-md-0">
            <a href="shop.php?search=headphones" class="category-pill"><i class="fas fa-headphones"></i> Headphones</a>
            <a href="shop.php?search=watch" class="category-pill"><i class="fas fa-clock"></i> Watches</a>
            <a href="shop.php?search=smartphone" class="category-pill"><i class="fas fa-mobile-alt"></i> Smartphones</a>
            <a href="shop.php?search=shoes" class="category-pill"><i class="fas fa-shoe-prints"></i> Sneakers</a>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="py-5" id="categories" style="background:var(--bg2)">
  <div class="container px-1 px-md-2">
    <div class="text-center mb-4">
      <div class="section-eyebrow">What We Offer</div>
      <h2 class="section-title">Shop by Category</h2>
      <div class="divider mx-auto mt-3"></div>
    </div>
    <div class="row g-3 mt-2">
      <?php
      $cats = mysqli_query($conn, "SELECT * FROM categories LIMIT 8");
      while ($c = mysqli_fetch_assoc($cats)): ?>
        <div class="col-6 col-md-3">
          <a href="shop.php?category=<?= $c['id'] ?>" class="text-decoration-none">
            <div class="cat-card">
              <i class="<?= htmlspecialchars($c['icon']) ?> cat-icon"></i>
              <div class="cat-name"><?= htmlspecialchars($c['name']) ?></div>
            </div>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- NEW ARRIVALS -->
<section class="py-5">
  <div class="container px-1 px-md-2">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <div class="section-eyebrow">Just In</div>
        <h2 class="section-title">New Arrivals</h2>
      </div>
      <a href="shop.php?sort=newest" class="btn-hero-outline text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
      <?php
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 4");
      while ($p = mysqli_fetch_assoc($prods)): 
        echo render_product_card($p);
      endwhile; ?>
    </div>
  </div>
</section>

<!-- MOST POPULAR -->
<section class="py-5" style="background:var(--bg2)">
  <div class="container px-1 px-md-2">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <div class="section-eyebrow">Top Rated</div>
        <h2 class="section-title">Most Popular</h2>
      </div>
    </div>
    <div class="row g-3">
      <?php
      // In a real app this would order by rating or order count. 
      // Using is_featured as proxy for popular for now.
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 4");
      while ($p = mysqli_fetch_assoc($prods)): 
        echo render_product_card($p);
      endwhile; ?>
    </div>
  </div>
</section>

<!-- ALL PRODUCTS -->
<section class="py-5">
  <div class="container px-1 px-md-2">
    <div class="text-center mb-5">
      <div class="section-eyebrow">Explore</div>
      <h2 class="section-title">All Products</h2>
      <div class="divider mx-auto mt-3"></div>
    </div>
    <div class="row g-3">
      <?php
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC LIMIT 8");
      while ($p = mysqli_fetch_assoc($prods)): 
        echo render_product_card($p);
      endwhile; ?>
    </div>
    <div class="text-center mt-5">
        <a href="shop.php" class="btn-hero text-decoration-none px-5 py-3">Load More Products</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
