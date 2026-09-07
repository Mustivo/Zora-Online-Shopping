<?php
require_once dirname(__DIR__) . '../core/config.php';
require_once '../core/config.php';
$page_query = mysqli_query($conn, "SELECT * FROM pages WHERE slug = 'about' LIMIT 1");
$custom_page = ($page_query && mysqli_num_rows($page_query) > 0) ? mysqli_fetch_assoc($page_query) : null;

$page_title = (!empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : "About Us") . " - Zora Shop Rwanda";
$meta_desc = "Learn more about Zora Shop Rwanda. We are dedicated to bringing you the best fashion, clothes, and accessories with top-notch customer service in Kigali.";
$meta_keywords = "About Zora Shop, Fashion store Kigali, Clothing shop Rwanda";
// require_once 'includes/header.php';
require_once dirname(__DIR__) . '/includes/header.php';


$banner_bg = (!empty($custom_page['banner_image']) && file_exists('uploads/' . $custom_page['banner_image'])) ? 'uploads/' . htmlspecialchars($custom_page['banner_image']) : 'uploads/about_hero.png';
$eyebrow = !empty($custom_page['subtitle']) ? htmlspecialchars($custom_page['subtitle']) : __('our_story');
$heading = !empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : __('about_zora') . ' <span style="color: var(--accent);">Zora Shop</span>';
$has_custom_body = !empty($custom_page['content']) && trim($custom_page['content']) !== '';
?>

<style>
.feature-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    padding: 2rem;
    border-radius: 20px;
    height: 100%;
    transition: all 0.3s ease;
}
.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.05);
    border-color: var(--primary);
}
.icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem auto;
    background: rgba(1, 42, 94, 0.05);
}
[data-theme="dark"] .icon-wrapper { background: rgba(255,255,255,0.05); }

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

<section class="hero d-flex align-items-center justify-content-center text-center" style="min-height: 50vh; background: url('<?= $banner_bg ?>') center/cover no-repeat; position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 42, 94, 0.85) 0%, rgba(1, 42, 94, 0.6) 60%, rgba(251, 124, 0, 0.4) 100%); z-index: 1;"></div>
  <div class="hero-grid-lines" style="z-index: 1; opacity: 0.2;"></div>
  <div class="container position-relative" style="z-index: 2; display: flex; flex-direction: column; align-items: center;">
    <div style="animation:fadeUp 0.8s ease; max-width: 800px;">
      <div class="section-eyebrow mb-3" style="color: var(--accent); letter-spacing: 4px; font-weight: 800;"><?= $eyebrow ?></div>
      <h1 class="hero-title mb-4 text-white" style="font-family: 'Playfair Display', serif; font-size: 4rem; line-height: 1.1; font-weight: 900; letter-spacing: -1px; text-shadow: 2px 4px 10px rgba(0,0,0,0.3);"><?= $heading ?></h1>
      <p class="text-white mb-0" style="font-size: 1.2rem; opacity: 0.95; line-height: 1.8; text-shadow: 1px 2px 5px rgba(0,0,0,0.3);">
        <?= __('about_desc') ?>
      </p>
    </div>
  </div>
</section>

<section class="py-5" style="background: var(--bg);">
  <div class="container py-5">
    <?php if ($has_custom_body): ?>
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="admin-card p-4 p-md-5" style="border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border);">
            <div class="page-custom-content">
              <?= $custom_page['content'] ?>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="row justify-content-center mb-5 text-center">
          <div class="col-lg-8">
              <h2 class="fw-bold mb-4" style="color: var(--primary); font-family: 'Playfair Display', serif;"><?= __('redefining_shopping') ?></h2>
              <p style="color: var(--text); line-height: 1.8; font-size: 1.1rem;"><?= __('founded_vision') ?></p>
          </div>
      </div>
      
      <div class="row text-center mt-5 mb-5">
          <div class="col-12 mb-5">
              <h2 class="fw-bold" style="color: var(--primary); font-family: 'Playfair Display', serif;"><?= __('why_choose_us') ?></h2>
              <div style="width: 60px; height: 4px; background: var(--accent); margin: 15px auto; border-radius: 2px;"></div>
          </div>
          <div class="col-md-4 mb-4">
              <div class="feature-card">
                  <div class="icon-wrapper"><i class="fas fa-truck fa-2x" style="color: var(--accent);"></i></div>
                  <h4 class="fw-bold mb-3" style="color: var(--primary);"><?= __('fast_delivery') ?></h4>
                  <p style="color: var(--text3); font-size: 0.95rem;"><?= __('fast_delivery_desc') ?></p>
              </div>
          </div>
          <div class="col-md-4 mb-4">
              <div class="feature-card">
                  <div class="icon-wrapper"><i class="fas fa-shield-alt fa-2x" style="color: var(--primary);"></i></div>
                  <h4 class="fw-bold mb-3" style="color: var(--primary);"><?= __('secure_payments') ?></h4>
                  <p style="color: var(--text3); font-size: 0.95rem;"><?= __('secure_payments_desc') ?></p>
              </div>
          </div>
          <div class="col-md-4 mb-4">
              <div class="feature-card">
                  <div class="icon-wrapper"><i class="fas fa-headset fa-2x" style="color: var(--accent);"></i></div>
                  <h4 class="fw-bold mb-3" style="color: var(--primary);"><?= __('24_7_support') ?></h4>
                  <p style="color: var(--text3); font-size: 0.95rem;"><?= __('support_desc') ?></p>
              </div>
          </div>
      </div>

      <!-- Logistics Section -->
      <div class="row justify-content-center mt-5 pt-5 border-top text-center" style="border-color: var(--border) !important;">
          <div class="col-lg-8">
              <h2 class="fw-bold mb-4" style="color: var(--primary); font-family: 'Playfair Display', serif;"><?= __('powered_by_tech') ?></h2>
              <p style="color: var(--text); line-height: 1.8;"><?= __('tech_desc1') ?></p>
              <p style="color: var(--text); line-height: 1.8;"><?= __('tech_desc2') ?></p>
              <div class="mt-4">
                  <a href="shop.php" class="btn px-5 py-3 fw-bold text-white shadow me-3" style="background: var(--primary); border: none; border-radius: 50px;"><?= __('start_shopping') ?> <i class="fas fa-arrow-right ms-2"></i></a>
                  <a href="contact.php" class="btn btn-outline-secondary px-5 py-3 fw-bold" style="border-radius: 50px; color: var(--text); border-color: var(--border);"><?= __('get_in_touch') ?></a>
              </div>
          </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once '../includes/footer.php'; ?>
