<?php
require_once 'core/config.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<div class='container py-5 text-center'><h2>Product not found.</h2><a href='shop.php' class='btn-hero'>Back to Shop</a></div>";
    require_once 'footer.php';
    exit;
}

$p = mysqli_fetch_assoc($result);

$colors_query = mysqli_query($conn, "SELECT id, color_name, image_path FROM product_images WHERE product_id = $id ORDER BY created_at ASC");
$colors = [];
while ($col = mysqli_fetch_assoc($colors_query)) {
    $colors[] = $col;
}

$wishlist_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_res = mysqli_query($conn, "SELECT product_id FROM wishlist WHERE user_id = $uid");
    while ($w_row = mysqli_fetch_assoc($w_res)) {
        $wishlist_product_ids[] = $w_row['product_id'];
    }
}

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
  <nav style="font-size:0.8rem;color:var(--text3);margin-bottom:1.5rem">
    <a href="index.php" class="text-decoration-none text-muted-custom">Home</a> <span class="mx-2">/</span>
    <a href="shop.php" class="text-decoration-none text-muted-custom">Shop</a> <span class="mx-2">/</span>
    <span class="gold"><?= htmlspecialchars($p['name']) ?></span>
  </nav>

  <div class="row g-4">
    <div class="col-md-6">
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
  ?>
  <div class="mt-5 pt-5 border-top">
      <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
              <div class="section-eyebrow">Discover More</div>
              <h2 class="section-title mb-0">Related Products</h2>
          </div>
      </div>
      <div class="row g-3 g-md-4">
          <?php while($rp = mysqli_fetch_assoc($related_result)) { render_product_card($rp); } ?>
      </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
