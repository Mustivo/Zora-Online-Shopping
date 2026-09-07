<?php
require_once '../core/config.php';

<<<<<<< HEAD
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store_manager'])) {
=======
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
<<<<<<< HEAD

$page_titles = [
    'index.php' => 'Dashboard Overview',
    'products.php' => 'Manage Products',
    'categories.php' => 'Categories',
    'orders.php' => 'Orders',
    'customers.php' => 'Customers',
    'inventory.php' => 'Inventory',
    'settings.php' => 'Settings',
    'delivery_methods.php' => 'Delivery Methods',
    'payments.php' => 'Payments',
    'shipping.php' => 'Shipping',
    'discounts.php' => 'Discounts',
    'support.php' => 'Support',
    'roles.php' => 'Roles & Permissions',
    'content.php' => 'Website Content',
    'reports.php' => 'Reports',
    'notifications.php' => 'Notifications'
];
$page_icons = [
    'index.php' => 'fas fa-tachometer-alt',
    'products.php' => 'fas fa-box',
    'categories.php' => 'fas fa-tags',
    'orders.php' => 'fas fa-shopping-cart',
    'customers.php' => 'fas fa-users',
    'inventory.php' => 'fas fa-warehouse',
    'settings.php' => 'fas fa-cog',
    'delivery_methods.php' => 'fas fa-truck',
    'payments.php' => 'fas fa-credit-card',
    'shipping.php' => 'fas fa-truck',
    'discounts.php' => 'fas fa-ticket-alt',
    'support.php' => 'fas fa-headset',
    'roles.php' => 'fas fa-user-shield',
    'content.php' => 'fas fa-file-alt',
    'reports.php' => 'fas fa-chart-line',
    'notifications.php' => 'fas fa-bell'
];

$role_panel_titles = [
    'admin' => 'Admin Panel',
    'store_manager' => 'Store Manager Panel',
    'rider' => 'Rider Panel'
];
$base_panel_title = $role_panel_titles[$_SESSION['role']] ?? 'Admin Panel';

$admin_page_title = $page_titles[$current_page] ?? $base_panel_title;
$admin_page_icon = $page_icons[$current_page] ?? 'fas fa-cogs';
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<title><?= $base_panel_title ?> - ZORA</title>
<link rel="icon" href="../uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">
<style>
<?php
$font_query = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_font_family'");
$site_font = ($font_query && mysqli_num_rows($font_query) > 0) ? htmlspecialchars_decode(mysqli_fetch_assoc($font_query)['setting_value']) : '';
?>
<?php if (!empty($site_font)): ?>
    *:not(.fa):not(.fas):not(.fab):not(.far):not(.fal):not(.fad):not(.fi) {
        font-family: <?= $site_font ?> !important;
    }
<?php endif; ?>
</style>
<script>
    // Apply dark mode immediately to prevent FOUC
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    // Apply collapsed sidebar immediately
    if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth > 768) {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>
<style>
@media (min-width: 769px) {
    html.sidebar-collapsed .admin-sidebar { width: 80px !important; overflow-x: hidden; }
    html.sidebar-collapsed .admin-nav-header { display: none; }
    html.sidebar-collapsed .admin-nav-item { font-size: 0 !important; justify-content: center; padding: 0.75rem 0 !important; margin: 0.2rem 0.5rem !important; }
    html.sidebar-collapsed .admin-nav-item i { font-size: 1.2rem; margin: 0 !important; }
    html.sidebar-collapsed .sidebar-logo-img { display: none; }
    html.sidebar-collapsed .sidebar-logo-container { padding: 1.5rem 0 0.5rem 0 !important; justify-content: center !important; }
    html.sidebar-collapsed .admin-nav-item:hover::after {
        content: attr(title);
        position: absolute; left: 100%; top: 50%; transform: translateY(-50%);
        background: var(--bg2); border: 1px solid var(--border); color: var(--text);
        padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;
        white-space: nowrap; margin-left: 10px; z-index: 2000; box-shadow: var(--shadow);
    }
}
</style>
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
            cancelButtonText: 'Cancel',
            background: 'var(--card)',
            color: 'var(--text)'
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

    function customAlert(message, type = 'info', title = null) {
        let icon = type;
        let autoTitle = title || (type === 'error' ? 'Error' : (type === 'warning' ? 'Notice' : 'Information'));
        
        Swal.fire({
            title: `<span style="font-weight: 700; color: var(--text);">${autoTitle}</span>`,
            html: `<div style="font-size: 1rem; color: var(--text); line-height: 1.5;">${message}</div>`,
            icon: icon,
            confirmButtonColor: '#2563eb',
            confirmButtonText: '<i class="fas fa-check me-1"></i> Understood',
            background: 'var(--card)',
            color: 'var(--text)',
            customClass: {
                popup: 'rounded-4 shadow-lg border',
                confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-bold'
            },
            buttonsStyling: false
        });
    }

    function showVariantExistsAlert(variantName) {
        Swal.fire({
            title: '<span style="font-weight: 800; font-size: 1.35rem; color: var(--text);">Variation Already Exists</span>',
            html: `
                <div style="margin: 10px 0 15px 0; text-align: center;">
                    <div style="display: inline-block; background: rgba(245, 158, 11, 0.12); border: 1.5px solid rgba(245, 158, 11, 0.35); padding: 6px 20px; border-radius: 50px; margin-bottom: 16px;">
                        <i class="fas fa-palette me-2" style="color: #d97706;"></i>
                        <strong style="color: #d97706; font-size: 1.15rem; letter-spacing: 0.5px;">${variantName}</strong>
                    </div>
                    <p style="font-size: 1.05rem; color: var(--text); margin-bottom: 8px; line-height: 1.6;">
                        A variation named <strong style="color: #2563eb;">"${variantName}"</strong> already exists for this product.
                    </p>
                    <small style="color: var(--text3); font-size: 0.85rem; display: block;">
                        Please choose a different variation name or update the stock of the existing variation.
                    </small>
                </div>
            `,
            icon: 'warning',
            iconColor: '#f59e0b',
            confirmButtonColor: '#2563eb',
            confirmButtonText: '<i class="fas fa-check me-2"></i> Understood',
            background: 'var(--card)',
            color: 'var(--text)',
            customClass: {
                popup: 'rounded-4 shadow-lg border',
                confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-bold'
            },
            buttonsStyling: false
        });
    }
    </script>
</head>
<body>


<script>
function toggleAdminSidebar() {
    var sidebar = document.querySelector('.admin-sidebar');
    var overlay = document.querySelector('.sidebar-overlay');
    
    // Explicit inline styles to bypass any cached CSS issues
    if (sidebar) {
        if (sidebar.style.transform === 'translateX(0px)') {
            sidebar.style.transform = 'translateX(-100%)';
            sidebar.style.boxShadow = 'none';
        } else {
            // Force display in case a media query set it to none
            sidebar.style.setProperty('display', 'flex', 'important');
            // Give it a tiny delay to ensure the display property applies before transforming
            setTimeout(function() {
                sidebar.style.transform = 'translateX(0px)';
                sidebar.style.boxShadow = '10px 0 40px rgba(0,0,0,0.2)';
            }, 10);
        }
    }
    
    if (overlay) {
        if (overlay.style.display === 'block') {
            overlay.style.display = 'none';
            overlay.style.opacity = '0';
            document.body.style.overflow = 'auto';
        } else {
            overlay.style.display = 'block';
            overlay.style.opacity = '1';
            document.body.style.overflow = 'hidden';
        }
    }
}

function toggleDesktopSidebar() {
    document.documentElement.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar_collapsed', document.documentElement.classList.contains('sidebar-collapsed'));
}

document.addEventListener('DOMContentLoaded', function() {
    var activeItem = document.querySelector('.admin-sidebar .admin-nav-item.active');
    if (activeItem) {
        // Scroll the sidebar so the active item is visible
        activeItem.scrollIntoView({ behavior: 'auto', block: 'center' });
    }
});
</script>
<div class="admin-layout">
    <?php
    $pending_orders = [];
    $pending_orders_query = mysqli_query($conn, "SELECT id, total_amount, created_at FROM orders WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5");
    if($pending_orders_query) {
        while($row = mysqli_fetch_assoc($pending_orders_query)) { $pending_orders[] = $row; }
    }
    $pending_count_query = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'pending'");
    $pending_count = $pending_count_query ? (mysqli_fetch_assoc($pending_count_query)['c'] ?? 0) : 0;
    ?>
    <div class="admin-mobile-header d-md-none d-flex justify-content-between align-items-center p-3 border-bottom shadow-sm">
        <img src="../uploads/logo.png" alt="ZORA" style="height: 30px; width: auto; object-fit: contain;">
        
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center justify-content-center position-relative" type="button" id="adminOrderNotificationsMobile" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text); background: var(--bg3); border-radius: 50px; width: 36px; height: 36px; box-shadow: none;" title="Order Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if($pending_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?= $pending_count > 99 ? '99+' : $pending_count ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="adminOrderNotificationsMobile" style="width: 280px; max-height: 400px; overflow-y: auto; z-index: 1050; background: var(--card);">
                    <li><h6 class="dropdown-header text-uppercase fw-bold" style="color: var(--text3);">Pending Orders (<?= $pending_count ?>)</h6></li>
                    <?php if($pending_count > 0): ?>
                        <?php foreach($pending_orders as $n_order): ?>
                            <li>
                                <a class="dropdown-item py-2 d-flex justify-content-between align-items-center" href="orders.php?id=<?= $n_order['id'] ?>" style="color: var(--text);">
                                    <div>
                                        <div class="fw-bold">Order #<?= $n_order['id'] ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= date('M d, H:i', strtotime($n_order['created_at'])) ?></div>
                                    </div>
                                    <div class="fw-bold text-success">
                                        RWF <?= number_format($n_order['total_amount']) ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center text-primary fw-bold" href="orders.php?status=pending">View All</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item text-muted text-center py-3" href="#">No pending orders</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <button onclick="toggleDarkMode()" class="btn btn-sm d-flex align-items-center justify-content-center" style="color: var(--text); background: var(--bg3); border-radius: 50px; width: 36px; height: 36px; box-shadow: none;" title="Toggle Dark Mode">
                <i class="fas fa-moon darkModeIcon"></i>
            </button>

            <button class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" onclick="toggleAdminSidebar()" style="width: 38px; height: 38px;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-overlay" onclick="toggleAdminSidebar()"></div>
    <div class="admin-sidebar">

        <div class="sidebar-logo-container d-none d-md-flex justify-content-between align-items-center" style="padding: 1.5rem 1.5rem 0.5rem;">
            <img src="../uploads/logo.png" alt="ZORA" style="height: 45px; width: auto; object-fit: contain;" class="sidebar-logo-img">
            <button onclick="toggleDesktopSidebar()" class="btn btn-sm text-muted shadow-none p-1"><i class="fas fa-bars"></i></button>
        </div>
        

=======
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
        
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        <div class="admin-nav-header">Main</div>
        <a href="index.php" class="admin-nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        
        <div class="admin-nav-header">Catalog</div>
        <a href="products.php" class="admin-nav-item <?= $current_page == 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a>
<<<<<<< HEAD
        <a href="delivery_methods.php" class="admin-nav-item <?= $current_page == 'delivery_methods.php' ? 'active' : '' ?>"><i class="fas fa-truck"></i> Delivery Methods</a>
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        <a href="categories.php" class="admin-nav-item <?= $current_page == 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> Categories</a>
        <a href="inventory.php" class="admin-nav-item <?= $current_page == 'inventory.php' ? 'active' : '' ?>"><i class="fas fa-warehouse"></i> Inventory</a>
        
        <div class="admin-nav-header">Sales</div>
        <a href="orders.php" class="admin-nav-item <?= $current_page == 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Orders</a>
<<<<<<< HEAD
        <?php
        $trash_badge_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE deleted_at IS NOT NULL");
        $trash_badge_count = $trash_badge_q ? (mysqli_fetch_assoc($trash_badge_q)['c'] ?? 0) : 0;
        ?>
        <a href="trash.php" class="admin-nav-item <?= $current_page == 'trash.php' ? 'active' : '' ?> d-flex justify-content-between align-items-center">
            <span><i class="fas fa-trash-alt"></i> Trash</span>
            <?php if($trash_badge_count > 0): ?>
                <span class="badge rounded-pill bg-danger" style="font-size: 0.7rem;"><?= $trash_badge_count ?></span>
            <?php endif; ?>
        </a>
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        <a href="payments.php" class="admin-nav-item <?= $current_page == 'payments.php' ? 'active' : '' ?>"><i class="fas fa-credit-card"></i> Payments</a>
        <a href="discounts.php" class="admin-nav-item <?= $current_page == 'discounts.php' ? 'active' : '' ?>"><i class="fas fa-ticket-alt"></i> Discounts & Coupons</a>
        <a href="shipping.php" class="admin-nav-item <?= $current_page == 'shipping.php' ? 'active' : '' ?>"><i class="fas fa-truck"></i> Shipping</a>
        
<<<<<<< HEAD
        <div class="admin-nav-header">Users & Employees</div>
        <a href="customers.php" class="admin-nav-item <?= $current_page == 'customers.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Customers</a>
        <a href="riders.php" class="admin-nav-item <?= $current_page == 'riders.php' ? 'active' : '' ?>"><i class="fas fa-motorcycle"></i> Riders</a>
        <a href="support.php" class="admin-nav-item <?= $current_page == 'support.php' ? 'active' : '' ?>"><i class="fas fa-headset"></i> Support</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="roles.php" class="admin-nav-item <?= $current_page == 'roles.php' ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Roles & Permissions</a>
        <?php endif; ?>
        
        <div class="admin-nav-header">Reports</div>
        <a href="reports.php" class="admin-nav-item <?= $current_page == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Reports & Analytics</a>
        
        <div class="admin-nav-header">System</div>
        <a href="content.php" class="admin-nav-item <?= $current_page == 'content.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Website Content</a>
        <a href="notifications.php" class="admin-nav-item <?= $current_page == 'notifications.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="settings.php" class="admin-nav-item <?= $current_page == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Security & Settings</a>
        <?php endif; ?>
        
        <div style="margin-top: 1.5rem; margin-bottom: 1rem; padding: 0 1rem;"><hr style="border-color: var(--border); margin: 0;"></div>
        
        <div class="px-3 mb-2 d-none d-md-block" style="color: var(--text3); font-size: 0.85rem;">
            <i class="<?= $admin_page_icon ?> me-2 text-primary"></i> <span class="fw-bold"><?= $admin_page_title ?></span>
        </div>
        
        <a href="../core/actions.php?action=admin_logout" class="admin-nav-item text-danger" style="margin-bottom: 3rem;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="admin-main">
        <style>
        .admin-main {
            padding-top: 85px !important;
        }
        .dashboard-header-fixed {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            z-index: 1000;
            background: var(--card);
            padding: 1rem 2.5rem;
            border-bottom: 1px solid var(--border);
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        html.sidebar-collapsed .dashboard-header-fixed {
            left: 80px;
        }
        .admin-search-input {
            background: var(--bg3); 
            color: var(--text); 
            box-shadow: none; 
            border-radius: 0 50px 50px 0;
        }
        .admin-search-input::placeholder { color: var(--text3); }
        .admin-search-input:focus { background: var(--bg3); color: var(--text); box-shadow: none; }
        .admin-search-icon {
            background: var(--bg3); 
            color: var(--text3); 
            border-radius: 50px 0 0 50px;
        }
        @media (max-width: 768px) {
            .admin-mobile-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1001;
                background: var(--bg2);
                height: 70px;
            }
            .admin-main {
                padding-top: 240px !important;
            }
            .dashboard-header-fixed {
                left: 0;
                top: 70px;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem !important;
            }
        }
        </style>

        <div class="dashboard-header-fixed d-flex justify-content-between align-items-center shadow-sm">
            <form action="products.php" method="GET" class="d-flex align-items-center gap-3 flex-grow-1 m-0" style="max-width: 400px;">
                <div class="input-group">
                    <input type="text" name="search" class="form-control border-0 admin-search-input" style="border-radius: 50px 0 0 50px; background: var(--bg3); color: var(--text);" placeholder="Search products..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit" class="btn btn-primary" style="border-radius: 0 50px 50px 0; padding: 0 20px;"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown d-none d-md-block">
                    <button class="btn btn-sm d-flex align-items-center justify-content-center position-relative" type="button" id="adminOrderNotifications" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text); background: var(--bg3); border-radius: 50px; width: 36px; height: 36px; box-shadow: none;" title="Order Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if($pending_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?= $pending_count > 99 ? '99+' : $pending_count ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="adminOrderNotifications" style="width: 320px; max-height: 400px; overflow-y: auto; z-index: 1050; background: var(--card);">
                        <li><h6 class="dropdown-header text-uppercase fw-bold" style="color: var(--text3);">Pending Orders (<?= $pending_count ?>)</h6></li>
                        <?php if($pending_count > 0): ?>
                            <?php foreach($pending_orders as $n_order): ?>
                                <li>
                                    <a class="dropdown-item py-2 d-flex justify-content-between align-items-center" href="orders.php?id=<?= $n_order['id'] ?>" style="color: var(--text);">
                                        <div>
                                            <div class="fw-bold">Order #<?= $n_order['id'] ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= date('M d, H:i', strtotime($n_order['created_at'])) ?></div>
                                        </div>
                                        <div class="fw-bold text-success">
                                            RWF <?= number_format($n_order['total_amount']) ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center text-primary fw-bold" href="orders.php?status=pending">View All Pending Orders</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item text-muted text-center py-3" href="#">No pending orders</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <button onclick="toggleDarkMode()" class="btn btn-sm d-none d-md-flex align-items-center justify-content-center" style="color: var(--text); background: var(--bg3); border-radius: 50px; width: 36px; height: 36px; box-shadow: none;" title="Toggle Dark Mode">
                    <i class="fas fa-moon darkModeIcon"></i>
                </button>
                
                <?php 
                $total_in_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(stock), 0) as c FROM products"))['c'] ?? 0;
                $total_stock_out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(oi.quantity), 0) as c FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.status = 'delivered'"))['c'] ?? 0;
                $out_of_stock_prods = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE stock <= 0"))['c'] ?? 0;
                ?>
                <div class="badge border px-3 py-2 shadow-sm d-none d-md-flex flex-column align-items-center" style="background: var(--bg3); border-color: var(--border) !important;" title="Total inventory remaining in stock">
                    <span style="font-size: 0.65rem; text-transform: uppercase; color: var(--text3); font-weight: 600;">In Stock</span>
                    <span class="fw-bold" style="font-size: 1.1rem; color: #10b981;"><?= number_format($total_in_stock) ?></span>
                </div>
                <div class="badge border px-3 py-2 shadow-sm d-none d-md-flex flex-column align-items-center" style="background: var(--bg3); border-color: var(--border) !important;" title="Total units sold & delivered">
                    <span style="font-size: 0.65rem; text-transform: uppercase; color: var(--text3); font-weight: 600;">Stock Out (Sold)</span>
                    <span class="fw-bold" style="font-size: 1.1rem; color: var(--accent);"><?= number_format($total_stock_out) ?></span>
                </div>
                <?php if ($out_of_stock_prods > 0): ?>
                <div class="badge border px-3 py-2 shadow-sm d-none d-md-flex flex-column align-items-center" style="background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.25) !important;" title="Products with 0 stock left">
                    <span style="font-size: 0.65rem; text-transform: uppercase; color: #ef4444; font-weight: 600;">Out of Stock</span>
                    <span class="fw-bold" style="font-size: 1.1rem; color: #ef4444;"><?= number_format($out_of_stock_prods) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php 
            $err_str = $_GET['error'];
            if (preg_match("/Variation ['\"]?([^'\"]+)['\"]? already exists/i", $err_str, $varMatch)):
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof showVariantExistsAlert === 'function') {
                    showVariantExistsAlert(<?= json_encode($varMatch[1]) ?>);
                }
            });
            </script>
            <?php endif; ?>
        <?php endif; ?>
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
