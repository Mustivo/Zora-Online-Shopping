<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - ZORA</title>
<link rel="icon" href="../uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">
<style>
.product-img-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
.admin-nav-header { padding: 1.25rem 1rem 0.5rem; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--text3); }
.admin-sidebar { width: 260px; background: var(--bg2); border-right: 1px solid var(--border); display: flex; flex-direction: column; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow-y: auto; padding-bottom: 2rem; }
.admin-nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 1.2rem; cursor: pointer; color: var(--text2); font-size: 0.85rem; font-weight: 500; transition: all 0.2s; text-decoration: none; margin: 0 0.5rem; border-radius: 8px; }
.admin-nav-item:hover { color: var(--text); background: rgba(0,0,0,0.03); }
.admin-nav-item.active { color: var(--accent); background: rgba(251,124,0,0.08); font-weight: 600; }
.admin-nav-item i { width: 18px; text-align: center; }
.admin-main { flex: 1; padding: 2rem; overflow-y: auto; }
</style>
</head>
<body>

<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'en,rw', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<div class="admin-layout">
    <div class="admin-sidebar">
        <div style="padding: 1.5rem 1.5rem 0.5rem; text-align: center;">
            <img src="../uploads/Logo.png" alt="ZORA" style="height: 45px; width: auto; object-fit: contain;">
        </div>
        
        <!-- GOOGLE TRANSLATE -->
        <div id="google_translate_element" class="px-3 mb-2 d-flex align-items-center justify-content-center" style="transform: scale(0.9);"></div>
        
        <div class="admin-nav-header">Main</div>
        <a href="index.php" class="admin-nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        
        <div class="admin-nav-header">Catalog</div>
        <a href="products.php" class="admin-nav-item <?= $current_page == 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a>
        <a href="categories.php" class="admin-nav-item <?= $current_page == 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> Categories</a>
        <a href="inventory.php" class="admin-nav-item <?= $current_page == 'inventory.php' ? 'active' : '' ?>"><i class="fas fa-warehouse"></i> Inventory</a>
        
        <div class="admin-nav-header">Sales</div>
        <a href="orders.php" class="admin-nav-item <?= $current_page == 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="payments.php" class="admin-nav-item <?= $current_page == 'payments.php' ? 'active' : '' ?>"><i class="fas fa-credit-card"></i> Payments</a>
        <a href="discounts.php" class="admin-nav-item <?= $current_page == 'discounts.php' ? 'active' : '' ?>"><i class="fas fa-ticket-alt"></i> Discounts & Coupons</a>
        <a href="shipping.php" class="admin-nav-item <?= $current_page == 'shipping.php' ? 'active' : '' ?>"><i class="fas fa-truck"></i> Shipping</a>
        
        <div class="admin-nav-header">Users</div>
        <a href="customers.php" class="admin-nav-item <?= $current_page == 'customers.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Customers</a>
        <a href="support.php" class="admin-nav-item <?= $current_page == 'support.php' ? 'active' : '' ?>"><i class="fas fa-headset"></i> Support</a>
        <a href="roles.php" class="admin-nav-item <?= $current_page == 'roles.php' ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Roles & Permissions</a>
        
        <div class="admin-nav-header">Content & Reports</div>
        <a href="content.php" class="admin-nav-item <?= $current_page == 'content.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Website Content</a>
        <a href="reports.php" class="admin-nav-item <?= $current_page == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Reports & Analytics</a>
        
        <div class="admin-nav-header">System</div>
        <a href="notifications.php" class="admin-nav-item <?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a>
        <a href="settings.php" class="admin-nav-item <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Security & Settings</a>
        
        <div style="margin-top: 1.5rem; margin-bottom: 1rem; padding: 0 1rem;"><hr style="border-color: var(--border); margin: 0;"></div>
        
        <a href="../core/actions.php?action=logout" class="admin-nav-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="admin-main">
        <?php if(isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if(isset($_GET['error'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
