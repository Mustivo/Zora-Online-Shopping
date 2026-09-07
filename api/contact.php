<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/includes/header.php';

// require_once 'core/config.php';
$page_title = "Contact Us - Zora Shop Rwanda";
$meta_desc = "Get in touch with Zora Shop Rwanda. We are here to help you with your orders, questions, and feedback. Contact us today!";
$meta_keywords = "Contact Zora Shop, Zora Shop customer service, Rwanda online shopping contact";
// require_once 'includes/header.php';

$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $first_name = clean_input($conn, $_POST['first_name']);
    $last_name = clean_input($conn, $_POST['last_name']);
    $email = clean_input($conn, $_POST['email']);
    $subject = clean_input($conn, $_POST['subject']);
    $message = clean_input($conn, $_POST['message']);
    
    $full_name = trim($first_name . ' ' . $last_name);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    $query = "INSERT INTO support_tickets (user_id, customer_name, customer_email, subject, message) 
              VALUES ($user_id, '$full_name', '$email', '$subject', '$message')";
              
    if(mysqli_query($conn, $query)) {
        $message_sent = true;
        $notif_msg = clean_input($conn, "New support ticket from $full_name: $subject");
        mysqli_query($conn, "INSERT INTO admin_notifications (message, link) VALUES ('$notif_msg', 'support.php')");
    }
}

$settings_q = mysqli_query($conn, "SELECT * FROM settings");
$settings = [];
if ($settings_q) {
    while($row = mysqli_fetch_assoc($settings_q)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $settings;
        return htmlspecialchars($settings[$key] ?? $default);
    }
}
?>

<section class="hero d-flex align-items-center justify-content-center text-center" style="min-height: 50vh; background: url('uploads/zora_contact_hero.png') center/cover no-repeat; position: relative; overflow: hidden;">
  <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 42, 94, 0.85) 0%, rgba(1, 42, 94, 0.6) 60%, rgba(251, 124, 0, 0.4) 100%); z-index: 1;"></div>
  <div class="hero-grid-lines" style="z-index: 1; opacity: 0.2;"></div>
  <div class="container position-relative" style="z-index: 2; display: flex; flex-direction: column; align-items: center;">
    <div style="animation:fadeUp 0.8s ease; max-width: 800px;">
      <div class="section-eyebrow mb-3" style="color: var(--accent); letter-spacing: 4px; font-weight: 800;"><?= __('contact_hero_eyebrow') ?></div>
      <h1 class="hero-title mb-4 text-white" style="font-family: 'Playfair Display', serif; font-size: 4.5rem; line-height: 1.1; font-weight: 900; letter-spacing: -1px; text-shadow: 2px 4px 10px rgba(0,0,0,0.3);"><?= __('contact_hero_title') ?></h1>
      <p class="text-white mb-0" style="font-size: 1.2rem; opacity: 0.95; line-height: 1.8; text-shadow: 1px 2px 5px rgba(0,0,0,0.3);">
        <?= __('contact_hero_desc') ?>
      </p>
    </div>
  </div>
</section>

<section class="py-5" style="background: var(--bg2);">
  <div class="container py-5">
    <div class="row g-5">
      <div class="col-lg-4">
        <h2 class="section-title mb-4" style="font-size: 2.2rem; color: var(--primary);"><?= __('contact_details') ?></h2>
        <p class="text-muted-custom mb-5" style="line-height: 1.8; font-size: 1.05rem;">
          <?= __('contact_details_desc') ?>
        </p>

        <div class="admin-card p-4 mb-4" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(1, 42, 94, 0.1), transparent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.5rem; flex-shrink: 0;">
                <i class="fas fa-location-dot"></i>
            </div>
            <div>
                <h5 style="font-weight: 800; margin-bottom: 0.25rem; font-size: 1.1rem; color: var(--text);"><?= __('head_office') ?></h5>
                <p class="text-muted-custom mb-0 text-sm"><?= nl2br(get_setting('store_address', "KN 4 Ave, Kigali, Rwanda\nKigali City Tower, 5th Floor")) ?></p>
            </div>
        </div>

        <div class="admin-card p-4 mb-4" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(251, 124, 0, 0.1), transparent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 1.5rem; flex-shrink: 0;">
                <i class="fas fa-envelope"></i>
            </div>
            <div>
                <h5 style="font-weight: 800; margin-bottom: 0.25rem; font-size: 1.1rem; color: var(--text);"><?= __('email_us') ?></h5>
                <p class="text-muted-custom mb-0 text-sm"><a href="mailto:<?= get_setting('contact_email', 'support@zora-shopping.com') ?>" class="text-decoration-none text-muted-custom"><?= get_setting('contact_email', 'support@zora-shopping.com') ?></a><br><a href="mailto:<?= get_setting('support_email_sales', 'sales@zora-shopping.com') ?>" class="text-decoration-none text-muted-custom"><?= get_setting('support_email_sales', 'sales@zora-shopping.com') ?></a></p>
            </div>
        </div>

        <div class="admin-card p-4" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 1.5rem; transition: transform 0.3s;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(76, 175, 125, 0.1), transparent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success); font-size: 1.5rem; flex-shrink: 0;">
                <i class="fas fa-phone"></i>
            </div>
            <div>
                <h5 style="font-weight: 800; margin-bottom: 0.25rem; font-size: 1.1rem; color: var(--text);"><?= __('call_us') ?></h5>
                <p class="text-muted-custom mb-0 text-sm"><a href="tel:<?= get_setting('support_phone', '0785242513') ?>" class="text-decoration-none text-muted-custom"><?= get_setting('support_phone', '0785242513') ?></a><br><?= get_setting('support_working_hours', 'Mon-Sat: 8 AM - 8 PM') ?></p>
            </div>
        </div>
      </div>
      
      <div class="col-lg-8">
        <div class="h-100 d-flex flex-column justify-content-center" style="background: var(--bg3); border-radius: 20px; padding: 4rem; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 20px 50px rgba(0,0,0,0.04);">
            <div class="section-eyebrow mb-2"><?= __('drop_a_line') ?></div>
            <h3 class="mb-4" style="font-family: 'Playfair Display', serif; font-weight: 800; font-size: 2.5rem; color: var(--primary);"><?= __('send_message_title') ?></h3>
            
            <?php if ($message_sent): ?>
                <div class="alert alert-success d-flex align-items-center p-4 mb-4" style="border-radius: 12px; border-left: 5px solid var(--success); background: rgba(76,175,125,0.1); color: var(--success); border-top: none; border-right: none; border-bottom: none;">
                    <i class="fas fa-check-circle me-3 fs-3"></i>
                    <div style="font-size: 1.05rem; font-weight: 500;"><?= __('contact_success') ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label-custom" style="font-size: 0.8rem;"><?= __('first_name') ?></label>
                        <input type="text" name="first_name" class="form-input py-3" placeholder="<?= __('first_name') ?>" required style="border-radius: 12px;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom" style="font-size: 0.8rem;"><?= __('last_name') ?></label>
                        <input type="text" name="last_name" class="form-input py-3" placeholder="<?= __('last_name') ?>" required style="border-radius: 12px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom" style="font-size: 0.8rem;"><?= __('email_address') ?></label>
                        <input type="email" name="email" class="form-input py-3" placeholder="name@example.com" required style="border-radius: 12px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom" style="font-size: 0.8rem;"><?= __('subject') ?></label>
                        <input type="text" name="subject" class="form-input py-3" placeholder="How can we help?" required style="border-radius: 12px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom" style="font-size: 0.8rem;"><?= __('message') ?></label>
                        <textarea name="message" class="form-input py-3" rows="6" placeholder="Write your message here..." required style="border-radius: 12px;"></textarea>
                    </div>
                    <div class="col-12 mt-4 pt-2">
                        <button type="submit" name="submit_contact" class="btn-primary-full py-3" style="font-size: 1.1rem; border-radius: 50px;"><?= __('send_message') ?> <i class="fas fa-paper-plane ms-2"></i></button>
                    </div>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
