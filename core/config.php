<?php
// config.php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty
$db_name = 'luxe_commerce';

// Create connection without database first to check/create it
$conn = mysqli_connect($db_host, $db_user, $db_pass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $db_name);
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Auto-create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-box'
    )",
    "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255) DEFAULT 'default_product.jpg',
        stock INT DEFAULT 0,
        rating DECIMAL(3,1) DEFAULT 0.0,
        is_featured TINYINT(1) DEFAULT 0,
        is_new TINYINT(1) DEFAULT 0,
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
    )",
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        color VARCHAR(50),
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        color_name VARCHAR(50) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY user_product (user_id, product_id)
    )"
];

foreach ($tables as $sql) {
    mysqli_query($conn, $sql);
}

// Add order_notes column if it doesn't exist
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_notes'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_notes TEXT");
}

// Function to sanitize inputs
function clean_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Check if an admin exists, if not create default admin
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Admin', 'User', 'admin@luxemarket.com', '$admin_pass', 'admin')");
}

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
?>
