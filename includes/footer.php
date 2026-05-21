<?php
// footer.php
?>
  <!-- FOOTER -->
  <footer class="footer d-none d-md-block">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="footer-brand"><img src="uploads/Logo.png" alt="Logo" style="height: 40px; width: auto; object-fit: contain;"></div>
          <p class="footer-desc">Your premium destination for quality products. We curate the finest items from around the world, delivered to your door.</p>
          <div class="social-links mt-3">
            <a href="https://www.instagram.com/zora_shop_rwanda?igsh=amphbDMwNW1vNmdw" target="_blank" class="social-btn instagram text-decoration-none"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@zora_shoprwanda?_r=1&_t=ZS-96MWD6meeLV" target="_blank" class="social-btn tiktok text-decoration-none"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
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
      </div>
    </div>
  </footer>

  <!-- ===== MOBILE BOTTOM NAV (PWA STYLE) ===== -->
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
          </a>
      <?php endif; ?>
  </nav>

  <!-- ===== CART SIDEBAR REMOVED ===== -->

  <!-- AUTH MODAL -->
  <div class="modal-overlay" id="authModal">
    <div class="modal-box">
      <button onclick="closeAuthModal()" style="position:absolute;top:1rem;right:1rem;background:none;border:none;color:var(--text3);font-size:1.1rem;cursor:pointer"><i class="fas fa-times"></i></button>
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
        </form>
      </div>
      
      <div class="modal-tab-panel" id="registerPanel">
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
        </form>
      </div>
    </div>
  </div>

  <script src="assets/script.js"></script>
</body>
</html>
