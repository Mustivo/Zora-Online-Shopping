<?php
// require_once './core/config.php';
require_once dirname(__DIR__) . '/core/config.php';
$page_title = "Zora Online Shopping | Latest Fashion in Rwanda";
$meta_desc = "Discover the latest fashion trends at Zora Online Shopping. Shop high-quality clothes, shoes, and accessories with fast delivery in Kigali, Rwanda.";
$meta_keywords = "Online shopping Rwanda, Zora shop Rwanda, buy clothes online Kigali, women's fashion Rwanda";
// require_once './includes/header.php';
require_once dirname(__DIR__) . '/includes/header.php';
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
}
}
?>
