<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=" . urlencode("Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_order') {
        $order_id = (int)$_POST['order_id'];
        $status = clean_input($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
        header("Location: orders.php?msg=" . urlencode("Order #$order_id updated."));
        exit;
    }

    if ($action === 'add_product') {
        $name = clean_input($conn, $_POST['name']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $cat_id = (int)$_POST['category_id'];
        $desc = clean_input($conn, $_POST['description']);
        
        $image = 'default_product.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $imageName)) {
                $image = $imageName;
            }
        }
        
        mysqli_query($conn, "INSERT INTO products (category_id, name, description, price, stock, image) VALUES ($cat_id, '$name', '$desc', $price, $stock, '$image')");
        header("Location: products.php?msg=" . urlencode("Product added successfully."));
        exit;
    }

    if ($action === 'edit_product') {
        $product_id = (int)$_POST['product_id'];
        $name = clean_input($conn, $_POST['name']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $cat_id = (int)$_POST['category_id'];
        $desc = clean_input($conn, $_POST['description']);
        
        $imageUpdate = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $imageName)) {
                $imageUpdate = ", image = '$imageName'";
            }
        }
        
        mysqli_query($conn, "UPDATE products SET category_id=$cat_id, name='$name', description='$desc', price=$price, stock=$stock $imageUpdate WHERE id=$product_id");
        header("Location: products.php?msg=" . urlencode("Product updated successfully."));
        exit;
    }

    if ($action === 'delete_product') {
        $product_id = (int)$_POST['product_id'];
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
        header("Location: products.php?msg=" . urlencode("Product deleted."));
        exit;
    }

    if ($action === 'add_category') {
        $name = clean_input($conn, $_POST['name']);
        $icon = clean_input($conn, $_POST['icon']) ?: 'fas fa-box';
        mysqli_query($conn, "INSERT INTO categories (name, icon) VALUES ('$name', '$icon')");
        header("Location: categories.php?msg=" . urlencode("Category added."));
        exit;
    }
    
    if ($action === 'edit_category') {
        $cat_id = (int)$_POST['category_id'];
        $name = clean_input($conn, $_POST['name']);
        $icon = clean_input($conn, $_POST['icon']) ?: 'fas fa-box';
        mysqli_query($conn, "UPDATE categories SET name='$name', icon='$icon' WHERE id=$cat_id");
        header("Location: categories.php?msg=" . urlencode("Category updated."));
        exit;
    }

    if ($action === 'delete_category') {
        $cat_id = (int)$_POST['category_id'];
        mysqli_query($conn, "DELETE FROM categories WHERE id = $cat_id");
        header("Location: categories.php?msg=" . urlencode("Category deleted."));
        exit;
    }

    if ($action === 'add_product_color') {
        $product_id = (int)$_POST['product_id'];
        $color_name = clean_input($conn, $_POST['color_name']);
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $imageName)) {
                mysqli_query($conn, "INSERT INTO product_images (product_id, color_name, image_path) VALUES ($product_id, '$color_name', '$imageName')");
                header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Color image added."));
                exit;
            }
        }
        header("Location: product_colors.php?id=$product_id&error=" . urlencode("Failed to upload image."));
        exit;
    }

    if ($action === 'delete_product_color') {
        $image_id = (int)$_POST['image_id'];
        $product_id = (int)$_POST['product_id'];
        
        $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE id = $image_id");
        if ($row = mysqli_fetch_assoc($res)) {
            $path = '../uploads/' . $row['image_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        mysqli_query($conn, "DELETE FROM product_images WHERE id = $image_id");
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Color image deleted."));
        exit;
    }
}
