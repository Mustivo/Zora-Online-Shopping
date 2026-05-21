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

$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? clean_input($conn, $_GET['sort']) : '';

$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
if ($search !== '') {
    $query .= " AND p.name LIKE '%$search%'";
}
if ($cat_filter > 0) {
    $query .= " AND p.category_id = $cat_filter";
}
if ($sort === 'price-asc') {
    $query .= " ORDER BY p.price ASC";
} elseif ($sort === 'price-desc') {
    $query .= " ORDER BY p.price DESC";
} elseif ($sort === 'newest') {
    $query .= " ORDER BY p.created_at DESC";
} else {
    $query .= " ORDER BY p.id DESC";
}

$result = mysqli_query($conn, $query);
$count = mysqli_num_rows($result);
?>

<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="admin-card" style="position:sticky;top:80px">
        <div class="admin-card-header">
          <span class="admin-card-title">Filters</span>
          <a href="shop.php" class="admin-action-btn text-decoration-none">Clear All</a>
        </div>
        <div class="p-3">
          <form action="shop.php" method="GET">
              <div class="mb-4">
                <div class="form-label-custom mb-2">Search</div>
                <input type="text" name="search" class="form-input" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
              </div>
              <div class="mb-4">
                <div class="form-label-custom mb-2">Category</div>
                <select name="category" class="form-select-custom">
                    <option value="0">All Categories</option>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM categories");
                    while ($c = mysqli_fetch_assoc($cats)) {
                        $selected = $cat_filter == $c['id'] ? 'selected' : '';
                        echo "<option value='{$c['id']}' $selected>" . htmlspecialchars($c['name']) . "</option>";
                    }
                    ?>
                </select>
              </div>
              <button type="submit" class="btn-hero w-100" style="padding:0.6rem;font-size:0.8rem">Apply Filters</button>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
          <h2 class="section-title" style="font-size:1.8rem">All Products</h2>
          <span class="text-sm text-muted-custom"><?= $count ?> results found</span>
        </div>
        <form action="shop.php" method="GET">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="category" value="<?= $cat_filter ?>">
            <select name="sort" class="form-select-custom" style="width:auto" onchange="this.form.submit()">
              <option value="">Sort: Default</option>
              <option value="price-asc" <?= $sort == 'price-asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price-desc" <?= $sort == 'price-desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
            </select>
        </form>
      </div>
      
      <div class="row g-3">
        <?php if($count == 0): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open text-muted" style="font-size:3rem"></i>
                <p class="mt-3 text-muted-custom">No products found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($result)): ?>
              <div class="col-6 col-md-4">
                <div class="product-card">
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
                        <?php 
                        $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
                        $fav_active = $is_fav ? 'active' : '';
                        ?>
                        <div class="product-wishlist <?= $fav_active ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this); event.preventDefault(); event.stopPropagation();"><i class="fas fa-heart"></i></div>
                      </div>
                  </a>
                  <div class="product-body">
                    <div class="product-category"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
                    <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none"><div class="product-name"><?= htmlspecialchars($p['name']) ?></div></a>
                    <div class="product-price"><?= number_format($p['price'], 0) ?> RFW</div>
                    <form action="core/actions.php" method="POST" class="mt-2">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-add-cart w-100" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
