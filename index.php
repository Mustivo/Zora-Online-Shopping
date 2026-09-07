<?php
require_once 'core/config.php';
$page_title = "Zora Online Shopping | Latest Fashion in Rwanda";
$meta_desc = "Discover the latest fashion trends at Zora Online Shopping. Shop high-quality clothes, shoes, and accessories with fast delivery in Kigali, Rwanda.";
$meta_keywords = "Online shopping Rwanda, Zora shop Rwanda, buy clothes online Kigali, women's fashion Rwanda";
require_once 'includes/header.php';
?>
<!-- WebSite & LocalBusiness Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "name": "Zora Online Shopping",
      "url": "https://<?= $_SERVER['HTTP_HOST'] ?>/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://<?= $_SERVER['HTTP_HOST'] ?>/shop.php?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "LocalBusiness",
      "name": "Zora Online Shopping",
      "image": "https://<?= $_SERVER['HTTP_HOST'] ?>/uploads/logo.png",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Kigali",
        "addressLocality": "Kigali",
        "addressCountry": "RW"
      },
      "telephone": "+250000000000",
      "url": "https://<?= $_SERVER['HTTP_HOST'] ?>/"
    }
  ]
}
</script>
<?php
$wishlist_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_res = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
    while ($w_row = mysqli_fetch_assoc($w_res)) {
        $wishlist_product_ids[] = $w_row['product_id'];
    }
}

// Render Product Card Function
if (!function_exists('render_product_card')) {
function render_product_card($p) {
    global $wishlist_product_ids, $is_rw;
    $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
    $fav_active = $is_fav ? 'active' : '';
    ob_start();
    ?>
    <div class="col-6 col-sm-4 col-md-3 col-xl-custom-5">
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
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
}
?>

<!-- HERO SECTION -->
<div class="container my-1 my-md-2">
  <section class="hero shadow-sm">
    <div class="hero-grid-lines"></div>
    <div class="container-fluid px-4 px-md-5 py-3">
      <div class="row align-items-center justify-content-between g-3">
        <div class="col-lg-6 hero-content text-start">
          <h1 class="hero-title mb-2 text-start" style="font-size: clamp(1.35rem, 2.3vw, 1.75rem); font-weight: 800; color: #ffffff; line-height: 1.15;">
            <?= __('welcome_to') ?><br>
            <span style="font-style: italic; font-weight: 800; color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.6);">Zora Online Shopping</span>
          </h1>
          <div class="hero-buttons-wrap d-flex align-items-center justify-content-start gap-2 flex-wrap mt-3 pt-1">
              <a href="shop.php" class="btn-hero text-decoration-none">
                <?= __('shop_now') ?> <i class="fas fa-arrow-right ms-1"></i>
              </a>
              <a href="#categories" class="btn-hero-outline text-decoration-none">
                <?= __('browse_categories') ?>
              </a>
          </div>
        </div>
        <div class="col-lg-5 d-none d-lg-block text-end">
          <div class="hero-visual ms-auto">
            <div class="hero-circle ms-auto">
              <div class="hero-product-cards">
                <?php
                $hero_cat_query = mysqli_query($conn, "SELECT id, name, icon, image FROM categories LIMIT 4");
                if($hero_cat_query && mysqli_num_rows($hero_cat_query) > 0) {
                    while($hcat = mysqli_fetch_assoc($hero_cat_query)) {
                        $cat_img = '';
                        if(!empty($hcat['image']) && file_exists('uploads/'.$hcat['image'])) {
                            $cat_img = $hcat['image'];
                        } else {
                            $cat_id = (int)$hcat['id'];
                            $p_img_q = mysqli_query($conn, "SELECT image FROM products WHERE category_id = $cat_id AND image != '' LIMIT 1");
                            if($p_img_q && $p_img_row = mysqli_fetch_assoc($p_img_q)) {
                                if(!empty($p_img_row['image']) && file_exists('uploads/'.$p_img_row['image'])) {
                                    $cat_img = $p_img_row['image'];
                                }
                            }
                        }
                        echo '<a href="shop.php?category='.urlencode($hcat['id']).'" class="cube-face">';
                        echo '<div class="hero-mini-card">';
                        if($cat_img) {
                            echo '<div class="hero-cat-img-wrap"><img src="uploads/'.htmlspecialchars($cat_img).'" class="hero-cat-img" alt="'.htmlspecialchars($hcat['name']).'"><div class="hero-cat-overlay"></div></div>';
                            echo '<div class="name">'.htmlspecialchars($hcat['name']).'</div>';
                        } else {
                            echo '<div class="icon"><i class="fas fa-layer-group"></i></div>';
                            echo '<div class="name">'.htmlspecialchars($hcat['name']).'</div>';
                        }
                        echo '</div>';
                        echo '</a>';
                    }
                } else {
                    $demo_cats = [
                        ['name' => 'Accessories', 'icon' => 'fas fa-gem'],
                        ['name' => 'Fashion', 'icon' => 'fas fa-tshirt'],
                        ['name' => 'Electronics', 'icon' => 'fas fa-headphones'],
                        ['name' => 'Sports', 'icon' => 'fas fa-futbol']
                    ];
                    foreach($demo_cats as $dc) {
                        echo '<a href="shop.php" class="cube-face"><div class="hero-mini-card"><div class="icon"><i class="'.$dc['icon'].'"></i></div><div class="name">'.$dc['name'].'</div></div></a>';
                    }
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- TRENDING PRODUCTS (Formerly Most Popular) -->
<section class="py-5" style="background:var(--bg2)">
  <div class="container px-1 px-md-2">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <div class="section-eyebrow"><?= __('hot_right_now') ?></div>
        <h2 class="section-title"><?= __('trending_products') ?></h2>
      </div>
    </div>
    <div class="row g-3">
      <?php
      // First try to get featured products
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 LIMIT 5");
      
      // If no featured products exist, fallback to 5 random products just so the user can see how it looks
      if (mysqli_num_rows($prods) == 0) {
          $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY RAND() LIMIT 5");
      }

      while ($p = mysqli_fetch_assoc($prods)): 
        echo render_product_card($p);
      endwhile; ?>
    </div>
  </div>
</section>

<!-- TRENDING SEARCH -->
<section class="py-2" style="background:var(--bg3); border-bottom: 1px solid var(--border);">
    <div class="container px-2 d-flex align-items-center">
        <span class="text-muted-custom font-weight-bold me-2 text-sm text-uppercase letter-spacing-1 flex-shrink-0" style="font-size: 0.75rem; color: var(--text3);"><?= __('trending') ?></span>
        <div class="trending-searches-scroll">
            <a href="shop.php?search=headphones" class="category-pill-small"><i class="fas fa-headphones"></i> Headphones</a>
            <a href="shop.php?search=watch" class="category-pill-small"><i class="fas fa-clock"></i> Watches</a>
            <a href="shop.php?search=smartphone" class="category-pill-small"><i class="fas fa-mobile-alt"></i> Smartphones</a>
            <a href="shop.php?search=shoes" class="category-pill-small"><i class="fas fa-shoe-prints"></i> Sneakers</a>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="py-5" id="categories" style="background:var(--bg2)">
  <div class="container px-1 px-md-2">
    <div class="text-center mb-4">
      <div class="section-eyebrow"><?= __('what_we_offer') ?></div>
      <h2 class="section-title"><?= __('shop_by_category') ?></h2>
      <div class="divider mx-auto mt-3"></div>
    </div>
    <div class="categories-scroll-container mt-2">
      <?php
      $cats = mysqli_query($conn, "SELECT * FROM categories LIMIT 8");
      while ($c = mysqli_fetch_assoc($cats)): ?>
        <a href="shop.php?category=<?= $c['id'] ?>" class="text-decoration-none flex-shrink-0">
          <div class="cat-card-small">
            <?php if(!empty($c['image']) && file_exists('uploads/'.$c['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($c['image']) ?>" class="cat-image" alt="<?= htmlspecialchars($c['name']) ?>">
            <?php else: ?>
                <div class="cat-image-placeholder"><i class="fas fa-box"></i></div>
            <?php endif; ?>
            <div class="cat-name"><?= htmlspecialchars($c['name']) ?></div>
          </div>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- NEW ARRIVALS -->
<section class="py-5">
  <div class="container px-1 px-md-2">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2" data-aos="fade-up">
      <div>
        <div class="section-eyebrow"><?= __('just_in') ?></div>
        <h2 class="section-title"><?= __('new_arrivals') ?></h2>
      </div>
      <a href="shop.php?sort=newest" class="btn-hero-outline text-decoration-none"><?= __('view_all') ?> <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
      <?php
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 5");
      while ($p = mysqli_fetch_assoc($prods)): 
        echo render_product_card($p);
      endwhile; ?>
    </div>
  </div>
</section>


<!-- ALL PRODUCTS -->
<section class="py-5">
  <div class="container px-1 px-md-2">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="section-eyebrow"><?= __('explore') ?></div>
      <h2 class="section-title"><?= __('all_products') ?></h2>
      <div class="divider mx-auto mt-3"></div>
    </div>
    <div class="row g-3" id="allProductsGrid">
      <?php
      $initial_limit = 10;
      $prods = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC LIMIT $initial_limit");
      $initial_count = 0;
      while ($p = mysqli_fetch_assoc($prods)): 
        $initial_count++;
        echo render_product_card($p);
      endwhile; 

      $total_count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
      $total_products_count = ($total_count_q && $t_row = mysqli_fetch_assoc($total_count_q)) ? (int)$t_row['total'] : 0;
      $has_more_initial = $initial_count < $total_products_count;
      ?>
    </div>
    <?php if ($has_more_initial): ?>
    <div class="text-center mt-5" id="loadMoreWrap">
        <button type="button" id="loadMoreProductsBtn" class="btn-hero text-decoration-none px-5 py-3 border-0" data-offset="<?= $initial_count ?>" data-limit="10" style="cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
          <span class="btn-text"><?= __('load_more_products') ?></span>
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('loadMoreProductsBtn');
    const productsGrid = document.getElementById('allProductsGrid');
    const loadMoreWrap = document.getElementById('loadMoreWrap');

    if (loadMoreBtn && productsGrid) {
        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const offset = parseInt(loadMoreBtn.getAttribute('data-offset')) || 0;
            const limit = parseInt(loadMoreBtn.getAttribute('data-limit')) || 10;
            const btnText = loadMoreBtn.querySelector('.btn-text');
            const spinner = loadMoreBtn.querySelector('.spinner-border');

            // Set loading state
            loadMoreBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (btnText) btnText.textContent = 'Loading...';

            fetch(`api_load_products.php?offset=${offset}&limit=${limit}&lang=<?= $lang ?>`)
                .then(res => {
                    if (!res.ok) throw new Error('Server returned ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.status === 'success' && data.html) {
                        // Append new product cards at the bottom of the grid
                        productsGrid.insertAdjacentHTML('beforeend', data.html);
                        
                        // Update offset
                        loadMoreBtn.setAttribute('data-offset', data.next_offset);

                        // If no more products, show completed message
                        if (!data.has_more || data.count === 0) {
                            if (loadMoreWrap) {
                                loadMoreWrap.innerHTML = '<p class="text-muted mt-4 fw-bold"><i class="fas fa-check-circle text-success me-1"></i> All products loaded</p>';
                            }
                        } else {
                            loadMoreBtn.disabled = false;
                            if (spinner) spinner.classList.add('d-none');
                            if (btnText) btnText.textContent = '<?= __('load_more_products') ?>';
                        }
                    } else {
                        if (loadMoreWrap) {
                            loadMoreWrap.innerHTML = '<p class="text-muted mt-4 fw-bold"><i class="fas fa-check-circle text-success me-1"></i> All products loaded</p>';
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading products:', err);
                    loadMoreBtn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    if (btnText) btnText.textContent = '<?= __('load_more_products') ?>';
                });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
