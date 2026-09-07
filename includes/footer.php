<?php
// footer.php
<<<<<<< HEAD
if (!isset($settings)) {
    $settings_q = mysqli_query($conn, "SELECT * FROM settings");
    $settings = [];
    if ($settings_q) {
        while($row = mysqli_fetch_assoc($settings_q)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $settings;
        return htmlspecialchars($settings[$key] ?? $default);
    }
}
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
?>
  <!-- FOOTER -->
  <footer class="footer d-none d-md-block">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
<<<<<<< HEAD
          <div class="footer-brand"><img src="uploads/logo.png" alt="Logo" style="height: 40px; width: auto; object-fit: contain;"></div>
          <p class="footer-desc"><?= __('footer_desc') ?></p>
=======
          <div class="footer-brand"><img src="uploads/Logo.png" alt="Logo" style="height: 40px; width: auto; object-fit: contain;"></div>
          <p class="footer-desc">Your premium destination for quality products. We curate the finest items from around the world, delivered to your door.</p>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
          <div class="social-links mt-3">
            <a href="https://www.instagram.com/zora_shop_rwanda?igsh=amphbDMwNW1vNmdw" target="_blank" class="social-btn instagram text-decoration-none"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@zora_shoprwanda?_r=1&_t=ZS-96MWD6meeLV" target="_blank" class="social-btn tiktok text-decoration-none"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
<<<<<<< HEAD
          <div class="footer-heading"><?= __('shop_heading') ?></div>
          <a href="shop.php" class="footer-link"><?= __('all_products') ?></a>
          <a href="shop.php?sort=newest" class="footer-link"><?= __('new_arrivals') ?></a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading"><?= __('support_heading') ?></div>
          <a href="how_to_order.php" class="footer-link"><?= __('how_to_order') ?></a>
          <a href="order_track.php" class="footer-link"><?= __('order_tracking') ?></a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading"><?= __('company_heading') ?></div>
          <a href="about.php" class="footer-link"><?= __('about_us') ?></a>
          <a href="contact.php" class="footer-link"><?= __('contact_us') ?></a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading"><?= __('legal_heading') ?></div>
          <a href="returns.php" class="footer-link"><?= __('returns_policy') ?></a>
          <a href="privacy.php" class="footer-link"><?= __('privacy_policy') ?></a>
          <a href="terms.php" class="footer-link"><?= __('terms_of_service') ?></a>
        </div>
      </div>
      <div class="footer-bottom">
        <span class="footer-copy">© 2026 Zora Online Shopping Rwanda. All rights reserved.</span>
=======
          <div class="footer-heading">Shop</div>
          <a href="shop.php" class="footer-link">All Products</a>
          <a href="shop.php?sort=newest" class="footer-link">New Arrivals</a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading">Support</div>
          <a href="index.php" class="footer-link">Help Center</a>
          <a href="user_panel.php" class="footer-link">Order Tracking</a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading">Company</div>
          <a href="index.php" class="footer-link">About Us</a>
          <a href="index.php" class="footer-link">Contact Us</a>
        </div>
        <div class="col-6 col-lg-2">
          <div class="footer-heading">Legal</div>
          <a href="index.php" class="footer-link">Privacy Policy</a>
          <a href="index.php" class="footer-link">Terms of Service</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span class="footer-copy">© <?= date('Y') ?> ZORA. All rights reserved.</span>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
      </div>
    </div>
  </footer>

  <!-- ===== MOBILE BOTTOM NAV (PWA STYLE) ===== -->
<<<<<<< HEAD
  <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
  <nav class="mobile-bottom-nav">
      <a href="index.php" class="mobile-nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
          <i class="fas fa-home"></i>
          <span><?= __('home') ?></span>
      </a>
      <a href="shop.php" class="mobile-nav-item <?= in_array($current_page, ['shop.php', 'product.php']) ? 'active' : '' ?>">
          <i class="fas fa-search"></i>
          <span><?= __('products') ?></span>
      </a>
      <a href="favorites.php" class="mobile-nav-item <?= $current_page == 'favorites.php' ? 'active' : '' ?>">
          <div style="position:relative; display:inline-block;">
              <i class="fas fa-heart"></i>
              <span class="cart-badge" style="top: -6px; right: -10px;" id="mobileWishlistCount"><?= $wishlist_count ?></span>
          </div>
          <span><?= __('favs') ?></span>
      </a>
      <a href="cart_view.php" class="mobile-nav-item <?= $current_page == 'cart_view.php' ? 'active' : '' ?>">
          <div style="position:relative; display:inline-block;">
              <i class="fas fa-shopping-bag"></i>
              <span class="cart-badge" style="top: -6px; right: -10px;" id="mobileCartCount"><?= $cart_count ?></span>
          </div>
          <span><?= __('cart') ?></span>
      </a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="<?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin/index.php' : 'user_panel.php' ?>" class="mobile-nav-item <?= $current_page == 'user_panel.php' ? 'active' : '' ?>">
                <?php if(!empty($user_profile_pic)): ?>
                    <img src="uploads/<?= htmlspecialchars($user_profile_pic) ?>" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; margin-bottom: 2px; border: 1px solid var(--border);">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
                <span><?= __('profile') ?></span>
            </a>
        <?php else: ?>
          <a href="javascript:void(0)" onclick="openAuthModal()" class="mobile-nav-item">
              <i class="fas fa-user"></i>
              <span><?= __('login') ?></span>
=======
  <nav class="mobile-bottom-nav">
      <a href="index.php" class="mobile-nav-item">
          <i class="fas fa-home"></i>
          <span>Home</span>
      </a>
      <a href="shop.php" class="mobile-nav-item">
          <i class="fas fa-search"></i>
          <span>Shop</span>
      </a>
      <a href="favorites.php" class="mobile-nav-item" style="position:relative">
          <i class="fas fa-heart"></i>
          <span>Favs</span>
          <span class="cart-badge" style="top: -5px; right: 15px;" id="mobileWishlistCount"><?= $wishlist_count ?></span>
      </a>
      <a href="cart_view.php" class="mobile-nav-item" style="position:relative">
          <i class="fas fa-shopping-bag"></i>
          <span>Cart</span>
          <span class="cart-badge" style="top: -5px; right: 15px;" id="mobileCartCount"><?= $cart_count ?></span>
      </a>
      <?php if(isset($_SESSION['user_id'])): ?>
          <a href="<?= isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin/index.php' : 'user_panel.php' ?>" class="mobile-nav-item">
              <i class="fas fa-user"></i>
              <span>Profile</span>
          </a>
      <?php else: ?>
          <a href="javascript:void(0)" onclick="openAuthModal()" class="mobile-nav-item">
              <i class="fas fa-user"></i>
              <span>Login</span>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
          </a>
      <?php endif; ?>
  </nav>

<<<<<<< HEAD

=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
  <!-- ===== CART SIDEBAR REMOVED ===== -->

  <!-- AUTH MODAL -->
  <div class="modal-overlay" id="authModal">
    <div class="modal-box">
      <button onclick="closeAuthModal()" style="position:absolute;top:1rem;right:1rem;background:none;border:none;color:var(--text3);font-size:1.1rem;cursor:pointer"><i class="fas fa-times"></i></button>
<<<<<<< HEAD
      <div class="modal-title"><?= __('welcome_back') ?></div>
      <p class="text-sm text-muted-custom mb-3"><?= __('sign_in_desc') ?></p>
      
      <div id="authAlert" class="alert d-none mb-3" style="font-size: 0.85rem; padding: 12px; border-radius: 8px;"></div>
      
      <div class="modal-tabs">
        <button class="modal-tab-btn active" onclick="switchModalTab('loginPanel', this)"><?= __('login') ?></button>
        <button class="modal-tab-btn" onclick="switchModalTab('registerPanel', this)"><?= __('register') ?></button>
      </div>
      
      <div class="modal-tab-panel active" id="loginPanel">
        <form id="loginForm" onsubmit="handleAuth(event, 'login')">
            <input type="hidden" name="action" value="login">
            <label class="form-label-custom"><?= __('email_address') ?></label>
            <input class="form-input mb-3" type="email" name="email" placeholder="name@zora-shopping.com" required>
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label-custom mb-0"><?= __('password') ?></label>
                <a href="forgot_password.php" style="font-size: 0.8rem; color: var(--accent); text-decoration: none;">Forgot password?</a>
            </div>
            <div class="password-toggle-wrap">
                <input class="form-input" type="password" name="password" placeholder="••••••••" required>
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <button type="submit" class="btn-primary-full mt-3"><?= __('login') ?></button>
=======
      <div class="modal-title">Welcome Back</div>
      <p class="text-sm text-muted-custom mb-3">Sign in to your account or create a new one.</p>
      <div class="modal-tabs">
        <button class="modal-tab-btn active" onclick="switchModalTab('loginPanel', this)">Sign In</button>
        <button class="modal-tab-btn" onclick="switchModalTab('registerPanel', this)">Register</button>
      </div>
      
      <div class="modal-tab-panel active" id="loginPanel">
        <form action="core/actions.php" method="POST">
            <input type="hidden" name="action" value="login">
            <label class="form-label-custom">Email Address</label>
            <input class="form-input mb-3" type="email" name="email" placeholder="you@example.com" required>
            <label class="form-label-custom">Password</label>
            <input class="form-input" type="password" name="password" placeholder="••••••••" required>
            <button type="submit" class="btn-primary-full">Sign In</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        </form>
      </div>
      
      <div class="modal-tab-panel" id="registerPanel">
<<<<<<< HEAD
        <form id="registerForm" onsubmit="handleAuth(event, 'register')">
            <input type="hidden" name="action" value="register">
            <div class="row g-2">
              <div class="col-6"><label class="form-label-custom"><?= __('first_name') ?></label><input class="form-input" name="first_name" type="text" placeholder="<?= __('first_name') ?>" required></div>
              <div class="col-6"><label class="form-label-custom"><?= __('last_name') ?></label><input class="form-input" name="last_name" type="text" placeholder="<?= __('last_name') ?>" required></div>
            </div>
            <label class="form-label-custom mt-3"><?= __('email_address') ?></label>
            <input class="form-input mb-3" type="email" name="email" placeholder="name@zora-shopping.com" required>
            
            <label class="form-label-custom"><?= __('password') ?></label>
            <div class="password-toggle-wrap mb-2">
                <input class="form-input" type="password" name="password" placeholder="8+ chars, uppercase, number, symbol" required>
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <label class="form-label-custom"><?= __('confirm_password') ?></label>
            <div class="password-toggle-wrap">
                <input class="form-input" type="password" name="confirm_password" placeholder="<?= __('confirm_password') ?>" required>
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <button type="submit" class="btn-primary-full mt-3"><?= __('create_account') ?></button>
=======
        <form action="core/actions.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="row g-2">
              <div class="col-6"><label class="form-label-custom">First Name</label><input class="form-input" name="first_name" type="text" placeholder="John" required></div>
              <div class="col-6"><label class="form-label-custom">Last Name</label><input class="form-input" name="last_name" type="text" placeholder="Doe" required></div>
            </div>
            <label class="form-label-custom mt-3">Email Address</label>
            <input class="form-input mb-3" type="email" name="email" placeholder="you@example.com" required>
            <label class="form-label-custom">Password</label>
            <input class="form-input" type="password" name="password" placeholder="Min. 8 characters" required>
            <button type="submit" class="btn-primary-full mt-3">Create Account</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        </form>
      </div>
    </div>
  </div>

<<<<<<< HEAD
  <!-- ===== FLOATING CHAT POPUP ===== -->
  <div class="chat-popup-widget" id="chatPopupWidget">
    <div class="chat-popup-header">
      <div class="chat-popup-title">
        <div class="chat-icon-bg">
          <i class="fas fa-comment-dots"></i>
        </div>
        <div>
          <div class="chat-popup-title-text"><?= __('zora_support') ?></div>
          <div class="chat-popup-status-text"><span class="chat-popup-status-dot"></span> <?= __('online_replies_instantly') ?></div>
        </div>
      </div>
      <button class="chat-close-btn" onclick="toggleChatPopup()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="chat-popup-body">
      <div class="chat-bubble">
        <?= __('welcome_help_today') ?>
      </div>
      <div class="chat-links">
        <a href="shop.php" class="chat-link-btn">
          <span><?= __('browse_products_chat') ?></span> <i class="fas fa-external-link-alt"></i>
        </a>
        <a href="order_track.php" class="chat-link-btn">
          <span><?= __('track_order_chat') ?></span> <i class="fas fa-external-link-alt"></i>
        </a>
        <?php
        $wa_phone = get_setting('support_phone', '0785242513');
        $wa_clean = preg_replace('/[^0-9]/', '', $wa_phone);
        if (substr($wa_clean, 0, 3) !== '250' && substr($wa_clean, 0, 1) === '0') {
            $wa_clean = '250' . substr($wa_clean, 1);
        }
        ?>
        <a href="https://wa.me/<?= $wa_clean ?>" class="chat-link-btn" target="_blank">
          <span><?= __('whatsapp_us_chat') ?></span>
        </a>
        <a href="how_to_order.php" class="chat-link-btn">
          <span><?= __('how_to_order_chat') ?></span>
        </a>
        <a href="delivery_info.php" class="chat-link-btn">
          <span><?= __('delivery_info_chat') ?></span>
        </a>
      </div>
    </div>
  </div>

  <button class="floating-chat-btn" onclick="toggleChatPopup()">
    <i class="fas fa-comment-dots"></i> 
    <div class="help-text-wrapper">
      <span class="help-text"><?= __('customer_support') ?></span>
    </div>
  </button>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="assets/script.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    // Framer-motion style staggered animations for all content
    document.querySelectorAll('.product-card, .cat-card-small, .hero-mini-card, .admin-card').forEach((el, index) => {
        if (!el.hasAttribute('data-aos')) {
            el.setAttribute('data-aos', 'fade-up');
            el.setAttribute('data-aos-delay', (index % 5) * 75); // Stagger effect
        }
    });
    
    document.querySelectorAll('.section-title, .section-eyebrow, .cat-card, .footer-heading, .footer-link').forEach((el, index) => {
        if (!el.hasAttribute('data-aos')) {
            el.setAttribute('data-aos', 'fade-up');
            el.setAttribute('data-aos-delay', (index % 3) * 50);
        }
    });

    AOS.init({
      once: true,
      duration: 800,
      offset: 40,
      easing: 'ease-out-back' // Spring-like bouncy effect similar to Framer Motion
    });
    
    function toggleChatPopup() {
      document.getElementById('chatPopupWidget').classList.toggle('open');
    }
    
    function closeChatPopup() {
      document.getElementById('chatPopupWidget').classList.remove('open');
    }
  </script>
<script>
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update icons
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        icon.className = newTheme === 'dark' ? 'fas fa-sun darkModeIcon' : 'fas fa-moon darkModeIcon';
    });
}

// Set initial icon state
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        icon.className = isDark ? 'fas fa-sun darkModeIcon' : 'fas fa-moon darkModeIcon';
    });
});
</script>
=======
  <script src="assets/script.js"></script>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
</body>
</html>
