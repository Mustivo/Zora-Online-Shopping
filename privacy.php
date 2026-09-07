<?php
require_once 'core/config.php';
$page_query = mysqli_query($conn, "SELECT * FROM pages WHERE slug = 'privacy' LIMIT 1");
$custom_page = ($page_query && mysqli_num_rows($page_query) > 0) ? mysqli_fetch_assoc($page_query) : null;

$page_title = (!empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : __('privacy_policy')) . " - Zora Shop Rwanda";
require_once 'includes/header.php';

$heading = !empty($custom_page['title']) ? htmlspecialchars($custom_page['title']) : __('privacy_policy');
$subtitle = !empty($custom_page['subtitle']) ? htmlspecialchars($custom_page['subtitle']) : '';
$has_custom_body = !empty($custom_page['content']) && trim($custom_page['content']) !== '';
$banner_bg = (!empty($custom_page['banner_image']) && file_exists('uploads/' . $custom_page['banner_image'])) ? 'uploads/' . htmlspecialchars($custom_page['banner_image']) : '';
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

<?php if (!empty($banner_bg)): ?>
<section class="hero d-flex align-items-center justify-content-center text-center mb-4" style="min-height: 30vh; background: url('<?= $banner_bg ?>') center/cover no-repeat; position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 42, 94, 0.85) 0%, rgba(1, 42, 94, 0.6) 60%, rgba(251, 124, 0, 0.4) 100%); z-index: 1;"></div>
  <div class="container position-relative" style="z-index: 2;">
    <h1 class="hero-title mb-2 text-white" style="font-family: 'Playfair Display', serif;"><?= $heading ?></h1>
    <?php if (!empty($subtitle)): ?>
      <p class="text-white mb-0" style="opacity: 0.9;"><?= $subtitle ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="admin-card p-4 p-md-5" style="border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border);">
                <?php if (empty($banner_bg)): ?>
                <div class="text-center mb-5">
                    <h1 class="hero-title mb-3" style="font-size: 2.8rem; color: var(--primary); font-family: 'Playfair Display', serif;"><?= $heading ?></h1>
                    <?php if (!empty($subtitle)): ?>
                        <p class="text-accent fw-bold mb-2"><?= $subtitle ?></p>
                    <?php endif; ?>
                    <p class="text-muted-custom"><?= __('last_updated') ?>: <?= date('F d, Y') ?></p>
                </div>
                <?php endif; ?>

                <?php if ($has_custom_body): ?>
                    <div class="page-custom-content">
                        <?= $custom_page['content'] ?>
                    </div>
                <?php else: ?>
                    <div class="content-block" style="font-size: 1.05rem; line-height: 1.8; color: var(--text);">
                        <p><?= __('privacy_intro') ?></p>

                        <h4 style="font-weight: 700; color: var(--primary); margin-top: 2rem; font-family: 'Playfair Display', serif;"><?= __('privacy_sec1_title') ?></h4>
                        <p><?= __('privacy_sec1_desc') ?></p>

                        <h4 style="font-weight: 700; color: var(--primary); margin-top: 2rem; font-family: 'Playfair Display', serif;"><?= __('privacy_sec2_title') ?></h4>
                        <p><?= __('privacy_sec2_desc') ?></p>
                        <ul style="list-style-type: disc; padding-left: 20px;">
                            <li style="margin-bottom: 0.5rem;"><?= __('privacy_sec2_li1') ?></li>
                            <li style="margin-bottom: 0.5rem;"><?= __('privacy_sec2_li2') ?></li>
                            <li style="margin-bottom: 0.5rem;"><?= __('privacy_sec2_li3') ?></li>
                            <li style="margin-bottom: 0.5rem;"><?= __('privacy_sec2_li4') ?></li>
                        </ul>

                        <h4 style="font-weight: 700; color: var(--primary); margin-top: 2rem; font-family: 'Playfair Display', serif;"><?= __('privacy_sec3_title') ?></h4>
                        <p><?= __('privacy_sec3_desc') ?></p>

                        <h4 style="font-weight: 700; color: var(--primary); margin-top: 2rem; font-family: 'Playfair Display', serif;"><?= __('privacy_sec4_title') ?></h4>
                        <p><?= __('privacy_sec4_desc') ?></p>

                        <div class="alert alert-info mt-5" style="border-radius: 12px; background: rgba(0,170,255,0.1); border: none; color: var(--accent);">
                            <strong><?= __('questions_privacy') ?></strong> <?= __('contact_dpo') ?> <a href="mailto:zora@gmail.com" style="color: inherit; font-weight: bold;">zora@gmail.com</a>.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
