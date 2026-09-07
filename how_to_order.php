<?php
require_once 'core/config.php';
$page_query = mysqli_query($conn, "SELECT * FROM pages WHERE slug = 'how_to_order' LIMIT 1");
$custom_page = ($page_query && mysqli_num_rows($page_query) > 0) ? mysqli_fetch_assoc($page_query) : null;

$page_title = (!empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : "How to Order") . " - Zora Shop Rwanda";
require_once 'includes/header.php';

$banner_bg = (!empty($custom_page['banner_image']) && file_exists('uploads/' . $custom_page['banner_image'])) ? 'uploads/' . htmlspecialchars($custom_page['banner_image']) : 'uploads/1779271766_9886.jpeg';
$eyebrow = !empty($custom_page['subtitle']) ? htmlspecialchars($custom_page['subtitle']) : 'Simple & Easy';
$heading = !empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : 'How to <span style="color: var(--accent);">Order</span>';
$has_custom_body = !empty($custom_page['content']) && trim($custom_page['content']) !== '';
?>

<style>
.page-custom-content {
    color: var(--text);
    line-height: 1.85;
    font-size: 1.05rem;
}
.page-custom-content h1, .page-custom-content h2, .page-custom-content h3, .page-custom-content h4 {
    color: var(--primary);
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}
.page-custom-content p {
    margin-bottom: 1.25rem;
}
.page-custom-content img {
    max-width: 100%;
    border-radius: 12px;
    margin: 1.5rem 0;
}
</style>

<section class="hero d-flex align-items-center" style="min-height: 35vh; background: url('<?= $banner_bg ?>') center/cover no-repeat; position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 42, 94, 0.85) 0%, rgba(1, 42, 94, 0.6) 60%, rgba(251, 124, 0, 0.4) 100%); z-index: 1;"></div>
  <div class="hero-grid-lines" style="z-index: 1; opacity: 0.2;"></div>
  <div class="container text-center position-relative" style="z-index: 2;">
    <div style="animation:fadeUp 0.8s ease">
      <div class="section-eyebrow mb-2" style="color: var(--accent); letter-spacing: 4px; font-weight: 800;"><?= $eyebrow ?></div>
      <h1 class="hero-title mb-3 text-white"><?= $heading ?></h1>
      <p class="mx-auto text-white" style="max-width: 600px; font-size: 1.1rem; opacity: 0.95;">
        Follow these simple steps to place your order and get your premium products delivered fast.
      </p>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <?php if ($has_custom_body): ?>
          <div class="admin-card p-4 p-md-5" style="border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div class="page-custom-content">
              <?= $custom_page['content'] ?>
            </div>
            <div class="text-center mt-5">
              <a href="shop.php" class="btn-primary-full px-5 py-3 d-inline-block" style="width: auto;">Start Shopping Now <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
          </div>
        <?php else: ?>
          <div class="d-flex mb-5 align-items-start">
              <div style="width: 60px; height: 60px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 15px rgba(251,124,0,0.4);">1</div>
              <div class="ms-4">
                  <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.5rem; color: var(--primary);">Browse & Select</h3>
                  <p class="text-muted-custom mt-2" style="font-size: 1.05rem; line-height: 1.7;">Explore our wide range of premium categories. Once you find the perfect item, click <strong><?= isset($is_rw) && $is_rw ? 'Ongera Mugitebo' : 'Add to Cart' ?></strong>. You can continue shopping or proceed directly to checkout.</p>
              </div>
          </div>

          <div class="d-flex mb-5 align-items-start">
              <div style="width: 60px; height: 60px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 15px rgba(251,124,0,0.4);">2</div>
              <div class="ms-4">
                  <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.5rem; color: var(--primary);">Review Your Cart</h3>
                  <p class="text-muted-custom mt-2" style="font-size: 1.05rem; line-height: 1.7;">Click on the shopping bag icon at the top right to view your cart. Review your items, adjust quantities if needed, and apply any discount coupons you might have.</p>
              </div>
          </div>

          <div class="d-flex mb-5 align-items-start">
              <div style="width: 60px; height: 60px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 15px rgba(251,124,0,0.4);">3</div>
              <div class="ms-4">
                  <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.5rem; color: var(--primary);">Secure Checkout</h3>
                  <p class="text-muted-custom mt-2" style="font-size: 1.05rem; line-height: 1.7;">Proceed to checkout. Fill in your delivery details and choose your preferred delivery method. We offer both standard and express delivery options.</p>
              </div>
          </div>

          <div class="d-flex mb-5 align-items-start">
              <div style="width: 60px; height: 60px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 15px rgba(251,124,0,0.4);">4</div>
              <div class="ms-4">
                  <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.5rem; color: var(--primary);">Payment & Confirmation</h3>
                  <p class="text-muted-custom mt-2" style="font-size: 1.05rem; line-height: 1.7;">Select a secure payment method (MoMo, Card, or Cash on Delivery) and complete your order. You will receive an immediate order confirmation and a tracking link.</p>
              </div>
          </div>

          <div class="text-center mt-5">
              <a href="shop.php" class="btn-primary-full px-5 py-3 d-inline-block" style="width: auto;">Start Shopping Now <i class="fas fa-arrow-right ms-2"></i></a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
