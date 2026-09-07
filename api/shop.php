<?php
require_once 'core/config.php';
<<<<<<< HEAD
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page_title = "Shop " . ($search ? "'" . htmlspecialchars($search) . "' - " : "- ") . "Zora Shop Rwanda";
$meta_desc = "Browse our extensive collection of fashion, clothes, and accessories. " . ($search ? "Search results for " . htmlspecialchars($search) : "Find exactly what you need at Zora Shop.");
$meta_keywords = "Shop online Rwanda, Buy fashion online Kigali, Zora Shop products";
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
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
<<<<<<< HEAD
$cat_filter = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : [$_GET['category']]) : [];
$cat_filter = array_map('intval', $cat_filter);
=======
$cat_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
$sort = isset($_GET['sort']) ? clean_input($conn, $_GET['sort']) : '';

$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
if ($search !== '') {
<<<<<<< HEAD
    $query .= " AND (p.name LIKE '%$search%' OR p.tags LIKE '%$search%')";
}
if (!empty($cat_filter) && $cat_filter[0] !== 0) {
    $cat_ids = implode(',', $cat_filter);
    $query .= " AND p.category_id IN ($cat_ids)";
=======
    $query .= " AND p.name LIKE '%$search%'";
}
if ($cat_filter > 0) {
    $query .= " AND p.category_id = $cat_filter";
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
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
<<<<<<< HEAD
      <div class="admin-card sticky-sidebar">
        <div class="admin-card-header d-flex justify-content-between align-items-center" style="border-bottom: none; padding-bottom: 0;">
          <span class="admin-card-title fw-bold" style="font-size: 1.2rem;"><?= __('filter') ?></span>
          <a href="shop.php" class="text-decoration-none" style="color: var(--accent); font-size: 0.9rem;"><?= __('clear_all') ?></a>
        </div>
        <div class="p-3 flex-grow-1 overflow-auto" style="overflow-y: auto;">
          <form action="shop.php" method="GET" id="filterForm">
              <?php if($search !== ''): ?>
                  <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
              <?php endif; ?>
              <?php if($sort !== ''): ?>
                  <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
              <?php endif; ?>
              
              <div class="mb-3 d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#categoriesCollapse">
                <span class="fw-bold" style="font-size: 1.05rem;"><?= __('categories') ?></span>
                <i class="fas fa-chevron-up text-muted" style="font-size: 0.8rem;"></i>
              </div>
              
              <div class="collapse show" id="categoriesCollapse">
                  <?php
                  $cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY parent_id ASC, name ASC");
                  $all_cats = [];
                  while ($row = mysqli_fetch_assoc($cats_query)) {
                      $all_cats[] = $row;
                  }
                  
                  $parents = array_filter($all_cats, function($c) { return empty($c['parent_id']); });
                  
                  foreach ($parents as $p):
                      $children = array_filter($all_cats, function($c) use ($p) { return $c['parent_id'] == $p['id']; });
                      $has_sub = count($children) > 0;
                      $checked = in_array($p['id'], $cat_filter) ? 'checked' : '';
                      
                      $child_selected = false;
                      foreach ($children as $c) {
                          if (in_array($c['id'], $cat_filter)) {
                              $child_selected = true;
                              break;
                          }
                      }
                      $show_sub = $child_selected ? 'show' : '';
                  ?>
                  <div class="form-check mb-2 d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center w-100">
                          <input class="form-check-input me-2" type="checkbox" name="category[]" value="<?= $p['id'] ?>" id="cat_<?= $p['id'] ?>" <?= $checked ?> onchange="document.getElementById('filterForm').submit()" style="cursor:pointer; width:1.1rem; height:1.1rem; border-color:var(--text3);">
                          <label class="form-check-label flex-grow-1" for="cat_<?= $p['id'] ?>" style="font-size: 0.95rem; cursor:pointer;">
                              <?= htmlspecialchars($p['name']) ?>
                          </label>
                          <?php if($has_sub): ?>
                              <i class="fas fa-chevron-<?= $show_sub ? 'up' : 'down' ?> text-muted ms-2 p-1" style="font-size: 0.8rem; cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#subCat_<?= $p['id'] ?>" onclick="this.classList.toggle('fa-chevron-down'); this.classList.toggle('fa-chevron-up'); event.preventDefault();"></i>
                          <?php endif; ?>
                      </div>
                  </div>
                  <?php if($has_sub): ?>
                      <div class="collapse <?= $show_sub ?> ms-4 mb-2" id="subCat_<?= $p['id'] ?>">
                          <?php foreach($children as $c): 
                              $c_checked = in_array($c['id'], $cat_filter) ? 'checked' : '';
                          ?>
                              <div class="form-check mb-1">
                                  <input class="form-check-input" type="checkbox" name="category[]" value="<?= $c['id'] ?>" id="cat_<?= $c['id'] ?>" <?= $c_checked ?> onchange="document.getElementById('filterForm').submit()" style="cursor:pointer; border-color:var(--text3);">
                                  <label class="form-check-label" for="cat_<?= $c['id'] ?>" style="font-size: 0.85rem; cursor:pointer; color: var(--text2);">
                                      <?= htmlspecialchars($c['name']) ?>
                                  </label>
                              </div>
                          <?php endforeach; ?>
                      </div>
                  <?php endif; ?>
                  <?php endforeach; ?>
              </div>
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-lg-9">
<<<<<<< HEAD
      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div style="flex: 1; min-width: 250px;">
          <h2 class="section-title mb-1" style="font-size:1.8rem"><?= __('all_products') ?></h2>
          <span class="text-sm text-muted-custom d-block mb-3"><?= $count ?> <?= __('results_found') ?></span>
          
          <form action="shop.php" method="GET" class="d-flex align-items-center" style="max-width: 400px;">
              <?php foreach($cat_filter as $c_id): ?>
                  <input type="hidden" name="category[]" value="<?= $c_id ?>">
              <?php endforeach; ?>
              <?php if($sort !== ''): ?>
                  <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
              <?php endif; ?>
              <div class="input-group">
                  <input type="text" name="search" class="form-control" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars($search) ?>" style="border-radius: 6px 0 0 6px; border: 1px solid var(--border); background-color: var(--bg3);">
                  <button class="btn btn-accent" type="submit" style="border-radius: 0 6px 6px 0;"><i class="fas fa-search"></i></button>
              </div>
          </form>
        </div>
        
        <form action="shop.php" method="GET" id="sortForm">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php foreach($cat_filter as $c_id): ?>
                <input type="hidden" name="category[]" value="<?= $c_id ?>">
            <?php endforeach; ?>
            <select name="sort" class="form-select-custom" style="width:auto" onchange="document.getElementById('sortForm').submit()">
              <option value=""><?= __('sort_default') ?></option>
              <option value="price-asc" <?= $sort == 'price-asc' ? 'selected' : '' ?>><?= __('price_low_high') ?></option>
              <option value="price-desc" <?= $sort == 'price-desc' ? 'selected' : '' ?>><?= __('price_high_low') ?></option>
              <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>><?= __('newest_arrivals') ?></option>
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            </select>
        </form>
      </div>
      
      <div class="row g-3">
        <?php if($count == 0): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open text-muted" style="font-size:3rem"></i>
<<<<<<< HEAD
                <p class="mt-3 text-muted-custom"><?= __('no_products_found') ?></p>
            </div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($result)): ?>
              <div class="col-6 col-sm-6 col-md-4 col-xl-3">
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
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                        <?php endif; ?>
                        <?php 
                        $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
                        $fav_active = $is_fav ? 'active' : '';
                        ?>
                        <div class="product-wishlist <?= $fav_active ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this); event.preventDefault(); event.stopPropagation();"><i class="fas fa-heart"></i></div>
                      </div>
                  </a>
<<<<<<< HEAD
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

                      <?php
                      global $store_order_status, $store_order_message;
                      $btn_disabled = ($p['stock'] <= 0 || $store_order_status === 'disable') ? 'disabled' : '';
                      if ($store_order_status === 'disable') {
                          $btn_text = htmlspecialchars($store_order_message);
                      } else {
                          $btn_text = __('add_to_cart');
                      }
                      ?>
                      <?php if ($btn_disabled): ?>
                          <button class="btn-add-cart w-100 d-flex align-items-center justify-content-center gap-1" style="background: #cbd5e1; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase;" disabled><i class="fas fa-shopping-cart"></i> <?= $btn_text ?></button>
                      <?php else: ?>
                          <a href="product.php?id=<?= $p['id'] ?>" class="btn-add-cart w-100 text-decoration-none text-center d-flex align-items-center justify-content-center gap-2" style="background: #3b82f6; border: none; border-radius: 8px; color: white !important; font-weight: 700; font-size: 0.78rem; padding: 8px 10px; text-transform: uppercase; transition: all 0.2s ease; box-shadow: 0 3px 10px rgba(59,130,246,0.25);"><i class="fas fa-shopping-cart" style="font-size:0.75rem;"></i> <?= $btn_text ?></a>
                      <?php endif; ?>
                    </div>
=======
                  <div class="product-body">
                    <div class="product-category"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
                    <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none"><div class="product-name"><?= htmlspecialchars($p['name']) ?></div></a>
                    <div class="product-price"><?= number_format($p['price'], 0) ?> RFW</div>
                    <form action="core/actions.php" method="POST" class="mt-2">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-add-cart w-100" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                    </form>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
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
