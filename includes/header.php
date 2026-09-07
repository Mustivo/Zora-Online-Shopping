<?php
// header.php
    date_default_timezone_set('Africa/Kigali');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlist_count = 0;
$user_profile_pic = '';
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM wishlist WHERE user_id = $uid");
    if ($w_count_row = mysqli_fetch_assoc($w_count_res)) {
        $wishlist_count = $w_count_row['c'];
    }
    $u_res = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id = $uid");
    if ($u_row = mysqli_fetch_assoc($u_res)) {
        $user_profile_pic = $u_row['profile_picture'];
    }
}

// Fetch global order status and messages for the banner
$status_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_status'");
$store_order_status = ($status_q && mysqli_num_rows($status_q) > 0) ? mysqli_fetch_assoc($status_q)['setting_value'] : 'enable';

$msg_key = (!empty($is_rw)) ? 'store_order_message_rw' : 'store_order_message';
$msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = '$msg_key'");
$store_order_message = ($msg_q && mysqli_num_rows($msg_q) > 0) ? mysqli_fetch_assoc($msg_q)['setting_value'] : '';
if (empty($store_order_message) && !empty($is_rw)) {
    $fallback_msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_message'");
    $store_order_message = ($fallback_msg_q && mysqli_num_rows($fallback_msg_q) > 0) ? mysqli_fetch_assoc($fallback_msg_q)['setting_value'] : '';
}

$prob_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_problem_faced'");
$store_order_problem = ($prob_q && mysqli_num_rows($prob_q) > 0) ? mysqli_fetch_assoc($prob_q)['setting_value'] : '';


// Fetch customer notifications
$uid_query_part = isset($_SESSION['user_id']) ? "user_id = " . (int)$_SESSION['user_id'] . " OR " : "";
$notifications_query = "SELECT id, message, link, is_read, created_at FROM user_notifications WHERE " . $uid_query_part . "(user_id IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) ORDER BY created_at DESC LIMIT 5";
$notifications_result = mysqli_query($conn, $notifications_query);
$notifications_count = 0;
$user_notifications = [];
if ($notifications_result) {
    while ($notif_row = mysqli_fetch_assoc($notifications_result)) {
        if ($notif_row['is_read'] == 0) $notifications_count++;
        $user_notifications[] = $notif_row;
    }
}
?>
<?php
// Default SEO values if not set before including header.php
$page_title = isset($page_title) ? $page_title : "Zora Online Shopping Rwanda";
$meta_desc = isset($meta_desc) ? $meta_desc : "Shop the best fashion, clothes, and accessories online at Zora Online Shopping in Rwanda. Fast delivery and premium quality.";
$meta_keywords = isset($meta_keywords) ? $meta_keywords : "Online shopping Rwanda, Buy clothes online Kigali, Fashion store Rwanda, Zora shop";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$canonical_url = isset($canonical_url) ? $canonical_url : $protocol . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'en') ?>" <?= ($lang === 'rw') ? 'class="notranslate" translate="no"' : '' ?>>
<head>
<meta charset="UTF-8">
<?php if ($lang === 'rw'): ?>
<meta name="google" content="notranslate">
<?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
<meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
<link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
<link rel="icon" href="uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/style.css?v=<?= time() ?>" rel="stylesheet">
<style>
@media (max-width: 991px) {
    .mobile-bottom-nav {
        display: flex !important;
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 60px !important;
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        z-index: 999999 !important;
        align-items: center !important;
        justify-content: space-around !important;
        padding: 4px 0 !important;
        box-shadow: 0 -4px 16px rgba(0,0,0,0.08) !important;
    }
    .mobile-nav-item {
        flex: 1 !important;
        text-align: center !important;
        color: #64748b !important;
        text-decoration: none !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 2px !important;
    }
    .mobile-nav-item i {
        font-size: 1.25rem !important;
        line-height: 1.2 !important;
    }
    .mobile-nav-item.active, .mobile-nav-item:hover {
        color: #2563eb !important;
    }
    [data-theme="dark"] .mobile-bottom-nav {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }
    [data-theme="dark"] .mobile-nav-item {
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .mobile-nav-item.active,
    [data-theme="dark"] .mobile-nav-item:hover {
        color: #3b82f6 !important;
    }
    body {
        padding-bottom: 75px !important;
    }
}
<?php if(isset($is_rw) && $is_rw): ?>
    .btn-add-cart { font-size: 0.7rem !important; text-transform: none !important; padding: 0.4rem 0.8rem !important; }
    .btn-add-cart-lg { font-size: 0.75rem !important; text-transform: none !important; padding: 0.7rem 1.5rem !important; }
    .hero-mini-card .name { font-size: 0.65rem !important; }
    .nav-link-custom { font-size: 0.75rem !important; padding: 0.4rem 0.4rem !important; }
    .navbar-main .btn { font-size: 0.75rem !important; padding: 4px 10px !important; }
    .search-bar { width: 140px !important; }
<?php endif; ?>
<?php
$bg_image_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_background_image'");
$site_bg_image = ($bg_image_query && mysqli_num_rows($bg_image_query) > 0) ? mysqli_fetch_assoc($bg_image_query)['setting_value'] : '';

$font_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_font_family'");
$site_font = ($font_query && mysqli_num_rows($font_query) > 0) ? htmlspecialchars_decode(mysqli_fetch_assoc($font_query)['setting_value']) : '';
?>
<?php if (!empty($site_bg_image)): ?>
    .hero {
        background-image: 
            linear-gradient(90deg, rgba(8, 12, 22, 0.88) 0%, rgba(15, 23, 42, 0.48) 50%, rgba(15, 23, 42, 0.12) 100%),
            url('uploads/<?= htmlspecialchars($site_bg_image) ?>') !important;
        background-size: cover !important;
        background-position: center right !important;
        background-repeat: no-repeat !important;
        animation: none !important;
    }
<?php endif; ?>
<?php if (!empty($site_font)): ?>
    *:not(.fa):not(.fas):not(.fab):not(.far):not(.fal):not(.fad):not(.fi) {
        font-family: <?= $site_font ?> !important;
    }
<?php endif; ?>
</style>
<link href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script>
    function toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        
        if (newTheme === 'dark') {
            html.setAttribute('data-theme', 'dark');
        } else {
            html.removeAttribute('data-theme');
        }
        localStorage.setItem('theme', newTheme);
        
        const icons = document.querySelectorAll('.darkModeIcon');
        icons.forEach(icon => {
            if (newTheme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }

    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const icons = document.querySelectorAll('.darkModeIcon');
        icons.forEach(icon => {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    });
</script>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-SJ2S761K41"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-SJ2S761K41');
</script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function customConfirm(event, message) {
        event.preventDefault();
        const target = event.target || event.srcElement;
        const form = target.tagName === 'FORM' ? target : target.closest('form');
        const link = target.tagName === 'A' ? target : target.closest('a');
        
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#012a5e',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                if (form) {
                    form.onsubmit = null;
                    form.submit();
                } else if (link) {
                    window.location.href = link.href;
                }
            }
        });
        return false;
    }

    function customAlert(message) {
        Swal.fire({
            title: 'Message',
            text: message,
            icon: 'info',
            confirmButtonColor: '#2563eb'
        });
    }

    function openAuthModal() {
        const modal = document.getElementById('authModal');
        if (modal) {
            modal.classList.add('open');
            modal.style.display = 'flex';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.zIndex = '99999';
            const alertBox = document.getElementById('authAlert');
            if (alertBox) alertBox.classList.add('d-none');
        }
    }

    function closeAuthModal() {
        const modal = document.getElementById('authModal');
        if (modal) {
            modal.classList.remove('open');
            modal.style.display = 'none';
        }
    }
    </script>
</head>
<body class="page-fade-up">

<style>


@keyframes popAnimation {
    0% { transform: scale(1) rotate(0deg); }
    20% { transform: scale(1.2) rotate(10deg); }
    40% { transform: scale(1.2) rotate(-10deg); }
    60% { transform: scale(1.2) rotate(10deg); }
    80% { transform: scale(1.2) rotate(-10deg); }
    100% { transform: scale(1) rotate(0deg); }
}
.bell-pop {
    animation: popAnimation 2s infinite ease-in-out;
    display: inline-block;
}
</style>
<header class="fixed-top" style="z-index: 1050; background: var(--bg);">
<?php if ($store_order_status !== 'enable' && (!empty($store_order_message) || !empty($store_order_problem))): ?>
    <div style="background: <?= $store_order_status === 'disable' ? 'var(--accent)' : '#f59e0b' ?>; color: white; text-align: center; padding: 10px 15px; font-size: 0.9rem; font-weight: 600; width: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <i class="fas <?= $store_order_status === 'disable' ? 'fa-ban' : 'fa-clock' ?> me-2"></i>
        <?= htmlspecialchars($store_order_message) ?> 
        <?php if(!empty($store_order_problem)): ?>
            <span style="opacity: 0.85; margin-left: 10px; font-weight: normal;">(<?= htmlspecialchars($store_order_problem) ?>)</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ===== NAVBAR ===== -->
<nav class="navbar-main">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between gap-1 gap-lg-2">
      <a href="index.php" class="nav-brand flex-shrink-0">
        <img src="uploads/logo.png" alt="ZORA" class="notranslate" style="height: 40px; width: auto; object-fit: contain;">
      </a>
      <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
      <div class="d-none d-md-flex align-items-center gap-1">
        <a href="index.php" class="nav-link-custom <?= $current_page == 'index.php' ? 'active' : '' ?>"><?= __('home') ?></a>
        <a href="shop.php" class="nav-link-custom <?= in_array($current_page, ['shop.php', 'product.php', 'category.php']) ? 'active' : '' ?>"><?= __('shop') ?></a>
        <a href="about.php" class="nav-link-custom <?= $current_page == 'about.php' ? 'active' : '' ?>"><?= __('about') ?></a>
        <a href="contact.php" class="nav-link-custom <?= $current_page == 'contact.php' ? 'active' : '' ?>"><?= __('contact') ?></a>
      </div>
      <div class="d-flex align-items-center gap-1 gap-lg-2">
        <div class="d-flex align-items-center gap-1 gap-lg-2">
            <form action="shop.php" method="GET" class="m-0 p-0 d-none d-md-block">
                <input type="text" name="search" class="search-bar" placeholder="<?= __('search_placeholder') ?>">
            </form>
            
            <button class="btn btn-sm d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearchBox" aria-expanded="false" aria-controls="mobileSearchBox" style="color: var(--text); padding: 0 4px; box-shadow: none;" title="Search">
                <i class="fas fa-search"></i>
            </button>
            
            <?php
            $current_get = $_GET;
            $current_get['lang'] = 'en';
            $lang_en_url = '?' . http_build_query($current_get);
            $current_get['lang'] = 'rw';
            $lang_rw_url = '?' . http_build_query($current_get);
            ?>
            <div class="d-flex align-items-center mx-1 gap-1" style="background: var(--bg3); padding: 4px 6px; border-radius: 50px; border: 1px solid var(--border);">
                <button onclick="toggleDarkMode()" class="btn btn-sm" style="color: var(--text); padding: 0 4px; box-shadow: none;" title="Toggle Dark Mode">
                    <i class="fas fa-moon darkModeIcon"></i>
                </button>
                <div style="width: 1px; height: 14px; background: var(--border);"></div>
                <a href="<?= htmlspecialchars($lang_en_url) ?>" class="text-decoration-none d-flex align-items-center gap-1" title="English" style="transition: all 0.2s; color: var(--text);">
                    <span class="fi fi-us" style="border-radius: 50%; width: 14px; height: 14px; background-size: cover; background-position: center; border: 1px solid var(--border);"></span> <span style="font-size: 0.75rem; font-weight: 700;">EN</span>
                </a>
                <div style="width: 1px; height: 14px; background: var(--border);"></div>
                <a href="<?= htmlspecialchars($lang_rw_url) ?>" class="text-decoration-none d-flex align-items-center gap-1" title="Kinyarwanda" style="transition: all 0.2s; color: var(--text);">
                    <span class="fi fi-rw" style="border-radius: 50%; width: 14px; height: 14px; background-size: cover; background-position: center; border: 1px solid var(--border);"></span> <span style="font-size: 0.75rem; font-weight: 700;">RW</span>
                </a>
            </div>
            <a href="https://www.tiktok.com/@zora_shoprwanda?_r=1&_t=ZS-96MWD6meeLV" target="_blank" class="social-btn tiktok text-decoration-none mx-1 d-none d-md-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.instagram.com/zora_shop_rwanda?igsh=amphbDMwNW1vNmdw" target="_blank" class="social-btn instagram text-decoration-none mx-1 d-none d-md-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-instagram"></i></a>
            
            <div class="dropdown d-inline-block me-1">
                <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn-nav-cart dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative;">
                    <i class="fas fa-bell <?= $notifications_count > 0 ? 'bell-pop text-danger' : '' ?>"></i>
                    <?php if ($notifications_count > 0): ?>
                        <span class="cart-badge bg-danger" style="background: red !important; border-radius: 50%; padding: 2px 5px; font-size: 0.6rem; position: absolute; top: -5px; right: -5px;"><?= $notifications_count ?></span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notificationDropdown" style="width: 320px; padding: 0;">
                    <li class="dropdown-header fw-bold" style="background: var(--bg2); padding: 10px 15px; border-bottom: 1px solid var(--border);">Notifications</li>
                    <?php if (!empty($user_notifications)): ?>
                        <?php foreach ($user_notifications as $notif): ?>
                            <li style="border-bottom: 1px solid var(--border);">
                                <a class="dropdown-item d-flex align-items-start gap-3 py-3 <?= $notif['is_read'] ? '' : 'bg-light' ?>" href="<?= htmlspecialchars($notif['link']) ?>" style="white-space: normal;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div style="flex: 1; line-height: 1.3;">
                                        <span style="font-size: 0.85rem; color: var(--text);"><?= html_entity_decode($notif['message']) ?></span><br>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= date('M d, H:i', strtotime($notif['created_at'])) ?></small>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><a href="user_panel.php?tab=notifications" class="dropdown-item text-center text-primary py-2 fw-bold" style="font-size: 0.85rem;">View All Notifications</a></li>
                    <?php else: ?>
                        <li><div class="dropdown-item text-muted text-center py-3" style="font-size: 0.85rem;">No notifications yet.</div></li>
                    <?php endif; ?>
                </ul>
                <?php else: ?>
                <button class="btn-nav-cart" type="button" onclick="openAuthModal();" style="position: relative;" title="<?= __('notifications') ?>">
                    <i class="fas fa-bell"></i>
                </button>
                <?php endif; ?>
            </div>

            <button class="btn-nav-cart d-none d-md-inline-block me-1" onclick="window.location.href='favorites.php'" title="<?= __('wishlist') ?>">
              <i class="fas fa-heart"></i>
              <span class="cart-badge" id="wishlistCount"><?= $wishlist_count ?></span>
            </button>
            <button class="btn-nav-cart d-none d-md-inline-block" onclick="window.location.href='cart_view.php'">
              <i class="fas fa-shopping-bag me-1"></i> <span class="d-none d-lg-inline" <?= $is_rw ? 'style="font-size: 0.75rem;"' : '' ?>><?= __('cart') ?></span>
              <span class="cart-badge" id="cartCount"><?= $cart_count ?></span>
            </button>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/index.php" class="btn-nav-cart text-decoration-none d-none d-md-inline-block"><i class="fas fa-cog"></i></a>
                    <?php else: ?>
                        <div class="dropdown d-none d-md-inline-block">
                            <a href="#" class="btn-nav-cart text-decoration-none d-inline-flex p-0" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px; overflow: hidden; border-radius: 50%; align-items: center; justify-content: center;">
                                <?php if(!empty($user_profile_pic)): ?>
                                    <img src="uploads/<?= htmlspecialchars($user_profile_pic) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 8px; margin-top: 10px;">
                                <li><a class="dropdown-item" href="user_panel.php"><i class="fas fa-user-circle me-2 text-muted"></i> Account</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="core/actions.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
            <?php else: ?>
                <button type="button" class="btn ms-1 fw-bold d-none d-lg-inline-block text-nowrap" onclick="openAuthModal()" style="background-color: var(--accent2); color: white; border: none; border-radius: 6px; padding: 6px 14px; white-space: nowrap; font-size: 0.82rem; cursor: pointer; position: relative; z-index: 1060;"><?= __('sign_in') ?></button>
            <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- Mobile Search Box (Collapsible) -->
    <div class="collapse d-md-none" id="mobileSearchBox">
        <div class="container py-2 pb-3">
            <form action="shop.php" method="GET" class="m-0 p-0 d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm border-0 shadow-sm" placeholder="<?= __('search_placeholder') ?>" style="border-radius: 8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 8px;"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</nav>
</header>

<!-- ===== TOAST CONTAINER ===== -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 1055;">
    <?php if ($notifications_count > 0): ?>
    <div id="newProductToast" class="toast align-items-center text-white border-0" style="background-color: var(--accent);" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-bell me-2"></i> You have <?= $notifications_count ?> unread notification(s)! Click the bell icon to view them.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
      <script>
      document.addEventListener("DOMContentLoaded", function() {
          let latestNotifId = "<?= !empty($user_notifications) ? $user_notifications[0]['id'] : 0 ?>";
          let seenNotifId = localStorage.getItem('seenNotifId');
          
          if(latestNotifId != 0 && seenNotifId !== latestNotifId) {
              if(typeof bootstrap !== 'undefined') {
                  var toastEl = document.getElementById('newProductToast');
                  var toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
                  toast.show();
                  localStorage.setItem('seenNotifId', latestNotifId);
              }
          }
      });
      </script>
    <?php endif; ?>
</div>
