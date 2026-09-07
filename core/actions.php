<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $first_name = clean_input($conn, $_POST['first_name']);
        $last_name = clean_input($conn, $_POST['last_name']);
        $email = clean_input($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            header("Location: ../index.php?error=" . urlencode("Email already exists."));
            exit;
        }

        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['first_name'] = $first_name;
            $_SESSION['role'] = 'user';
            header("Location: ../user_panel.php?msg=" . urlencode("Registration successful."));
        } else {
            header("Location: ../index.php?error=" . urlencode("Registration failed."));
        }
        exit;
    }

    if ($action === 'login') {
        $email = clean_input($conn, $_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT id, first_name, password, role FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php?msg=" . urlencode("Welcome Admin!"));
                } else {
                    header("Location: ../index.php?msg=" . urlencode("Login successful."));
                }
                exit;
            }
        }
        header("Location: ../index.php?error=" . urlencode("Invalid email or password."));
        exit;
    }

    if ($action === 'admin_login') {
        $email = clean_input($conn, $_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT id, first_name, password, role FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'admin') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: ../admin/index.php?msg=" . urlencode("Welcome Admin!"));
                    exit;
                } else {
                    header("Location: ../admin/login.php?error=" . urlencode("Access denied. Admin only."));
                    exit;
                }
            }
        }
        header("Location: ../admin/login.php?error=" . urlencode("Invalid email or password."));
        exit;
    }

    if ($action === 'add_to_cart') {
        $product_id = (int)$_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $color = isset($_POST['color']) ? clean_input($conn, $_POST['color']) : '';

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        } else {
            // Upgrade old cart format if necessary
            $first_key = array_key_first($_SESSION['cart']);
            if ($first_key !== null && !is_array($_SESSION['cart'][$first_key])) {
                $_SESSION['cart'] = [];
            }
        }

        $cart_key = $product_id . ($color !== '' ? '_' . $color : '');

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'color' => $color
            ];
        }

        header("Location: ../cart_view.php?msg=" . urlencode("Product added to cart."));
        exit;
    }

    if ($action === 'toggle_wishlist') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'unauthorized']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $product_id = (int)$_POST['product_id'];

        $check = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
            echo json_encode(['status' => 'success', 'action' => 'removed']);
        } else {
            mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)");
            echo json_encode(['status' => 'success', 'action' => 'added']);
        }
        exit;
    }
}

// GET actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'logout') {
        session_destroy();
        header("Location: ../index.php?msg=" . urlencode("Logged out successfully."));
        exit;
    }
}
?>
