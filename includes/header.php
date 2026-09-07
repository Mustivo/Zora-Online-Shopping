<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlist_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM wishlist WHERE user_id = $uid");
    if ($w_count_row = mysqli_fetch_assoc($w_count_res)) {
        $wishlist_count = $w_count_row['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zora Online Shopping</title>
<link rel="icon" href="uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>

<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'en,rw', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
/* Hide Google Translate Native UI Elements completely */
.goog-te-banner-frame.skiptranslate { display: none !important; }
body { top: 0px !important; }
.goog-tooltip { display: none !important; }
.goog-tooltip:hover { display: none !important; }
.goog-text-highlight { background-color: transparent !important; border: none !important; box-shadow: none !important; }
#google_translate_element { opacity: 0 !important; position: absolute !important; z-index: -1 !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
</style>
<!-- ===== NAVBAR ===== -->
<nav class="navbar-main">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between gap-3">
      <a href="index.php" class="nav-brand">
        <img src="uploads/Logo.png" alt="ZORA" style="height: 40px; width: auto; object-fit: contain;">
      </a>
      <div class="d-none d-md-flex align-items-center gap-1">
        <a href="index.php" class="nav-link-custom">Home</a>
        <a href="shop.php" class="nav-link-custom">Shop</a>
        <a href="index.php#categories" class="nav-link-custom">Categories</a>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <form action="shop.php" method="GET" class="m-0 p-0 d-none d-md-block">
                <input type="text" name="search" class="search-bar" placeholder="Search products...">
            </form>
            <a href="#" onclick="toggleLanguage(); return false;" class="text-dark fs-4 mx-2" title="Translate English / Kinyarwanda"><i class="fas fa-language"></i></a>
            <div id="google_translate_element" style="display:none;"></div>
            <script type="text/javascript">
            function toggleLanguage() {
                var lang = 'rw';
                var cookie = document.cookie.match(/(^|;) ?googtrans=([^;]*)(;|$)/);
                if (cookie && cookie[2].endsWith('rw')) {
                    lang = 'en';
                }
                doGTranslate('en|' + lang);
            }
            function doGTranslate(lang_pair) {
                if(lang_pair.value) lang_pair=lang_pair.value;
                if(lang_pair=='') return;
                var lang=lang_pair.split('|')[1];
                var teCombo;
                var sel=document.getElementsByTagName('select');
                for(var i=0;i<sel.length;i++)
                    if(sel[i].className.indexOf('goog-te-combo')!=-1) { teCombo=sel[i]; break; }
                if(document.getElementById('google_translate_element')==null || document.getElementById('google_translate_element').innerHTML.length==0 || teCombo.length==0 || teCombo.innerHTML.length==0) {
                    setTimeout(function() { doGTranslate(lang_pair) }, 500);
                } else {
                    teCombo.value=lang;
                    if(typeof document.createEvent!='undefined') {
                        var e=document.createEvent('HTMLEvents');
                        e.initEvent('change',true,true);
                        teCombo.dispatchEvent(e);
                    } else {
                        teCombo.fireEvent('onchange');
                    }
                }
            }
            </script>
            <a href="https://www.tiktok.com/@zora_shoprwanda?_r=1&_t=ZS-96MWD6meeLV" target="_blank" class="social-btn tiktok text-decoration-none mx-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.instagram.com/zora_shop_rwanda?igsh=amphbDMwNW1vNmdw" target="_blank" class="social-btn instagram text-decoration-none mx-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fab fa-instagram"></i></a>
            <button class="btn-nav-cart d-none d-md-inline-block me-1" onclick="window.location.href='favorites.php'">
              <i class="fas fa-heart"></i>
              <span class="cart-badge" id="wishlistCount"><?= $wishlist_count ?></span>
            </button>
            <button class="btn-nav-cart d-none d-md-inline-block" onclick="window.location.href='cart_view.php'">
              <i class="fas fa-shopping-bag me-1"></i> <span class="d-none d-lg-inline">Cart</span>
              <span class="cart-badge" id="cartCount"><?= $cart_count ?></span>
            </button>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="btn-nav-cart text-decoration-none d-none d-md-inline-block"><i class="fas fa-cog"></i></a>
                <?php else: ?>
                    <a href="user_panel.php" class="btn-nav-cart text-decoration-none d-none d-md-inline-block"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn ms-1 fw-bold d-none d-md-inline-block" onclick="openAuthModal()" style="background-color: var(--accent); color: white; border: none; border-radius: 6px; padding: 6px 16px;">Sign in</button>
            <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- ===== TOAST CONTAINER ===== -->
<div class="toast-container" id="toastContainer"></div>
