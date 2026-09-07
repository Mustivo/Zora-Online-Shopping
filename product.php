<?php
require_once 'core/config.php';
<<<<<<< HEAD
=======
require_once 'includes/header.php';
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
<<<<<<< HEAD
    $page_title = "Product Not Found";
    require_once 'includes/header.php';
    echo "<div class='container py-5 text-center'><h2>" . __('product_not_found') . "</h2><a href='shop.php' class='btn-hero'>" . __('back_to_shop') . "</a></div>";
    require_once 'includes/footer.php';
=======
    echo "<div class='container py-5 text-center'><h2>Product not found.</h2><a href='shop.php' class='btn-hero'>Back to Shop</a></div>";
    require_once 'footer.php';
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    exit;
}

$p = mysqli_fetch_assoc($result);

<<<<<<< HEAD
$page_title = $p['name'] . " | Zora Shop Rwanda";
$meta_desc = mb_substr(strip_tags($p['description']), 0, 155) . "...";
$meta_keywords = $p['name'] . ", " . $p['cat_name'] . ", buy online Rwanda";
$canonical_url = "https://" . $_SERVER['HTTP_HOST'] . "/product.php?id=" . $id;

require_once 'includes/header.php';
?>
<!-- Product Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "<?= htmlspecialchars($p['name']) ?>",
  "image": "https://<?= $_SERVER['HTTP_HOST'] ?>/uploads/<?= htmlspecialchars($p['image']) ?>",
  "description": "<?= htmlspecialchars(strip_tags($p['description'])) ?>",
  "brand": {
    "@type": "Brand",
    "name": "Zora Shop"
  },
  "offers": {
    "@type": "Offer",
    "url": "https://<?= $_SERVER['HTTP_HOST'] ?>/product.php?id=<?= $id ?>",
    "priceCurrency": "RWF",
    "price": "<?= $p['price'] ?>",
    "availability": "<?= $p['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
  }
}
</script>
<?php
if (!function_exists('parse_sizes_json')) {
    function parse_sizes_json($sizes_string) {
        if (empty($sizes_string)) return [];
        $sizes_string = html_entity_decode($sizes_string);
        $decoded = json_decode($sizes_string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = json_decode(stripslashes($sizes_string), true);
        }
        $sizes = [];
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach($decoded as $item) {
                $val = is_array($item) ? ($item['value'] ?? ($item['size'] ?? '')) : $item;
                $val = trim((string)$val);
                if ($val !== '' && strtolower($val) !== 'standard' && strtolower($val) !== 'default' && strtolower($val) !== 'none') {
                    $sizes[] = $val;
                }
            }
        } else {
            $raw = array_filter(array_map('trim', explode(',', $sizes_string)));
            foreach ($raw as $item) {
                if ($item !== '' && strtolower($item) !== 'standard' && strtolower($item) !== 'default' && strtolower($item) !== 'none') {
                    $sizes[] = $item;
                }
            }
        }
        return array_values(array_unique($sizes));
    }
}

$colors_query = mysqli_query($conn, "SELECT id, color_name, image_path, sizes, price FROM product_images WHERE product_id = $id ORDER BY id ASC, created_at ASC");
$colors = [];
$raw_gallery_images = [];

while ($col = mysqli_fetch_assoc($colors_query)) {
    $cName = trim($col['color_name'] ?? '');
    
    // Check if this is a generic placeholder name or a genuine color variant
    $is_generic = in_array(strtolower($cName), ['', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null']);
    
    if (!$is_generic) {
        // Group images by genuine color variant
        if (!isset($colors[$cName])) {
            $cNameEscaped = mysqli_real_escape_string($conn, $cName);
            $inv_q = mysqli_query($conn, "SELECT size_name, stock, price FROM product_inventory WHERE product_id = $id AND color_name = '$cNameEscaped'");
            
            $parsed_sizes = [];
            $variant_price = (!empty($col['price']) && (float)$col['price'] > 0) ? (float)$col['price'] : null;
            if ($inv_q && mysqli_num_rows($inv_q) > 0) {
                while ($inv = mysqli_fetch_assoc($inv_q)) {
                    if ($variant_price === null && !empty($inv['price']) && (float)$inv['price'] > 0) {
                        $variant_price = (float)$inv['price'];
                    }
                    $sz = trim($inv['size_name'] ?? '');
                    if ($sz !== '' && strtolower($sz) !== 'standard' && strtolower($sz) !== 'default' && strtolower($sz) !== 'none') {
                        $parsed_sizes[] = ['size' => $sz, 'stock' => (int)$inv['stock']];
                    }
                }
            } else {
                $legacy_str = !empty($col['sizes']) ? $col['sizes'] : ($p['sizes'] ?? '');
                $legacy = parse_sizes_json($legacy_str);
                foreach ($legacy as $ls) {
                    $ls_clean = trim($ls);
                    if ($ls_clean !== '' && strtolower($ls_clean) !== 'standard' && strtolower($ls_clean) !== 'default' && strtolower($ls_clean) !== 'none') {
                        $parsed_sizes[] = ['size' => $ls_clean, 'stock' => (int)$p['stock']];
                    }
                }
            }
            
            $col['price'] = $variant_price;
            $col['parsed_sizes'] = $parsed_sizes;
            $col['images'] = [];
            $col['primary_image'] = !empty($col['image_path']) ? $col['image_path'] : '';
            $colors[$cName] = $col;
        }
        if (!empty($col['image_path'])) {
            $colors[$cName]['images'][] = $col['image_path'];
        }
    } else {
        if (!empty($col['image_path'])) {
            $raw_gallery_images[] = $col['image_path'];
        }
    }
}

// Also include any variations that exist in product_inventory without photos
$inv_colors_q = mysqli_query($conn, "SELECT DISTINCT color_name FROM product_inventory WHERE product_id = $id AND color_name != ''");
if ($inv_colors_q) {
    while ($inv_c = mysqli_fetch_assoc($inv_colors_q)) {
        $cName = trim($inv_c['color_name']);
        if (!empty($cName) && !isset($colors[$cName])) {
            $cNameEscaped = mysqli_real_escape_string($conn, $cName);
            $inv_q = mysqli_query($conn, "SELECT size_name, stock, price FROM product_inventory WHERE product_id = $id AND color_name = '$cNameEscaped'");
            $parsed_sizes = [];
            $variant_price = null;
            if ($inv_q) {
                while ($inv = mysqli_fetch_assoc($inv_q)) {
                    if ($variant_price === null && !empty($inv['price']) && (float)$inv['price'] > 0) {
                        $variant_price = (float)$inv['price'];
                    }
                    $sz = trim($inv['size_name'] ?? '');
                    if ($sz !== '' && strtolower($sz) !== 'standard' && strtolower($sz) !== 'default' && strtolower($sz) !== 'none') {
                        $parsed_sizes[] = ['size' => $sz, 'stock' => (int)$inv['stock']];
                    }
                }
            }
            $colors[$cName] = [
                'id' => 0,
                'color_name' => $cName,
                'image_path' => '',
                'sizes' => '',
                'price' => $variant_price,
                'parsed_sizes' => $parsed_sizes,
                'images' => [],
                'primary_image' => ''
            ];
        }
    }
}

// Reset keys to be numeric for easier JS iteration
$colors = array_values($colors);

$all_product_images = [];
if (!empty($p['image'])) {
    $all_product_images[] = $p['image'];
}
foreach ($raw_gallery_images as $gimg) {
    if (!in_array($gimg, $all_product_images)) {
        $all_product_images[] = $gimg;
    }
}
foreach ($colors as $cObj) {
    foreach ($cObj['images'] as $cImg) {
        if (!in_array($cImg, $all_product_images)) {
            $all_product_images[] = $cImg;
        }
    }
}

$parsed_sizes = parse_sizes_json($p['sizes'] ?? '');

$parsed_colors = [];
if (!empty($p['colors'])) {
    $decoded = json_decode($p['colors'], true);
    if (is_array($decoded)) {
        foreach($decoded as $item) {
            $v = is_array($item) ? ($item['value'] ?? '') : $item;
            $v_clean = trim($v);
            if (!in_array(strtolower($v_clean), ['', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null'])) {
                $parsed_colors[] = $v_clean;
            }
        }
    } else {
        $raw_c = array_filter(array_map('trim', explode(',', $p['colors'])));
        foreach ($raw_c as $rc) {
            if (!in_array(strtolower($rc), ['', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null'])) {
                $parsed_colors[] = $rc;
            }
        }
    }
=======
$colors_query = mysqli_query($conn, "SELECT id, color_name, image_path FROM product_images WHERE product_id = $id ORDER BY created_at ASC");
$colors = [];
while ($col = mysqli_fetch_assoc($colors_query)) {
    $colors[] = $col;
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
}

$wishlist_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_res = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
    while ($w_row = mysqli_fetch_assoc($w_res)) {
        $wishlist_product_ids[] = $w_row['product_id'];
    }
}

<<<<<<< HEAD
if (!function_exists('render_product_card')) {
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
function render_product_card($p) {
    global $wishlist_product_ids;
    $is_fav = in_array($p['id'], $wishlist_product_ids ?? []);
    $fav_active = $is_fav ? 'active' : '';
    ?>
<<<<<<< HEAD
    <div class="col-6 col-sm-6 col-md-4 col-lg-3">
      <div class="product-card h-100 shadow-sm d-flex flex-column" style="border-radius: 14px; overflow: hidden; background: var(--card); border: 1px solid var(--border);">
        <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none">
            <div class="product-image" style="aspect-ratio: 1 / 1; width: 100%; height: auto; position: relative; overflow: hidden; background: var(--bg3);">
              <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
                  <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center"><i class="fas fa-box text-muted" style="font-size: 3rem;"></i></div>
              <?php endif; ?>
              <?php if($p['stock'] <= 0): ?>
                  <div class="product-badge sale bg-danger text-white"><?= __('out_of_stock') ?></div>
              <?php endif; ?>
              <?php if(isset($p['is_new']) && $p['is_new']): ?>
                  <div class="product-badge bg-success text-white" style="left:auto;right:10px;"><?= __('new_badge') ?></div>
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
              <?php endif; ?>
              <div class="product-wishlist <?= $fav_active ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this); event.preventDefault(); event.stopPropagation();"><i class="fas fa-heart"></i></div>
            </div>
        </a>
<<<<<<< HEAD
        <div class="product-body d-flex flex-column flex-grow-1" style="padding: 1rem;">
          <div class="product-category" style="font-size: 0.75rem; color: var(--accent); font-weight: 700; text-transform: uppercase; margin-bottom: 2px;"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
          <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none"><div class="product-name notranslate" style="font-size: 0.95rem; font-weight: 700; color: var(--text); line-height: 1.3; margin-bottom: 8px;"><?= htmlspecialchars($p['name']) ?></div></a>

          <div class="mt-auto pt-2">
            <div class="mb-2">
              <?php if (!empty($p['discount_price']) && $p['discount_price'] > 0): ?>
                  <div class="product-price">
                      <span class="text-danger fw-bold" style="font-size: 1.05rem;"><?= number_format($p['discount_price'], 0) ?> RFW</span>
                      <del class="text-muted ms-2" style="font-size: 0.8rem;"><?= number_format($p['price'], 0) ?> RFW</del>
                  </div>
              <?php else: ?>
                  <div class="product-price" style="font-size: 1.05rem; font-weight: 700; color: #3b82f6;"><?= number_format($p['price'], 0) ?> RFW</div>
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
                <button class="btn-add-cart w-100 d-flex align-items-center justify-content-center gap-2" style="background: #cbd5e1; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 0.85rem; padding: 10px 14px; text-transform: uppercase;" disabled><i class="fas fa-shopping-cart"></i> <?= $btn_text ?></button>
            <?php else: ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="btn-add-cart w-100 text-decoration-none text-center d-flex align-items-center justify-content-center gap-2" style="background: #3b82f6; border: none; border-radius: 8px; color: white !important; font-weight: 700; font-size: 0.85rem; padding: 10px 14px; text-transform: uppercase; transition: all 0.2s ease;"><i class="fas fa-shopping-cart"></i> <?= $btn_text ?></a>
            <?php endif; ?>
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
          </div>
        </div>
      </div>
    </div>
    <?php
}
<<<<<<< HEAD
}
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
?>

<div class="container py-5">
  <nav style="font-size:0.8rem;color:var(--text3);margin-bottom:1.5rem">
<<<<<<< HEAD
    <a href="index.php" class="text-decoration-none text-muted-custom"><?= __('home') ?></a> <span class="mx-2">/</span>
    <a href="shop.php" class="text-decoration-none text-muted-custom"><?= __('shop') ?></a> <span class="mx-2">/</span>
    <span class="gold notranslate"><?= htmlspecialchars($p['name']) ?></span>
=======
    <a href="index.php" class="text-decoration-none text-muted-custom">Home</a> <span class="mx-2">/</span>
    <a href="shop.php" class="text-decoration-none text-muted-custom">Shop</a> <span class="mx-2">/</span>
    <span class="gold"><?= htmlspecialchars($p['name']) ?></span>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
  </nav>

  <div class="row g-4">
    <div class="col-md-6">
<<<<<<< HEAD
      <div class="product-detail-img shadow-sm" style="overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative; border-radius: 14px; border: 1px solid #e2e8f0; aspect-ratio: 1 / 1; width: 100%; max-width: 440px; margin: 0 auto; background: #ffffff; padding: 0;">
        <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
            <img id="mainProductImage" src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 100%; height: 100%; object-fit: contain; display: block; border-radius: 14px;">
        <?php else: ?>
            <i id="mainProductImageFallback" class="fas fa-box text-muted" style="font-size: 5rem;"></i>
            <img id="mainProductImage" src="" alt="Product Image" style="width: 100%; height: 100%; object-fit: contain; display: none; border-radius: 14px;">
        <?php endif; ?>
        
        <button id="prevImageBtn" class="img-nav-btn" style="display:none;" onclick="navigateImage(-1)"><i class="fas fa-chevron-left"></i></button>
        <button id="nextImageBtn" class="img-nav-btn" style="display:none;" onclick="navigateImage(1)"><i class="fas fa-chevron-right"></i></button>
      </div>
      
      <div id="productThumbnails" class="gap-2 mt-3 overflow-auto pb-2" style="white-space: nowrap; display: none; max-width: 440px; margin: 0 auto;">
      </div>
      
      <style>
      .thumbnail-img { border: 2px solid transparent; opacity: 0.6; transition: all 0.3s ease; }
      .thumbnail-img:hover { opacity: 0.9; }
      .thumbnail-img.active { border-color: #3b82f6; opacity: 1; }
      
      .img-nav-btn {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          background: rgba(255, 255, 255, 0.8);
          color: var(--text);
          border: none;
          width: 45px;
          height: 45px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all 0.3s ease;
          box-shadow: 0 4px 10px rgba(0,0,0,0.15);
          z-index: 10;
          font-size: 1.1rem;
      }
      .img-nav-btn:hover {
          background: #3b82f6;
          color: white;
          transform: translateY(-50%) scale(1.05);
      }
      #prevImageBtn { left: 15px; }
      #nextImageBtn { right: 15px; }
      
      [data-theme="dark"] .img-nav-btn {
          background: rgba(30, 30, 30, 0.8);
          color: white;
      }
      [data-theme="dark"] .img-nav-btn:hover {
          background: #3b82f6;
      }
      </style>

    </div>
    <div class="col-md-6">
      <div class="text-uppercase fw-bold mb-1" style="color: #3b82f6; font-size: 0.85rem; letter-spacing: 0.5px;"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
      <h1 class="fw-bold mb-2 notranslate" style="color: #1e293b; font-size: 1.85rem; letter-spacing: -0.5px;"><?= htmlspecialchars($p['name']) ?></h1>
      <?php 
      $rating = isset($p['rating']) ? (float)$p['rating'] : 0;
      if ($rating > 0):
      ?>
      <div class="product-rating mb-3 text-start" style="font-size: 1rem;">
          <?php 
          $full = floor($rating);
          $half = ($rating - $full) >= 0.5;
          $empty = 5 - $full - ($half ? 1 : 0);
          for($i=0; $i<$full; $i++) echo '<i class="fas fa-star text-warning"></i>';
          if($half) echo '<i class="fas fa-star-half-alt text-warning"></i>';
          for($i=0; $i<$empty; $i++) echo '<i class="far fa-star text-warning"></i>';
          ?>
          <span class="ms-2 text-muted" style="font-size: 0.9rem;">(<?= number_format($rating, 1) ?>)</span>
      </div>
      <?php endif; ?>
      <?php if (!empty($p['discount_price']) && $p['discount_price'] > 0): ?>
          <div class="detail-price mb-3" style="font-size: 1.6rem; font-weight: 700; color: #3b82f6;" id="productPriceContainer">
              <span id="productCurrentPrice"><?= number_format($p['discount_price'], 0) ?> RFW</span>
              <del id="productOldPrice" class="text-muted ms-3" style="font-size: 1.1rem; font-weight: 400;"><?= number_format($p['price'], 0) ?> RFW</del>
          </div>
          <?php if (!empty($p['discount_expiry'])): ?>
              <?php
                $now = new DateTime();
                $expiry = new DateTime($p['discount_expiry']);
                $diff = $now->diff($expiry);
                if ($diff->invert == 0) {
                    $time_left = "";
                    if ($diff->d > 0) $time_left .= $diff->d . " day(s) ";
                    if ($diff->h > 0) $time_left .= $diff->h . " hour(s) ";
                    if (empty($time_left) && $diff->i > 0) $time_left .= $diff->i . " min(s)";
                    if (!empty($time_left)) {
                        echo '<div class="alert alert-warning py-2 px-3 mb-4 d-inline-block" style="border-radius: 8px; font-size: 0.9rem;"><i class="fas fa-clock me-2"></i> Hurry! Discount expires in <strong>' . trim($time_left) . '</strong></div><br>';
                    }
                }
              ?>
          <?php else: ?>
              <div class="mb-4"></div>
          <?php endif; ?>
      <?php else: ?>
          <div class="detail-price mb-3" style="font-size: 1.6rem; font-weight: 700; color: #3b82f6;" id="productPriceContainer">
              <span id="productCurrentPrice"><?= number_format($p['price'], 0) ?> RFW</span>
              <del id="productOldPrice" class="text-muted ms-3" style="font-size: 1.1rem; font-weight: 400; display: none;"></del>
          </div>
      <?php endif; ?>
      
      <div class="mb-4 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
        <span style="color: #64748b; font-weight: 500;"><?= __('availability') ?>:</span>
        <?php if($p['stock'] > 0): ?>
            <span class="d-flex align-items-center gap-1" style="color: #10b981; font-weight: 600;"><i class="fas fa-check-circle"></i> <?= __('in_stock') ?> (<?= $p['stock'] ?> <?= __('available') ?>)</span>
        <?php else: ?>
            <span class="d-flex align-items-center gap-1 text-danger fw-semibold"><i class="fas fa-times-circle"></i> <?= __('out_of_stock') ?></span>
        <?php endif; ?>
      </div>

      <?php 
      $initial_sizes = [];
      $has_default_image = isset($p['image']) && $p['image'];
      if ($has_default_image) {
          $initial_sizes = $parsed_sizes;
      } else if (count($colors) > 0) {
          $initial_sizes = $colors[0]['parsed_sizes'] ?? [];
      } else {
          $initial_sizes = $parsed_sizes;
      }
      ?>

      <?php if(count($colors) > 0 || count($parsed_colors) > 0): ?>
      <div class="mb-4" id="colorSection">
          <label class="form-label-custom mb-2 text-uppercase fw-bold" style="color: #64748b; font-size: 0.8rem; letter-spacing: 0.5px;"><?= __('color') ?>: <span id="colorLabelText" class="text-primary fw-bold ms-1" style="display:inline;"><?= __('select_a_color') ?></span></label>
          <div class="d-flex gap-2 flex-wrap" id="colorSwatches">
              
              <?php foreach($colors as $i => $color): ?>
              <button type="button" class="color-btn" 
                  data-sizes='<?= htmlspecialchars(json_encode($color['parsed_sizes']), ENT_QUOTES, 'UTF-8') ?>' 
                  data-images='<?= htmlspecialchars(json_encode($color['images']), ENT_QUOTES, 'UTF-8') ?>'
                  data-price='<?= !empty($color['price']) ? (float)$color['price'] : '' ?>'
                  onclick="changeProductImage(this, '<?= htmlspecialchars($color['color_name'], ENT_QUOTES) ?>')"><?= htmlspecialchars($color['color_name']) ?></button>
              <?php endforeach; ?>
              
              <?php foreach($parsed_colors as $i => $color): 
                  if (strtolower(trim($color)) === 'gallery') $color = 'Image';
              ?>
              <button type="button" class="color-btn" onclick="changeProductImage(this, '<?= htmlspecialchars($color, ENT_QUOTES) ?>')"><?= htmlspecialchars($color) ?></button>
              <?php endforeach; ?>
          </div>
      </div>
      <?php endif; ?>

      <div class="mb-4" id="sizeSection" style="<?= count($initial_sizes) > 0 ? '' : 'display:none;' ?>">
          <label class="form-label-custom mb-2 text-uppercase fw-bold" style="color: #64748b; font-size: 0.8rem; letter-spacing: 0.5px;"><?= __('size') ?>: <span id="sizeLabelText" class="text-primary fw-bold ms-1" style="display:inline;"><?= count($initial_sizes) > 0 ? __('select_a_size') : '' ?></span></label>
          <div class="d-flex gap-2 flex-wrap" id="sizeSwatches">
              <?php 
              foreach($initial_sizes as $i => $sizeObj): 
                  $sName = is_array($sizeObj) ? $sizeObj['size'] : $sizeObj;
                  $sStock = is_array($sizeObj) ? $sizeObj['stock'] : null;
                  
                  if ($sStock !== null && $sStock <= 0) {
                      echo '<button type="button" class="size-btn disabled" disabled>' . htmlspecialchars($sName) . ' (' . __('out_of_stock') . ')</button>';
                  } else {
                      $maxStk = $sStock !== null ? $sStock : $p['stock'];
                      echo '<button type="button" class="size-btn" onclick="selectSize(this, \'' . htmlspecialchars($sName, ENT_QUOTES) . '\', ' . $maxStk . ')">' . htmlspecialchars($sName) . '</button>';
                  }
              endforeach; 
              ?>
          </div>
      </div>
      <script>
      let currentImages = [];
      let currentImageIndex = 0;

      function updateImageNav() {
          const prevBtn = document.getElementById('prevImageBtn');
          const nextBtn = document.getElementById('nextImageBtn');
          
          if (currentImages.length > 1) {
              prevBtn.style.display = 'flex';
              nextBtn.style.display = 'flex';
          } else {
              prevBtn.style.display = 'none';
              nextBtn.style.display = 'none';
          }
      }

      function navigateImage(direction) {
          if (currentImages.length <= 1) return;
          
          currentImageIndex += direction;
          
          if (currentImageIndex < 0) {
              currentImageIndex = currentImages.length - 1;
          } else if (currentImageIndex >= currentImages.length) {
              currentImageIndex = 0;
          }
          
          const thumbContainer = document.getElementById('productThumbnails');
          let thumbEl = null;
          if (thumbContainer && thumbContainer.style.display !== 'none' && thumbContainer.children.length > currentImageIndex) {
              thumbEl = thumbContainer.children[currentImageIndex];
          }
          
          setMainImage(thumbEl, 'uploads/' + currentImages[currentImageIndex]);
      }

      function setMainImage(thumbnailElement, src) {
          const img = document.getElementById('mainProductImage');
          const fallback = document.getElementById('mainProductImageFallback');
          if (src !== '') {
              if (img) {
                  img.src = src;
                  img.style.display = 'block';
                  if (fallback) fallback.style.display = 'none';
              }
              const selectedImgInput = document.getElementById('selectedImageInput');
              if (selectedImgInput) {
                  const parts = src.split('/');
                  selectedImgInput.value = parts[parts.length - 1];
              }
          }
          
          document.querySelectorAll('.thumbnail-img').forEach(el => el.classList.remove('active'));
          if (thumbnailElement) {
              thumbnailElement.classList.add('active');
              thumbnailElement.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
      }

      const defaultBaseSizes = <?= json_encode($initial_sizes) ?>;
      const allBaseImages = <?= json_encode($all_product_images) ?>;

      function restoreBaseGallery() {
          currentImages = (allBaseImages && allBaseImages.length > 0) ? allBaseImages : [];
          currentImageIndex = 0;
          const thumbContainer = document.getElementById('productThumbnails');
          if (currentImages.length > 0) {
              if (thumbContainer && currentImages.length > 1) {
                  thumbContainer.innerHTML = '';
                  thumbContainer.style.display = 'flex';
                  currentImages.forEach((imgSrc, index) => {
                      const img = document.createElement('img');
                      img.src = 'uploads/' + imgSrc;
                      img.className = 'thumbnail-img' + (index === 0 ? ' active' : '');
                      img.style.width = '70px';
                      img.style.height = '70px';
                      img.style.objectFit = 'cover';
                      img.style.borderRadius = '8px';
                      img.style.cursor = 'pointer';
                      img.style.flexShrink = '0';
                      img.onclick = function() { 
                          currentImageIndex = index;
                          setMainImage(this, 'uploads/' + imgSrc); 
                      };
                      thumbContainer.appendChild(img);
                  });
                  setMainImage(thumbContainer.firstChild, 'uploads/' + currentImages[0]);
              } else {
                  if (thumbContainer) thumbContainer.style.display = 'none';
                  setMainImage(null, 'uploads/' + currentImages[0]);
              }
          } else {
              if (thumbContainer) thumbContainer.style.display = 'none';
              setMainImage(null, '<?= !empty($p['image']) ? 'uploads/' . htmlspecialchars($p['image']) : '' ?>');
          }
          updateImageNav();
      }

      const basePrice = <?= (float)$p['price'] ?>;
      const baseDiscountPrice = <?= (!empty($p['discount_price']) && (float)$p['discount_price'] > 0) ? (float)$p['discount_price'] : 0 ?>;

      function updateDisplayPrice(customPrice) {
          const curEl = document.getElementById('productCurrentPrice');
          const oldEl = document.getElementById('productOldPrice');
          if (!curEl) return;
          
          if (customPrice !== null && customPrice !== undefined && customPrice > 0) {
              curEl.innerText = Number(customPrice).toLocaleString() + ' RFW';
              if (oldEl) {
                  oldEl.style.display = 'none';
              }
          } else {
              if (baseDiscountPrice > 0) {
                  curEl.innerText = Number(baseDiscountPrice).toLocaleString() + ' RFW';
                  if (oldEl) {
                      oldEl.innerText = Number(basePrice).toLocaleString() + ' RFW';
                      oldEl.style.display = 'inline';
                  }
              } else {
                  curEl.innerText = Number(basePrice).toLocaleString() + ' RFW';
                  if (oldEl) {
                      oldEl.style.display = 'none';
                  }
              }
          }
      }

      function restoreBaseSizes() {
          const sizeSection = document.getElementById('sizeSection');
          const sizeSwatches = document.getElementById('sizeSwatches');
          const sizeLabelText = document.getElementById('sizeLabelText');
          const selectedSizeInput = document.getElementById('selectedSizeInput');
          
          const validBaseSizes = Array.isArray(defaultBaseSizes) ? defaultBaseSizes.filter(s => {
              const name = (typeof s === 'object') ? (s.size || '') : s;
              return name && name.trim() !== '' && name.toLowerCase() !== 'standard' && name.toLowerCase() !== 'default';
          }) : [];

          if (sizeLabelText) sizeLabelText.innerText = (validBaseSizes.length > 0) ? "<?= __('select_a_size') ?>" : '';
          if (selectedSizeInput) {
              selectedSizeInput.value = '';
              selectedSizeInput.disabled = (validBaseSizes.length === 0);
          }
          
          if (validBaseSizes.length > 0) {
              if (sizeSection) sizeSection.style.display = 'block';
              if (sizeSwatches) {
                  sizeSwatches.innerHTML = '';
                  validBaseSizes.forEach(sizeObj => {
                      const sName = (typeof sizeObj === 'object') ? sizeObj.size : sizeObj;
                      const sStock = (typeof sizeObj === 'object') ? sizeObj.stock : null;
                      const btn = document.createElement('button');
                      btn.type = 'button';
                      if (sStock !== null && sStock <= 0) {
                          btn.className = 'size-btn disabled';
                          btn.disabled = true;
                          btn.innerText = sName + ' (<?= __('out_of_stock') ?>)';
                      } else {
                          const maxStk = (sStock !== null) ? sStock : <?= (int)$p['stock'] ?>;
                          btn.className = 'size-btn';
                          btn.onclick = function() { selectSize(this, sName, maxStk); };
                          btn.innerText = sName;
                      }
                      sizeSwatches.appendChild(btn);
                  });
              }
          } else {
              if (sizeSection) sizeSection.style.display = 'none';
              if (sizeSwatches) sizeSwatches.innerHTML = '';
          }
      }

      function changeProductImage(element, colorName) {
          const isCurrentlyActive = element.classList.contains('active');
          
          if (isCurrentlyActive) {
              // Click again -> Toggle OFF / Deactivate
              element.classList.remove('active');
              document.getElementById('colorLabelText').innerText = "<?= __('select_a_color') ?>";
              document.getElementById('selectedColorInput').value = '';
              updateDisplayPrice(null);
              restoreBaseGallery();
              restoreBaseSizes();
              return;
          }
          
          // Toggle ON / Activate
          const imagesAttr = element.getAttribute('data-images');
          currentImages = [];
          if (imagesAttr) {
              try {
                  currentImages = JSON.parse(imagesAttr);
              } catch(e) {}
          }
          
          const thumbContainer = document.getElementById('productThumbnails');
          
          if (currentImages.length > 0 && currentImages[0]) {
              currentImageIndex = 0;
              
              if (thumbContainer && currentImages.length > 1) {
                  thumbContainer.innerHTML = '';
                  thumbContainer.style.display = 'flex';
                  currentImages.forEach((imgSrc, index) => {
                      const img = document.createElement('img');
                      img.src = 'uploads/' + imgSrc;
                      img.className = 'thumbnail-img' + (index === 0 ? ' active' : '');
                      img.style.width = '70px';
                      img.style.height = '70px';
                      img.style.objectFit = 'cover';
                      img.style.borderRadius = '8px';
                      img.style.cursor = 'pointer';
                      img.style.flexShrink = '0';
                      img.onclick = function() { 
                          currentImageIndex = index;
                          setMainImage(this, 'uploads/' + imgSrc); 
                      };
                      thumbContainer.appendChild(img);
                  });
                  setMainImage(thumbContainer.firstChild, 'uploads/' + currentImages[0]);
              } else {
                  if (thumbContainer) thumbContainer.style.display = 'none';
                  setMainImage(null, 'uploads/' + currentImages[0]);
              }
          } else {
              restoreBaseGallery();
          }
          
          updateImageNav();
          
          document.querySelectorAll('#colorSwatches .color-btn').forEach(el => el.classList.remove('active'));
          element.classList.add('active');
          document.getElementById('colorLabelText').innerText = colorName;
          document.getElementById('selectedColorInput').value = colorName;

          // Check custom variation price
          const priceAttr = element.getAttribute('data-price');
          const customPrice = (priceAttr && parseFloat(priceAttr) > 0) ? parseFloat(priceAttr) : null;
          updateDisplayPrice(customPrice);
          
          const sizeSection = document.getElementById('sizeSection');
          const sizeSwatches = document.getElementById('sizeSwatches');
          const sizeLabelText = document.getElementById('sizeLabelText');
          const selectedSizeInput = document.getElementById('selectedSizeInput');

          if (sizeLabelText) sizeLabelText.innerText = "<?= __('select_a_size') ?>";
          if (selectedSizeInput) selectedSizeInput.value = '';

          const sizesAttr = element.getAttribute('data-sizes');
          let validSizes = [];
          if (sizesAttr) {
              try {
                  const rawSizes = JSON.parse(sizesAttr);
                  if (Array.isArray(rawSizes)) {
                      validSizes = rawSizes.filter(s => {
                          const name = (typeof s === 'object') ? (s.size || '') : s;
                          return name && name.trim() !== '' && name.toLowerCase() !== 'standard' && name.toLowerCase() !== 'default';
                      });
                  }
              } catch(e) {}
          }

          if (validSizes && validSizes.length > 0) {
              if (sizeSection) sizeSection.style.display = 'block';
              if (sizeSwatches) {
                  sizeSwatches.innerHTML = '';
                  if (selectedSizeInput) selectedSizeInput.disabled = false;
                  
                  let availableCount = 0;
                  let lastAvailableBtn = null;
                  let lastAvailableSize = '';
                  let lastMaxStk = null;

                  validSizes.forEach((sizeObj, index) => {
                      const btn = document.createElement('button');
                      btn.type = 'button';
                      
                      const sName = (typeof sizeObj === 'object') ? sizeObj.size : sizeObj;
                      const sStock = (typeof sizeObj === 'object') ? sizeObj.stock : null;
                      
                      if (sStock !== null && sStock <= 0) {
                          btn.className = 'size-btn disabled';
                          btn.disabled = true;
                          btn.innerText = sName + ' (<?= __('out_of_stock') ?>)';
                      } else {
                          const maxStk = (sStock !== null) ? sStock : <?= (int)$p['stock'] ?>;
                          btn.className = 'size-btn';
                          btn.onclick = function() { selectSize(this, sName, maxStk); };
                          btn.innerText = sName;
                          availableCount++;
                          lastAvailableBtn = btn;
                          lastAvailableSize = sName;
                          lastMaxStk = maxStk;
                      }
                      sizeSwatches.appendChild(btn);
                  });

                  if (availableCount === 1 && lastAvailableBtn) {
                      selectSize(lastAvailableBtn, lastAvailableSize, lastMaxStk);
                  }
              }
          } else {
              if (sizeSection) sizeSection.style.display = 'none';
              if (sizeSwatches) sizeSwatches.innerHTML = '';
              if (selectedSizeInput) {
                  selectedSizeInput.value = '';
                  selectedSizeInput.disabled = true;
              }
          }
      }
      
      document.addEventListener("DOMContentLoaded", function() {
          restoreBaseGallery();
      });
      </script>
      <style>
      .color-btn {
          background: #f8fafc;
          color: #334155;
          border: 1.5px solid #cbd5e1;
          padding: 8px 22px;
          border-radius: 25px;
          font-size: 0.85rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s ease;
      }
      .color-btn:hover {
          border-color: #3b82f6;
          background: #f1f5f9;
          color: #0f172a;
      }
      .color-btn.active, .size-btn.active {
          background: #3b82f6 !important;
          color: #ffffff !important;
          border-color: #3b82f6 !important;
          font-weight: 700 !important;
          box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
      }
      .size-btn {
          background: #f8fafc;
          color: #334155;
          border: 1px solid #e2e8f0;
          padding: 8px 18px;
          border-radius: 12px;
          font-size: 0.85rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s;
      }
      .size-btn:hover {
          border-color: #cbd5e1;
          background: #f1f5f9;
          color: #0f172a;
      }
      .size-btn.disabled {
          opacity: 0.5;
          cursor: not-allowed;
          border-style: dashed;
      }
      .highlight-required {
          animation: pulseHighlight 0.5s ease 3;
          border-radius: 12px;
          padding: 8px;
          background: rgba(239, 68, 68, 0.08);
      }
      @keyframes pulseHighlight {
          0% { outline: 2px solid #3b82f6; box-shadow: 0 0 10px #3b82f6; }
          50% { outline: 2px solid #ef4444; box-shadow: 0 0 15px #ef4444; }
          100% { outline: 2px solid #3b82f6; box-shadow: 0 0 10px #3b82f6; }
      }
      /* Dark mode support for product details */
      [data-theme="dark"] .product-detail-img {
          background: #1e293b !important;
          border-color: #334155 !important;
      }
      [data-theme="dark"] h1.notranslate {
          color: #f8fafc !important;
      }
      [data-theme="dark"] .color-btn,
      [data-theme="dark"] .size-btn {
          background: #1e293b;
          color: #cbd5e1;
          border-color: #334155;
      }
      [data-theme="dark"] .color-btn:hover,
      [data-theme="dark"] .size-btn:hover {
          background: #334155;
          color: #ffffff;
          border-color: #3b82f6;
      }
      [data-theme="dark"] .color-btn.active,
      [data-theme="dark"] .size-btn.active {
          background: #3b82f6 !important;
          color: #ffffff !important;
          border-color: #3b82f6 !important;
      }
      .qty-input-group {
          background: #f1f5f9;
          border: 1px solid #cbd5e1;
          border-radius: 50px;
          height: 48px;
          width: 120px;
          min-width: 120px;
          display: inline-flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 14px;
          flex-shrink: 0;
          transition: background 0.2s, border-color 0.2s;
      }
      .qty-input-btn {
          color: #334155;
          font-weight: 700;
          font-size: 1.25rem;
          cursor: pointer;
          background: transparent;
          border: none;
          padding: 0 4px;
          line-height: 1;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          transition: color 0.15s, transform 0.1s;
      }
      .qty-input-btn:hover {
          color: #3b82f6;
          transform: scale(1.15);
      }
      .qty-input-val {
          width: 36px;
          color: #0f172a;
          outline: none;
          font-weight: 700;
          font-size: 1.05rem;
          text-align: center;
          background: transparent;
          border: none;
          padding: 0;
      }

      /* Dark mode support for product details */
      [data-theme="dark"] .qty-input-group {
          background: #1e293b !important;
          border: 1px solid #475569 !important;
      }
      [data-theme="dark"] .qty-input-btn {
          color: #ffffff !important;
      }
      [data-theme="dark"] .qty-input-btn:hover {
          color: #60a5fa !important;
      }
      [data-theme="dark"] .qty-input-val {
          color: #ffffff !important;
      }
      [data-theme="dark"] label.form-label-custom {
          color: #94a3b8 !important;
      }
      [data-theme="dark"] .product-tab-btn {
          color: #94a3b8 !important;
      }
      [data-theme="dark"] .product-tab-btn.active {
          background: #1e293b !important;
          color: #ffffff !important;
      }

      .btn-product-action {
          border: none !important;
          border-radius: 50px !important;
          font-weight: 700 !important;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          transition: transform 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
          cursor: pointer;
          height: 48px;
          font-size: 0.88rem !important;
      }
      .btn-product-action:hover {
          transform: translateY(-2px);
          filter: brightness(1.06);
      }
      .btn-product-action:active {
          transform: translateY(0);
          filter: brightness(0.95);
      }

      .product-actions-grid {
          display: flex;
          flex-direction: column;
          gap: 12px;
          width: 100%;
      }
      .product-actions-top-row {
          display: flex;
          align-items: center;
          gap: 12px;
          width: 100%;
      }
      .product-actions-top-row .btn-product-action {
          flex: 1 1 0;
          min-width: 0;
          white-space: nowrap;
      }

      @media (max-width: 576px) {
          .product-actions-top-row {
              flex-wrap: wrap;
          }
          .product-actions-top-row .qty-input-group {
              width: 100% !important;
              justify-content: center !important;
              gap: 30px;
          }
          .product-actions-top-row .btn-product-action {
              flex: 1 1 calc(50% - 6px);
              min-width: 130px;
          }
      }
      </style>

      <script>
      function selectSize(element, sizeName, maxStock) {
          const isCurrentlyActive = element.classList.contains('active');
          
          if (isCurrentlyActive) {
              // Click again -> Toggle OFF / Deactivate
              element.classList.remove('active');
              document.getElementById('sizeLabelText').innerText = "<?= __('select_a_size') ?>";
              document.getElementById('selectedSizeInput').value = '';
              const qtyInput = document.querySelector('input[name="quantity"]');
              if (qtyInput) {
                  qtyInput.setAttribute('max', <?= (int)$p['stock'] ?>);
              }
              return;
          }
          
          // Toggle ON / Activate
          document.querySelectorAll('#sizeSwatches .size-btn').forEach(el => el.classList.remove('active'));
          element.classList.add('active');
          document.getElementById('sizeLabelText').innerText = sizeName;
          document.getElementById('selectedSizeInput').value = sizeName;
          
          if (maxStock !== undefined && maxStock !== null) {
              const qtyInput = document.querySelector('input[name="quantity"]');
              if (qtyInput) {
                  qtyInput.setAttribute('max', maxStock);
                  if (parseInt(qtyInput.value) > maxStock) {
                      qtyInput.value = Math.max(1, maxStock);
                  }
              }
          }
      }

      function validateProductSelection() {
          const colorInput = document.getElementById('selectedColorInput');
          const sizeInput = document.getElementById('selectedSizeInput');
          
          const colorSection = document.getElementById('colorSection');
          const colorSwatches = document.getElementById('colorSwatches');
          if (colorSection && colorSection.style.display !== 'none' && colorSwatches && colorSwatches.children.length > 0 && colorInput) {
              const val = (colorInput.value || '').trim();
              if (!val || val === 'Default Image' || val.toLowerCase() === 'default') {
                  colorSection.classList.add('highlight-required');
                  setTimeout(() => colorSection.classList.remove('highlight-required'), 2500);
                  colorSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  const msgColor = <?= json_encode(__('please_select_color')) ?>;
                  if (typeof customAlert === 'function') {
                      customAlert(msgColor);
                  } else if (typeof showToast === 'function') {
                      showToast(msgColor, true);
                  } else {
                      alert(msgColor);
                  }
                  return false;
              }
          }
      
          const sizeSection = document.getElementById('sizeSection');
          const sizeSwatches = document.getElementById('sizeSwatches');
          const sizeButtons = sizeSwatches ? sizeSwatches.querySelectorAll('.size-btn') : [];
          if (sizeSection && sizeSection.style.display !== 'none' && sizeButtons.length > 0 && sizeInput && !sizeInput.disabled) {
              const val = (sizeInput.value || '').trim();
              if (!val) {
                  sizeSection.classList.add('highlight-required');
                  setTimeout(() => sizeSection.classList.remove('highlight-required'), 2500);
                  sizeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  const msgSize = <?= json_encode(__('please_select_size')) ?>;
                  if (typeof customAlert === 'function') {
                      customAlert(msgSize);
                  } else if (typeof showToast === 'function') {
                      showToast(msgSize, true);
                  } else {
                      alert(msgSize);
                  }
                  return false;
              }
          }
          return true;
      }
      </script>
      <form action="core/actions.php" method="POST" onsubmit="return validateProductSelection();">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          
          <?php if(count($colors) > 0 || count($parsed_colors) > 0): ?>
              <input type="hidden" name="color" id="selectedColorInput" value="">
          <?php endif; ?>
          <input type="hidden" name="selected_image" id="selectedImageInput" value="<?= htmlspecialchars($p['image'] ?? '') ?>">
          
          <?php 
          $has_valid_initial_sizes = false;
          if (!empty($initial_sizes)) {
              foreach ($initial_sizes as $is) {
                  $isName = is_array($is) ? ($is['size'] ?? '') : $is;
                  if (trim($isName) !== '' && strtolower(trim($isName)) !== 'standard' && strtolower(trim($isName)) !== 'default') {
                      $has_valid_initial_sizes = true;
                      break;
                  }
              }
          }
          ?>
          <input type="hidden" name="size" id="selectedSizeInput" value="" <?= $has_valid_initial_sizes ? '' : 'disabled' ?>>
          
          <style>
          input[type=number]::-webkit-inner-spin-button, 
          input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
          </style>
          
          <?php
          global $store_order_status, $store_order_message;
          $btn_disabled = ($p['stock'] <= 0 || $store_order_status === 'disable') ? 'disabled' : '';
          if ($store_order_status === 'disable') {
              $btn_text = htmlspecialchars($store_order_message);
              $buy_now_text = __('disabled');
          } else {
              $btn_text = __('add_to_cart');
              $buy_now_text = __('buy_now');
          }
          
          $wa_phone = function_exists('get_setting') ? get_setting('support_phone', '0785242513') : '0785242513';
          $wa_clean = preg_replace('/[^0-9]/', '', $wa_phone);
          if (substr($wa_clean, 0, 3) !== '250' && substr($wa_clean, 0, 1) === '0') {
              $wa_clean = '250' . substr($wa_clean, 1);
          }
          ?>
          
          <!-- Actions Container -->
          <div class="product-actions-wrapper mb-4">
            <div class="product-actions-grid">
              <!-- Top Row: Quantity + Add to Cart + Buy Now -->
              <div class="product-actions-top-row">
                <!-- Quantity Counter -->
                <div class="qty-input-group">
                  <button type="button" class="qty-input-btn" onclick="this.nextElementSibling.value=Math.max(1,parseInt(this.nextElementSibling.value)-1)">-</button>
                  <?php $default_max_stock = isset($initial_max_stock) ? $initial_max_stock : $p['stock']; ?>
                  <input type="number" name="quantity" value="1" min="1" max="<?= $default_max_stock ?>" class="qty-input-val" readonly>
                  <button type="button" class="qty-input-btn" onclick="this.previousElementSibling.value=Math.min(parseInt(this.previousElementSibling.getAttribute('max'))||99, parseInt(this.previousElementSibling.value)+1)">+</button>
                </div>

                <!-- 1. Add to Cart Button -->
                <button type="submit" class="btn-product-action d-flex align-items-center justify-content-center gap-2 notranslate" style="background: #3b82f6 !important; color: #ffffff !important; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);" <?= $btn_disabled ?> onclick="this.form.querySelector('input[name=\'action\']').value='add_to_cart';">
                    <i class="fas fa-shopping-cart"></i> <?= $btn_text ?>
                </button>
                
                <!-- 2. Buy Now Button -->
                <button type="submit" class="btn-product-action d-flex align-items-center justify-content-center gap-2" style="background: #f59e0b !important; color: #ffffff !important; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);" <?= $btn_disabled ?> onclick="this.form.querySelector('input[name=\'action\']').value='buy_now';">
                    <i class="fas fa-bolt"></i> <?= $buy_now_text ?>
                </button>
              </div>

              <!-- Bottom Row: Ask via WhatsApp Button -->
              <a href="https://wa.me/<?= $wa_clean ?>?text=Hello,%20I%20am%20interested%20in%20buying%20<?= urlencode($p['name']) ?>%20(<?= $p['id'] ?>)" target="_blank" class="btn-product-action w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none" style="background: #22c55e !important; color: #ffffff !important; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);">
                  <i class="fab fa-whatsapp" style="font-size: 1.15rem;"></i> <?= __('ask_whatsapp') ?>
              </a>
            </div>
          </div>
      </form>

      <?php
      $is_rw = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'rw') || (isset($lang) && $lang === 'rw');

      $perk_delivery = get_setting($is_rw ? 'perk_delivery_rw' : 'perk_delivery_en', '');
      if (empty($perk_delivery)) {
          $perk_delivery = $is_rw ? get_setting('perk_delivery_en', '') : '';
          if (empty($perk_delivery)) $perk_delivery = __('fast_delivery_perk');
      }

      $perk_quality = get_setting($is_rw ? 'perk_quality_rw' : 'perk_quality_en', '');
      if (empty($perk_quality)) {
          $perk_quality = $is_rw ? get_setting('perk_quality_en', '') : '';
          if (empty($perk_quality)) $perk_quality = __('authentic_perk');
      }

      $perk_return = get_setting($is_rw ? 'perk_return_rw' : 'perk_return_en', '');
      if (empty($perk_return)) {
          $perk_return = $is_rw ? get_setting('perk_return_en', '') : '';
          if (empty($perk_return)) $perk_return = __('return_perk');
      }

      $perk_payment = get_setting($is_rw ? 'perk_payment_rw' : 'perk_payment_en', '');
      if (empty($perk_payment)) {
          $perk_payment = $is_rw ? get_setting('perk_payment_en', '') : '';
          if (empty($perk_payment)) $perk_payment = __('payment_perk');
      }
      ?>
      <!-- Trust & Delivery Perks Card -->
      <div class="product-perks-box mt-3 p-3 rounded-3" style="background: var(--bg3); border: 1px solid var(--border); border-radius: 12px;">
        <div class="row g-2">
          <div class="col-6">
            <div class="d-flex align-items-center gap-2">
              <div class="perk-icon-circle" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                <i class="fas fa-shipping-fast"></i>
              </div>
              <span class="small fw-semibold" style="font-size: 0.78rem; color: var(--text); line-height: 1.2;"><?= htmlspecialchars($perk_delivery) ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center gap-2">
              <div class="perk-icon-circle" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                <i class="fas fa-shield-alt"></i>
              </div>
              <span class="small fw-semibold" style="font-size: 0.78rem; color: var(--text); line-height: 1.2;"><?= htmlspecialchars($perk_quality) ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center gap-2">
              <div class="perk-icon-circle" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                <i class="fas fa-undo-alt"></i>
              </div>
              <span class="small fw-semibold" style="font-size: 0.78rem; color: var(--text); line-height: 1.2;"><?= htmlspecialchars($perk_return) ?></span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center gap-2">
              <div class="perk-icon-circle" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                <i class="fas fa-credit-card"></i>
              </div>
              <span class="small fw-semibold" style="font-size: 0.78rem; color: var(--text); line-height: 1.2;"><?= htmlspecialchars($perk_payment) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs Section -->
  <div class="mt-4" id="reviews-section">
      <?php
      $rev_count_query = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM product_reviews WHERE product_id = $id");
      $rev_count_data = mysqli_fetch_assoc($rev_count_query);
      $rev_count = $rev_count_data['cnt'] ?? 0;
      ?>
      <div class="d-inline-flex p-1 rounded-3 mb-3" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px;">
          <button class="btn product-tab-btn active px-4 py-2 border-0" onclick="switchTab('description')" id="tab-btn-description" style="font-weight: 600; font-size: 0.88rem; color: #1e293b; border-radius: 8px; background: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.06);"><?= __('description') ?></button>
          <button class="btn product-tab-btn px-4 py-2 border-0" onclick="switchTab('reviews')" id="tab-btn-reviews" style="font-weight: 500; font-size: 0.88rem; color: #64748b; background: transparent; border-radius: 8px;"><?= __('reviews') ?> (<?= $rev_count ?>)</button>
      </div>

      <div class="product-tab-content border p-4" style="background: var(--card); border-color: var(--border) !important; border-radius: 12px;">
          <!-- Description Tab -->
          <div id="tab-description">
              <div class="text-muted-custom mb-4" style="line-height:1.7"><?= html_entity_decode($p['description']) ?></div>
              <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
                  <div class="mb-4 text-center">
                      <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?> description" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
                  </div>
              <?php endif; ?>
          </div>

          <!-- Reviews Tab -->
          <div id="tab-reviews" style="display: none;">
              <div class="row g-4">
                  <div class="col-md-7">
                      <?php 
                      $reviews = false;
                      try {
                          $reviews = mysqli_query($conn, "SELECT r.*, u.first_name, u.last_name FROM product_reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $id ORDER BY r.created_at DESC");
                      } catch (Throwable $e) {}
                      if($reviews && mysqli_num_rows($reviews) > 0):
                          while($rev = mysqli_fetch_assoc($reviews)):
                      ?>
                          <div class="review-item mb-4 pb-4 border-bottom">
                              <div class="d-flex align-items-center mb-2">
                                  <div class="text-warning me-2">
                                      <?php
                                      for($i=1; $i<=5; $i++) {
                                          echo $i <= $rev['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                      }
                                      ?>
                                  </div>
                                  <span class="fw-bold"><?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']) ?></span>
                                  <span class="text-muted ms-2" style="font-size: 0.85rem;"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                              </div>
                              <p class="mb-0 text-muted-custom"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                          </div>
                      <?php 
                          endwhile;
                      else:
                      ?>
                          <p class="text-muted"><?= __('no_reviews_yet') ?></p>
                      <?php endif; ?>
                  </div>
                  <div class="col-md-5">
                      <div class="p-4" style="background: var(--bg2); border-radius: 12px;">
                          <h4 class="mb-3" style="font-size: 1.2rem;"><?= __('leave_review') ?></h4>
                          <?php if(isset($_SESSION['user_id'])): ?>
                              <form action="core/actions.php" method="POST">
                                  <input type="hidden" name="action" value="submit_review">
                                  <input type="hidden" name="product_id" value="<?= $id ?>">
                                  <div class="mb-3">
                                      <label class="form-label text-muted-custom"><?= __('rating') ?></label>
                                      <div class="star-rating-input d-flex align-items-center mb-2" style="font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">
                                          <i class="far fa-star me-1" data-rating="1"></i>
                                          <i class="far fa-star me-1" data-rating="2"></i>
                                          <i class="far fa-star me-1" data-rating="3"></i>
                                          <i class="far fa-star me-1" data-rating="4"></i>
                                          <i class="far fa-star me-1" data-rating="5"></i>
                                      </div>
                                      <input type="hidden" name="rating" id="ratingInput" required>
                                  </div>
                                  <script>
                                  document.addEventListener('DOMContentLoaded', function() {
                                      const stars = document.querySelectorAll('.star-rating-input i');
                                      const ratingInput = document.getElementById('ratingInput');
                                      
                                      stars.forEach(star => {
                                          star.addEventListener('click', function() {
                                              let rating = this.getAttribute('data-rating');
                                              ratingInput.value = rating;
                                              updateStars(rating);
                                          });
                                          
                                          star.addEventListener('mouseenter', function() {
                                              let rating = this.getAttribute('data-rating');
                                              updateStars(rating, true);
                                          });
                                          
                                          star.addEventListener('mouseleave', function() {
                                              updateStars(ratingInput.value || 0);
                                          });
                                      });

                                      function updateStars(rating, isHover = false) {
                                          stars.forEach(s => {
                                              let sRating = parseInt(s.getAttribute('data-rating'));
                                              if (sRating <= rating) {
                                                  s.classList.remove('far', 'text-muted');
                                                  s.classList.add('fas', 'text-warning');
                                              } else {
                                                  s.classList.remove('fas', 'text-warning');
                                                  s.classList.add('far');
                                              }
                                          });
                                      }
                                  });
                                  </script>
                                  <div class="mb-3">
                                      <label class="form-label text-muted-custom"><?= __('comment_optional') ?></label>
                                      <textarea name="comment" rows="3" class="form-control" style="background: var(--bg3); border: 1px solid var(--border); color: var(--text);"></textarea>
                                  </div>
                                  <button type="submit" class="btn btn-primary w-100" style="background: var(--accent); border: none;"><?= __('submit_review') ?></button>
                              </form>
                          <?php else: ?>
                              <div class="p-3 text-center rounded-3 mb-3" style="background: var(--bg3); border: 1px solid var(--border);">
                                  <p class="text-muted-custom mb-2"><?= __('login_to_review') ?></p>
                                  <button type="button" onclick="openAuthModal()" class="btn btn-sm btn-primary px-4 fw-bold" style="background: var(--accent); border: none; border-radius: 6px;"><?= __('sign_in') ?></button>
                              </div>
                          <?php endif; ?>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <script>
   function switchTab(tabId) {
       document.getElementById('tab-description').style.display = 'none';
       document.getElementById('tab-reviews').style.display = 'none';
       
       const btnDesc = document.getElementById('tab-btn-description');
       if (btnDesc) {
           btnDesc.classList.remove('active', 'bg-white', 'shadow-sm');
           btnDesc.style.color = 'var(--text3)';
           btnDesc.style.background = 'transparent';
       }
       
       const btnRev = document.getElementById('tab-btn-reviews');
       if (btnRev) {
           btnRev.classList.remove('active', 'bg-white', 'shadow-sm');
           btnRev.style.color = 'var(--text3)';
           btnRev.style.background = 'transparent';
       }
       
       const targetTab = document.getElementById('tab-' + tabId);
       if (targetTab) targetTab.style.display = 'block';
       
       const activeBtn = document.getElementById('tab-btn-' + tabId);
       if (activeBtn) {
           activeBtn.classList.add('active', 'bg-white', 'shadow-sm');
           activeBtn.style.color = 'var(--text)';
           activeBtn.style.background = 'white';
       }
   }
   
   document.addEventListener("DOMContentLoaded", function() {
       if(window.location.hash === '#reviews-section' || window.location.hash === '#reviews') {
           switchTab('reviews');
       }
   });
   </script>

  <!-- Related Products Section -->
  <?php
  $cat_id = (int)($p['category_id'] ?? 0);
  if ($cat_id > 0):
      $related_query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = $cat_id AND p.id != $id LIMIT 4";
      $related_result = mysqli_query($conn, $related_query);
      if ($related_result && mysqli_num_rows($related_result) > 0):
=======
      <div class="product-detail-img" id="imgZoomContainer" style="overflow:hidden; display:flex; align-items:center; justify-content:center; cursor: zoom-in; position: relative; border-radius: 12px;">
        <?php if(isset($p['image']) && $p['image'] && file_exists('uploads/' . $p['image'])): ?>
            <img id="mainProductImage" src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%; height:100%; object-fit:cover; transition: transform 0.1s ease-out;">
        <?php else: ?>
            <i id="mainProductImageFallback" class="fas fa-box text-muted"></i>
            <img id="mainProductImage" src="" style="width:100%; height:100%; object-fit:cover; display:none; transition: transform 0.1s ease-out;">
        <?php endif; ?>
      </div>

      <?php if(count($colors) > 0 || (isset($p['image']) && $p['image'])): ?>
      <div class="mt-3">
          <label class="form-label-custom mb-2">Color: <span id="colorLabelText" class="text-white fw-normal">Default</span></label>
          <div class="d-flex gap-2 flex-wrap" id="colorSwatches">
              <?php if(isset($p['image']) && $p['image']): ?>
              <div class="thumb-img border-accent active" onclick="changeProductImage('uploads/<?= htmlspecialchars($p['image']) ?>', this, 'Default')" style="width:60px; height:60px; background-image:url('uploads/<?= htmlspecialchars($p['image']) ?>'); background-size:cover; border-radius:4px;"></div>
              <?php endif; ?>
              
              <?php foreach($colors as $color): ?>
              <div class="thumb-img" onclick="changeProductImage('uploads/<?= htmlspecialchars($color['image_path']) ?>', this, '<?= htmlspecialchars($color['color_name'], ENT_QUOTES) ?>')" style="width:60px; height:60px; background-image:url('uploads/<?= htmlspecialchars($color['image_path']) ?>'); background-size:cover; border-radius:4px;" title="<?= htmlspecialchars($color['color_name']) ?>"></div>
              <?php endforeach; ?>
          </div>
      </div>
      <script>
      function changeProductImage(src, element, colorName) {
          const img = document.getElementById('mainProductImage');
          const fallback = document.getElementById('mainProductImageFallback');
          if (img) {
              img.src = src;
              img.style.display = 'block';
              if (fallback) fallback.style.display = 'none';
          }
          
          document.querySelectorAll('#colorSwatches .thumb-img').forEach(el => el.classList.remove('border-accent', 'active'));
          element.classList.add('border-accent', 'active');
          document.getElementById('colorLabelText').innerText = colorName;
          document.getElementById('selectedColorInput').value = colorName === 'Default' ? '' : colorName;
      }
      </script>
      <style>
      .thumb-img.border-accent { border-color: var(--accent) !important; border-width: 2px; }
      </style>
      <?php endif; ?>
      
      <script>
      // Image Zoom Logic
      const zoomContainer = document.getElementById('imgZoomContainer');
      const zoomImg = document.getElementById('mainProductImage');
      if (zoomContainer && zoomImg) {
          zoomContainer.addEventListener('mousemove', (e) => {
              const { left, top, width, height } = zoomContainer.getBoundingClientRect();
              const x = ((e.clientX - left) / width) * 100;
              const y = ((e.clientY - top) / height) * 100;
              zoomImg.style.transformOrigin = `${x}% ${y}%`;
              zoomImg.style.transform = 'scale(2)';
          });
          zoomContainer.addEventListener('mouseleave', () => {
              zoomImg.style.transformOrigin = 'center center';
              zoomImg.style.transform = 'scale(1)';
          });
      }
      </script>
    </div>
    <div class="col-md-6">
      <div class="product-category mb-2"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></div>
      <h1 class="detail-title mb-2"><?= htmlspecialchars($p['name']) ?></h1>
      <div class="detail-price mb-4"><?= number_format($p['price'], 0) ?> RFW</div>
      <p class="text-muted-custom mb-4" style="line-height:1.7"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
      
      <div class="mb-4">
        <span class="text-sm font-weight-bold">Availability:</span>
        <?php if($p['stock'] > 0): ?>
            <span class="text-success"><i class="fas fa-check-circle me-1"></i> In Stock (<?= $p['stock'] ?> available)</span>
        <?php else: ?>
            <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
        <?php endif; ?>
      </div>
      
      <div class="mb-4 d-flex flex-column gap-2">
          <div class="text-muted" style="font-size: 0.9rem;">
              <i class="fas fa-truck text-primary me-2"></i> <strong>Shipping:</strong> Home Delivery (2,000 RFW) or Fast Delivery (5,000 RFW)
          </div>
          <div class="text-muted" style="font-size: 0.9rem;">
              <i class="fas fa-shield-alt text-primary me-2"></i> <strong>Secure Payment:</strong> MTN Mobile Money or Cash on Delivery
          </div>
      </div>

      <form action="core/actions.php" method="POST">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="color" id="selectedColorInput" value="">
          <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="qty-input-group d-flex justify-content-between px-2" style="width: 120px; background: var(--bg3); border-radius: 30px;">
              <button type="button" class="qty-input-btn border-0 bg-transparent" onclick="this.nextElementSibling.value=Math.max(1,parseInt(this.nextElementSibling.value)-1)">-</button>
              <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock'] ?>" class="qty-input-val bg-transparent border-0 text-center" style="width: 50px; color:var(--text1) !important; outline: none; font-weight: bold; -moz-appearance: textfield;" readonly>
              <button type="button" class="qty-input-btn border-0 bg-transparent" onclick="this.previousElementSibling.value=Math.min(parseInt(this.previousElementSibling.getAttribute('max'))||99, parseInt(this.previousElementSibling.value)+1)">+</button>
            </div>
            <style>
            input[type=number]::-webkit-inner-spin-button, 
            input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
            </style>
            <button type="submit" class="btn-add-cart-lg px-5" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
          </div>
      </form>
    </div>
  </div>

  <!-- Related Products Section -->
  <?php
  $cat_id = $p['category_id'];
  $related_query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = $cat_id AND p.id != $id LIMIT 4";
  $related_result = mysqli_query($conn, $related_query);
  if (mysqli_num_rows($related_result) > 0):
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
  ?>
  <div class="mt-5 pt-5 border-top">
      <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
<<<<<<< HEAD
              <div class="section-eyebrow"><?= __('discover_more') ?></div>
              <h2 class="section-title mb-0"><?= __('related_products') ?></h2>
=======
              <div class="section-eyebrow">Discover More</div>
              <h2 class="section-title mb-0">Related Products</h2>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
          </div>
      </div>
      <div class="row g-3 g-md-4">
          <?php while($rp = mysqli_fetch_assoc($related_result)) { render_product_card($rp); } ?>
      </div>
  </div>
<<<<<<< HEAD
  <?php endif; endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
=======
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
