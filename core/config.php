<?php
// config.php
<<<<<<< HEAD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Africa/Kigali');

// Set your base URL here for deployment (e.g., 'https://yourdomain.com/' or 'http://localhost/Commerce/')
define('BASE_URL', 'http://localhost/commerce/');
$host = BASE_URL;

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'zora_shop';
=======
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty
$db_name = 'luxe_commerce';
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

// Create connection without database first to check/create it
$conn = mysqli_connect($db_host, $db_user, $db_pass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
<<<<<<< HEAD
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $db_name);
    @mysqli_query($conn, "SET time_zone = '+02:00'");
=======
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $db_name);
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
} else {
    die("Error creating database: " . mysqli_error($conn));
}

<<<<<<< HEAD
// Language system
if (isset($_GET['lang'])) {
    $allowed_langs = ['en', 'rw'];
    if (in_array($_GET['lang'], $allowed_langs)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$is_rw = ($lang === 'rw');

// Load translations
$translations = [];
$lang_file = __DIR__ . '/../includes/lang/' . $lang . '.php';
if (file_exists($lang_file)) {
    $translations = require $lang_file;
} else {
    $lang_file_en = __DIR__ . '/../includes/lang/en.php';
    if (file_exists($lang_file_en)) {
        $translations = require $lang_file_en;
    }
}

if (!function_exists('__')) {
    function __($key, $replacements = []) {
        global $translations;
        $text = isset($translations[$key]) ? $translations[$key] : $key;
        if (!empty($replacements)) {
            foreach ($replacements as $k => $v) {
                $text = str_replace(':' . $k, $v, $text);
            }
        }
        return $text;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $conn, $settings;
        if (!isset($settings)) {
            $settings = [];
            if (isset($conn) && $conn) {
                $settings_q = @mysqli_query($conn, "SELECT * FROM settings");
                if ($settings_q) {
                    while($row = mysqli_fetch_assoc($settings_q)) {
                        $settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            }
        }
        return htmlspecialchars($settings[$key] ?? $default);
    }
}

=======
// Set charset
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
mysqli_set_charset($conn, "utf8mb4");

// Auto-create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
<<<<<<< HEAD
        role ENUM('user', 'admin', 'store_manager', 'rider') DEFAULT 'user',
        profile_picture VARCHAR(255) DEFAULT NULL,
        reset_token VARCHAR(100) DEFAULT NULL,
        reset_expires DATETIME DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
=======
        role ENUM('user', 'admin') DEFAULT 'user',
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
<<<<<<< HEAD
        parent_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-box',
        image VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
=======
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-box'
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    )",
    "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
<<<<<<< HEAD
        discount_price DECIMAL(10,2) DEFAULT NULL,
        discount_expiry DATETIME DEFAULT NULL,
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        image VARCHAR(255) DEFAULT 'default_product.jpg',
        stock INT DEFAULT 0,
        rating DECIMAL(3,1) DEFAULT 0.0,
        is_featured TINYINT(1) DEFAULT 0,
        is_new TINYINT(1) DEFAULT 0,
<<<<<<< HEAD
        tags VARCHAR(255) DEFAULT '',
        sizes VARCHAR(255) DEFAULT NULL,
        colors VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        INDEX idx_category_id (category_id),
        INDEX idx_is_featured (is_featured),
        INDEX idx_is_new (is_new),
        INDEX idx_created_at (created_at)
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE,
        user_id INT NULL,
        rider_id INT NULL,
        delivered_by INT NULL,
        guest_email VARCHAR(100) NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_name VARCHAR(100) NULL,
        shipping_phone VARCHAR(20) NULL,
        shipping_address TEXT NULL,
        shipping_city VARCHAR(100) NULL,
        shipping_state VARCHAR(100) NULL,
        shipping_zip VARCHAR(20) NULL,
        shipping_country VARCHAR(100) NULL,
        shipping_province INT NULL,
        shipping_district INT NULL,
        shipping_sector INT NULL,
        shipping_cell INT NULL,
        shipping_village INT NULL,
        shipping_gate VARCHAR(255) NULL,
        shipping_method VARCHAR(50) NULL,
        delivery_method_id INT NULL,
        payment_method VARCHAR(50) NULL,
        status ENUM('Pending', 'Processing', 'Shipped', 'Delivery Requested', 'Delivered', 'Cancelled', 'Declined', 'Failed') DEFAULT 'Pending',
        delivery_deadline DATETIME DEFAULT NULL,
        delivery_proof VARCHAR(255) NULL,
        order_notes TEXT NULL,
        stock_deducted TINYINT(1) DEFAULT 0,
        restocked TINYINT(1) DEFAULT 0,
        deleted_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (delivered_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_rider_id (rider_id),
        INDEX idx_status (status),
        INDEX idx_deleted_at (deleted_at),
        INDEX idx_deadline (delivery_deadline),
        INDEX idx_created_at (created_at)
=======
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_name VARCHAR(100),
        shipping_address TEXT,
        shipping_city VARCHAR(100),
        shipping_state VARCHAR(100),
        shipping_zip VARCHAR(20),
        shipping_country VARCHAR(100),
        shipping_method VARCHAR(50),
        payment_method VARCHAR(50),
        status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    )",
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
<<<<<<< HEAD
        size VARCHAR(50) DEFAULT NULL,
        color VARCHAR(50) DEFAULT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
        INDEX idx_order_id (order_id),
        INDEX idx_product_id (product_id)
=======
        color VARCHAR(50),
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    )",
    "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        color_name VARCHAR(50) NOT NULL,
<<<<<<< HEAD
        sizes TEXT NULL,
        image_path VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id)
=======
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    )",
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
<<<<<<< HEAD
        UNIQUE KEY uniq_user_product (user_id, product_id),
        INDEX idx_product_id (product_id)
    )",
    "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id),
        INDEX idx_user_id (user_id)
    )",
    "CREATE TABLE IF NOT EXISTS rwanda_locations (
        id INT PRIMARY KEY,
        type ENUM('province', 'district', 'sector', 'cell', 'village') NOT NULL,
        parent_id INT DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        delivery_fee DECIMAL(10,2) DEFAULT 0.00,
        FOREIGN KEY (parent_id) REFERENCES rwanda_locations(id) ON DELETE CASCADE,
        INDEX idx_parent_id (parent_id),
        INDEX idx_type (type)
    )",
    "CREATE TABLE IF NOT EXISTS delivery_methods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS kigali_streets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        district VARCHAR(100) NOT NULL,
        district_id INT DEFAULT NULL,
        sector VARCHAR(100) NOT NULL,
        sector_id INT DEFAULT NULL,
        area VARCHAR(100) DEFAULT NULL,
        fee DECIMAL(10,2) NOT NULL DEFAULT 1500.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NULL DEFAULT NULL,
        code VARCHAR(50) NOT NULL UNIQUE,
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        expiry_date DATE,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id)
    )",
    "CREATE TABLE IF NOT EXISTS support_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    )",
    "CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subtitle TEXT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT,
        banner_image VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '#',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_is_read (is_read)
    )",
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT
    )",
    "CREATE TABLE IF NOT EXISTS product_inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        color_name VARCHAR(100) NOT NULL DEFAULT 'Base',
        size_name VARCHAR(50) NOT NULL DEFAULT '',
        price DECIMAL(10,2) NULL DEFAULT NULL,
        stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id)
    )",
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL UNIQUE,
        user_id INT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(100) NOT NULL DEFAULT 'Cash on Delivery',
        transaction_id VARCHAR(100) NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Paid',
        payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS user_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        message VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '#',
        expires_at DATETIME DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_read (user_id, is_read)
=======
        UNIQUE KEY user_product (user_id, product_id)
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    )"
];

foreach ($tables as $sql) {
    mysqli_query($conn, $sql);
}

<<<<<<< HEAD
=======
// Add order_notes column if it doesn't exist
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_notes'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_notes TEXT");
}

>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
// Function to sanitize inputs
function clean_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

<<<<<<< HEAD
// Check if delivery methods are empty, if so insert defaults
$dm_check = mysqli_query($conn, "SELECT id FROM delivery_methods LIMIT 1");
if ($dm_check && mysqli_num_rows($dm_check) == 0) {
    mysqli_query($conn, "INSERT INTO delivery_methods (name, price) VALUES ('Standard Delivery', 2000), ('Express Delivery', 5000), ('Pick Up In Store', 0)");
}

// Check if an admin exists, if not create default admin
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($admin_check && mysqli_num_rows($admin_check) == 0) {
=======
// Check if an admin exists, if not create default admin
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if (mysqli_num_rows($admin_check) == 0) {
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@luxemarket.com', '$admin_pass', 'admin')");
}

<<<<<<< HEAD
// Seed default pages if not exist
$default_pages = [
    [
        'slug' => 'about',
        'title' => 'About Us',
        'subtitle' => 'Learn more about Zora Shop Rwanda and our commitment to bringing you the finest shopping experience.',
        'content' => ''
    ],
    [
        'slug' => 'how_to_order',
        'title' => 'How to Order',
        'subtitle' => 'Follow these simple steps to place your order and get your products delivered fast.',
        'content' => ''
    ],
    [
        'slug' => 'returns',
        'title' => 'Returns & Refunds',
        'subtitle' => 'Our return policy is designed to give you peace of mind with every purchase.',
        'content' => ''
    ],
    [
        'slug' => 'privacy',
        'title' => 'Privacy Policy',
        'subtitle' => 'How we collect, protect, and handle your personal information.',
        'content' => ''
    ],
    [
        'slug' => 'terms',
        'title' => 'Terms of Service',
        'subtitle' => 'Terms and conditions governing the use of Zora Shop services.',
        'content' => ''
    ]
];

foreach ($default_pages as $dp) {
    $pslug = $dp['slug'];
    $chk_p = mysqli_query($conn, "SELECT id FROM pages WHERE slug = '$pslug' LIMIT 1");
    if ($chk_p && mysqli_num_rows($chk_p) == 0) {
        $ptitle = clean_input($conn, $dp['title']);
        $psub = clean_input($conn, $dp['subtitle']);
        $pcont = clean_input($conn, $dp['content']);
        @mysqli_query($conn, "INSERT INTO pages (title, subtitle, slug, content) VALUES ('$ptitle', '$psub', '$pslug', '$pcont')");
    }
}

// Apply expired discount cleanup
mysqli_query($conn, "UPDATE products SET discount_price = NULL, discount_expiry = NULL WHERE discount_expiry IS NOT NULL AND discount_expiry < NOW()");

// Cleanup expired notifications
mysqli_query($conn, "DELETE FROM user_notifications WHERE expires_at IS NOT NULL AND expires_at < NOW()");

// Lazy evaluation for expired orders
$expired_orders_res = mysqli_query($conn, "SELECT id, stock_deducted, restocked FROM orders WHERE status NOT IN ('Cancelled', 'Declined', 'Failed', 'Delivered') AND delivery_deadline IS NOT NULL AND delivery_deadline < NOW()");
if ($expired_orders_res && mysqli_num_rows($expired_orders_res) > 0) {
    while ($expired_order = mysqli_fetch_assoc($expired_orders_res)) {
        $order_id = (int)$expired_order['id'];
        
        // Restock items if they were deducted and not yet restocked
        if ($expired_order['stock_deducted'] == 1 && $expired_order['restocked'] == 0) {
            $items_res = mysqli_query($conn, "SELECT product_id, color, size, quantity FROM order_items WHERE order_id = $order_id");
            if ($items_res) {
                while ($itm = mysqli_fetch_assoc($items_res)) {
                    $pid = (int)$itm['product_id'];
                    $qty = (int)$itm['quantity'];
                    $color = $itm['color'] ?? '';
                    $size = $itm['size'] ?? '';
                    mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $pid");
                    if (!empty($color) || !empty($size)) {
                        $where_inv = "product_id = $pid";
                        if (!empty($color)) $where_inv .= " AND color_name = '$color'";
                        if (!empty($size)) $where_inv .= " AND size_name = '$size'";
                        mysqli_query($conn, "UPDATE product_inventory SET stock = stock + $qty WHERE $where_inv");
                    }
                }
            }
            mysqli_query($conn, "UPDATE orders SET restocked = 1 WHERE id = $order_id");
        }

        // Mark as cancelled
        mysqli_query($conn, "UPDATE orders SET status = 'Cancelled' WHERE id = $order_id");
        
        // Notify Admin
        $notif_msg = "Order #$order_id was automatically cancelled due to delivery deadline expiration.";
        mysqli_query($conn, "INSERT INTO admin_notifications (message, link) VALUES ('$notif_msg', 'orders.php')");
    }
}

=======
// Insert default categories if none exist
$cat_check = mysqli_query($conn, "SELECT id FROM categories LIMIT 1");
if (mysqli_num_rows($cat_check) == 0) {
    mysqli_query($conn, "INSERT INTO categories (name, icon) VALUES ('Electronics', 'fas fa-laptop'), ('Fashion', 'fas fa-tshirt'), ('Home', 'fas fa-home'), ('Beauty', 'fas fa-spa'), ('Sports', 'fas fa-football-ball'), ('Toys', 'fas fa-gamepad'), ('Books', 'fas fa-book')");
    
    // Insert default products
    mysqli_query($conn, "INSERT INTO products (category_id, name, description, price, stock, is_featured) VALUES 
        (1, 'Premium Wireless Headphones', 'High quality noise cancelling headphones', 299.99, 50, 1),
        (2, 'Classic Leather Watch', 'Elegant timepiece for any occasion', 150.00, 30, 1),
        (1, 'Smartphone Pro Max', 'Latest generation smartphone', 999.00, 20, 1),
        (3, 'Minimalist Desk Lamp', 'Modern LED desk lamp', 45.00, 100, 1)");
}
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
?>
