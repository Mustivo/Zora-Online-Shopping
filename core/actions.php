<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $first_name = clean_input($conn, $_POST['first_name']);
        $last_name = clean_input($conn, $_POST['last_name']);
        $email = clean_input($conn, $_POST['email']);
        $raw_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($raw_password !== $confirm_password) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
                exit;
            }
            header("Location: ../index.php?error=" . urlencode("Passwords do not match."));
            exit;
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $raw_password)) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long, and contain at least 1 uppercase letter, 1 number, and 1 special character.']);
                exit;
            }
            header("Location: ../index.php?error=" . urlencode("Weak password provided."));
            exit;
        }

        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Check if email exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
                exit;
            }
            header("Location: ../index.php?error=" . urlencode("Email already exists."));
            exit;
        }

        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$password')";
        if (mysqli_query($conn, $sql)) {
            // Clear any previous guest cart
            unset($_SESSION['cart']);

            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['first_name'] = $first_name;
            $_SESSION['role'] = 'user';
            
            $notif_msg = clean_input($conn, "New user registered: $first_name $last_name ($email)");
            mysqli_query($conn, "INSERT INTO admin_notifications (message, link) VALUES ('$notif_msg', 'customers.php')");
            
            // Send welcome email
            require_once 'mailer.php';
            $account_url = BASE_URL . "user_panel.php";
            
            $subject = "Welcome to Zora Online Shopping Rwanda!";
            $html = "<div style='text-align: center; margin-bottom: 24px;'>";
            $html .= "<h2 style='margin: 0 0 8px; color: #012a5e; font-size: 24px; font-weight: 800;'>Welcome, " . htmlspecialchars($first_name) . "!</h2>";
            $html .= "<p style='margin: 0; color: #64748b; font-size: 15px;'>Your account has been successfully created.</p>";
            $html .= "</div>";
            $html .= "<p>Thank you for joining <strong>Zora Online Shopping Rwanda</strong>! You can now browse our wide selection of fashion, electronics, and lifestyle products with fast delivery across Rwanda.</p>";
            $html .= "<div style='text-align: center; margin: 35px 0 15px;'>";
            $html .= "<a href='" . $account_url . "' style='background-color: #fb7c00; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(251, 124, 0, 0.3);'>Go to My Account &rarr;</a>";
            $html .= "</div>";
            sendMail($email, "$first_name $last_name", $subject, $html);
            
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'success', 'message' => 'Registration successful.', 'role' => 'user']);
                exit;
            }
            header("Location: ../user_panel.php?msg=" . urlencode("Registration successful."));
        } else {
            if (isset($_POST['ajax'])) {
                echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
                exit;
            }
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
                // Clear any previous guest cart so the logged-in user doesn't see what the guest added
                unset($_SESSION['cart']);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];
                
                if (isset($_POST['ajax'])) {
                    echo json_encode(['status' => 'success', 'message' => 'Login successful.', 'role' => $user['role']]);
                    exit;
                }
                
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php?msg=" . urlencode("Welcome Admin!"));
                } else {
                    header("Location: ../index.php?msg=" . urlencode("Login successful."));
                }
                exit;
            }
        }
        
        if (isset($_POST['ajax'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
            exit;
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
                if (in_array($user['role'], ['admin', 'store_manager', 'rider'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['role'];
                    
                    if ($user['role'] === 'rider') {
                        header("Location: ../admin/rider_dashboard.php?msg=" . urlencode("Welcome Rider!"));
                    } else {
                        header("Location: ../admin/index.php?msg=" . urlencode("Welcome " . ucfirst($user['role']) . "!"));
                    }
                    exit;
                } else {
                    header("Location: ../admin/login.php?error=" . urlencode("Access denied. Authorized personnel only."));
                    exit;
                }
            }
        }
        header("Location: ../admin/login.php?error=" . urlencode("Invalid email or password."));
        exit;
    }

    if ($action === 'forgot_password') {
        $email = clean_input($conn, $_POST['email']);
        
        $sql = "SELECT id, first_name FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        if ($user = mysqli_fetch_assoc($result)) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            mysqli_query($conn, "UPDATE users SET reset_token = '$token', reset_expires = '$expires' WHERE id = " . $user['id']);
            
            require_once 'mailer.php';
            $reset_link = BASE_URL . "reset_password.php?token=" . $token;
            $subject = "Password Reset Request - Zora";
            $html = "<div style='text-align: center; margin-bottom: 24px;'>";
            $html .= "<h2 style='margin: 0 0 8px; color: #012a5e; font-size: 22px; font-weight: 800;'>Password Reset Request</h2>";
            $html .= "<p style='margin: 0; color: #64748b; font-size: 14px;'>Secure your account</p>";
            $html .= "</div>";
            $html .= "<p>Hello <strong>" . htmlspecialchars($user['first_name']) . "</strong>,</p>";
            $html .= "<p>We received a request to reset the password for your Zora account. Click the button below to set a new password. For your security, this link expires in <strong>1 hour</strong>.</p>";
            $html .= "<div style='text-align: center; margin: 35px 0 20px;'>";
            $html .= "<a href='" . $reset_link . "' style='background-color: #012a5e; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(1, 42, 94, 0.25);'>Reset Password</a>";
            $html .= "</div>";
            $html .= "<p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you did not request a password reset, you can safely ignore this email.</p>";
            sendMail($email, $user['first_name'], $subject, $html);
            header("Location: ../forgot_password.php?msg=" . urlencode("A password reset link has been sent to your email."));
        } else {
            header("Location: ../forgot_password.php?error=" . urlencode("Email does not exist."));
        }
        exit;
    }

    if ($action === 'reset_password') {
        $token = clean_input($conn, $_POST['token']);
        $raw_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($raw_password !== $confirm_password) {
            header("Location: ../reset_password.php?token=$token&error=" . urlencode("Passwords do not match."));
            exit;
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $raw_password)) {
            header("Location: ../reset_password.php?token=$token&error=" . urlencode("Password must be at least 8 characters long, containing at least 1 uppercase letter, 1 number, and 1 special character."));
            exit;
        }

        $query = mysqli_query($conn, "SELECT id, email, first_name FROM users WHERE reset_token = '$token' AND reset_expires > NOW()");
        if ($user = mysqli_fetch_assoc($query)) {
            $password = password_hash($raw_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$password', reset_token = NULL, reset_expires = NULL WHERE id = " . $user['id']);
            
            require_once 'mailer.php';
            $subject = "Your Password Has Been Reset - Zora";
            $login_url = BASE_URL . "index.php";
            
            $html = "<div style='text-align: center; margin-bottom: 24px;'>";
            $html .= "<h2 style='margin: 0 0 8px; color: #012a5e; font-size: 22px; font-weight: 800;'>Password Reset Successful</h2>";
            $html .= "<p style='margin: 0; color: #16a34a; font-size: 14px; font-weight: 600;'>&#10004; Account Security Updated</p>";
            $html .= "</div>";
            $html .= "<p>Hello <strong>" . htmlspecialchars($user['first_name']) . "</strong>,</p>";
            $html .= "<p>Your password has been successfully updated. You can now log into your account using your new password.</p>";
            $html .= "<div style='text-align: center; margin: 35px 0 15px;'>";
            $html .= "<a href='" . $login_url . "' style='background-color: #fb7c00; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(251, 124, 0, 0.3);'>Log In Now &rarr;</a>";
            $html .= "</div>";
            sendMail($user['email'], $user['first_name'], $subject, $html);
            
            header("Location: ../index.php?msg=" . urlencode("Password reset successful. Please log in."));
        } else {
            header("Location: ../reset_password.php?token=$token&error=" . urlencode("Invalid or expired reset token."));
        }
        exit;
    }

    if ($action === 'update_profile') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php?error=" . urlencode("Unauthorized."));
            exit;
        }
        
        $user_id = (int)$_SESSION['user_id'];
        $first_name = clean_input($conn, $_POST['first_name']);
        $last_name = clean_input($conn, $_POST['last_name']);
        $new_password = $_POST['new_password'] ?? '';
        
        $update_parts = ["first_name = '$first_name'", "last_name = '$last_name'"];
        
        if (!empty($new_password)) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
                header("Location: ../user_panel.php?error=" . urlencode("Weak password provided."));
                exit;
            }
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_parts[] = "password = '$hash'";
        }
        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . strtolower($ext);
            
            // Fetch old profile picture before updating
            $oldRes = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id = $user_id");
            $oldPic = ($oldRes && $row = mysqli_fetch_assoc($oldRes)) ? $row['profile_picture'] : '';
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], '../uploads/' . $filename)) {
                $update_parts[] = "profile_picture = '$filename'";
                $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
                if (!empty($oldPic) && $oldPic !== $filename && !in_array(strtolower($oldPic), $protected_files)) {
                    $oldPath = '../uploads/' . $oldPic;
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }
        
        $sql = "UPDATE users SET " . implode(', ', $update_parts) . " WHERE id = $user_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['first_name'] = $first_name; // update session name
            header("Location: ../user_panel.php?msg=" . urlencode("Profile updated successfully."));
        } else {
            header("Location: ../user_panel.php?error=" . urlencode("Failed to update profile."));
        }
        exit;
    }

    if ($action === 'add_to_cart' || $action === 'buy_now') {
        // Check global order status
        $status_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_status'");
        $order_status = ($status_q && mysqli_num_rows($status_q) > 0) ? mysqli_fetch_assoc($status_q)['setting_value'] : 'enable';
        
        if ($order_status === 'disable') {
            $msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_message'");
            $disable_msg = ($msg_q && mysqli_num_rows($msg_q) > 0) ? mysqli_fetch_assoc($msg_q)['setting_value'] : 'Ordering is temporarily disabled.';
            header("Location: ../product.php?id=" . (int)$_POST['product_id'] . "&error=" . urlencode($disable_msg));
            exit;
        }

        $product_id = (int)$_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $color = isset($_POST['color']) ? clean_input($conn, $_POST['color']) : '';
        $size = isset($_POST['size']) ? clean_input($conn, $_POST['size']) : '';

        // Validate required color selection if product has genuine color options
        $has_colors_q = mysqli_query($conn, "SELECT id FROM product_images WHERE product_id = $product_id AND LOWER(TRIM(color_name)) NOT IN ('', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null') LIMIT 1");
        $has_colors = ($has_colors_q && mysqli_num_rows($has_colors_q) > 0);
        if (!$has_colors) {
            $p_colors_q = mysqli_query($conn, "SELECT colors FROM products WHERE id = $product_id");
            if ($p_colors_q && $p_row = mysqli_fetch_assoc($p_colors_q)) {
                $raw_c = trim($p_row['colors'] ?? '');
                if ($raw_c !== '' && $raw_c !== '[]' && $raw_c !== '[""]') {
                    $decoded_c = json_decode($raw_c, true);
                    if (is_array($decoded_c)) {
                        foreach ($decoded_c as $dc) {
                            $val = is_array($dc) ? ($dc['value'] ?? '') : $dc;
                            $val_clean = strtolower(trim($val));
                            if (!in_array($val_clean, ['', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null'])) {
                                $has_colors = true;
                                break;
                            }
                        }
                    } else {
                        $parts = array_filter(array_map('trim', explode(',', $raw_c)));
                        foreach ($parts as $part) {
                            if (!in_array(strtolower($part), ['', 'default', 'image', 'gallery', 'photo', 'default image', 'none', 'standard', 'null'])) {
                                $has_colors = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($has_colors && empty($color)) {
            header("Location: ../product.php?id=$product_id&error=" . urlencode(__('please_select_color')));
            exit;
        }

        // Validate required size selection if product has genuine size options
        $c_name_esc = clean_input($conn, $color !== '' ? $color : 'Base');
        $has_inv_q = mysqli_query($conn, "SELECT id FROM product_inventory WHERE product_id = $product_id LIMIT 1");
        $has_inv_records = ($has_inv_q && mysqli_num_rows($has_inv_q) > 0);
        
        $has_sizes = false;
        if ($has_inv_records) {
            $has_sizes_q = mysqli_query($conn, "SELECT id FROM product_inventory WHERE product_id = $product_id AND (color_name = '$c_name_esc' OR color_name = 'Base' OR color_name = 'Default Image') AND size_name IS NOT NULL AND TRIM(size_name) != '' AND LOWER(TRIM(size_name)) NOT IN ('standard', 'default', 'none') LIMIT 1");
            $has_sizes = ($has_sizes_q && mysqli_num_rows($has_sizes_q) > 0);
        } else {
            $p_sizes_q = mysqli_query($conn, "SELECT sizes FROM products WHERE id = $product_id");
            if ($p_sizes_q && $p_row = mysqli_fetch_assoc($p_sizes_q)) {
                $raw_s = trim($p_row['sizes'] ?? '');
                if ($raw_s !== '' && $raw_s !== '[]' && $raw_s !== '[""]') {
                    $decoded_s = json_decode($raw_s, true);
                    if (is_array($decoded_s) && count($decoded_s) > 0) {
                        foreach ($decoded_s as $ds) {
                            $ds_val = is_array($ds) ? ($ds['value'] ?? ($ds['size'] ?? '')) : $ds;
                            $ds_clean = strtolower(trim((string)$ds_val));
                            if ($ds_clean !== '' && !in_array($ds_clean, ['standard', 'default', 'none'])) {
                                $has_sizes = true;
                                break;
                            }
                        }
                    } else if (!is_array($decoded_s)) {
                        $parts = array_filter(array_map('trim', explode(',', $raw_s)));
                        foreach ($parts as $part) {
                            $part_clean = strtolower(trim($part));
                            if ($part_clean !== '' && !in_array($part_clean, ['standard', 'default', 'none'])) {
                                $has_sizes = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($has_sizes && empty($size)) {
            header("Location: ../product.php?id=$product_id&error=" . urlencode(__('please_select_size')));
            exit;
        }

        // Fetch optional custom price for this color variation
        $variant_custom_price = null;
        if (!empty($color)) {
            $c_esc = clean_input($conn, $color);
            $var_p_q = mysqli_query($conn, "SELECT price FROM product_inventory WHERE product_id = $product_id AND color_name = '$c_esc' AND price IS NOT NULL AND price > 0 LIMIT 1");
            if ($var_p_q && $var_p_row = mysqli_fetch_assoc($var_p_q)) {
                $variant_custom_price = (float)$var_p_row['price'];
            } else {
                $img_p_q = mysqli_query($conn, "SELECT price FROM product_images WHERE product_id = $product_id AND color_name = '$c_esc' AND price IS NOT NULL AND price > 0 LIMIT 1");
                if ($img_p_q && $img_p_row = mysqli_fetch_assoc($img_p_q)) {
                    $variant_custom_price = (float)$img_p_row['price'];
                }
            }
        }

        if (!isset($_SESSION['cart']) || $action === 'buy_now') {
            $_SESSION['cart'] = [];
        } else {
            // Upgrade old cart format if necessary
            $first_key = array_key_first($_SESSION['cart']);
            if ($first_key !== null && !is_array($_SESSION['cart'][$first_key])) {
                $_SESSION['cart'] = [];
            }
        }

        $cart_key = $product_id . ($color !== '' ? '_' . $color : '') . ($size !== '' ? '_' . $size : '');
        
        $current_cart_qty = isset($_SESSION['cart'][$cart_key]) ? (int)$_SESSION['cart'][$cart_key]['quantity'] : 0;
        $requested_qty = $current_cart_qty + $quantity;
        
        // Validate stock
        $available_stock = 0;
        if ($size !== '') {
            $cName = $color !== '' ? $color : 'Base';
            $sName = $size;
            $inv_q = mysqli_query($conn, "SELECT stock FROM product_inventory WHERE product_id = $product_id AND color_name = '$cName' AND size_name = '$sName'");
            if ($inv_q && mysqli_num_rows($inv_q) > 0) {
                $available_stock = (int)mysqli_fetch_assoc($inv_q)['stock'];
            } else {
                $inv_q_fb = mysqli_query($conn, "SELECT stock FROM product_inventory WHERE product_id = $product_id AND size_name = '$sName'");
                if ($inv_q_fb && mysqli_num_rows($inv_q_fb) > 0) {
                    $available_stock = (int)mysqli_fetch_assoc($inv_q_fb)['stock'];
                } else {
                    $p_q = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
                    if ($p_q && mysqli_num_rows($p_q) > 0) $available_stock = (int)mysqli_fetch_assoc($p_q)['stock'];
                }
            }
        } else {
            $p_q = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
            if ($p_q && mysqli_num_rows($p_q) > 0) $available_stock = (int)mysqli_fetch_assoc($p_q)['stock'];
        }
        
        if ($requested_qty > $available_stock) {
            header("Location: ../product.php?id=$product_id&error=" . urlencode("Not enough stock available."));
            exit;
        }

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
            if (!empty($_POST['selected_image'])) {
                $_SESSION['cart'][$cart_key]['selected_image'] = clean_input($conn, $_POST['selected_image']);
            }
            if ($variant_custom_price !== null) {
                $_SESSION['cart'][$cart_key]['custom_price'] = $variant_custom_price;
            }
        } else {
            $_SESSION['cart'][$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'color' => $color,
                'size' => $size,
                'custom_price' => $variant_custom_price,
                'selected_image' => isset($_POST['selected_image']) ? clean_input($conn, $_POST['selected_image']) : ''
            ];
        }

        if ($action === 'buy_now') {
            header("Location: ../checkout.php");
        } else {
            header("Location: ../cart_view.php?msg=" . urlencode("Product added to cart."));
        }
        exit;
    }

    if ($action === 'cancel_order_user') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php?error=" . urlencode("Unauthorized."));
            exit;
        }
        $user_id = (int)$_SESSION['user_id'];
        $order_id = (int)$_POST['order_id'];
        
        $chk_q = mysqli_query($conn, "SELECT id, status, stock_deducted, restocked FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'Pending'");
        if ($chk_q && mysqli_num_rows($chk_q) > 0) {
            $order_info = mysqli_fetch_assoc($chk_q);
            
            // Restock items if they were deducted and not yet restocked
            if ($order_info['stock_deducted'] == 1 && $order_info['restocked'] == 0) {
                $items_res = mysqli_query($conn, "SELECT product_id, color, size, quantity FROM order_items WHERE order_id = $order_id");
                while ($itm = mysqli_fetch_assoc($items_res)) {
                    $pid = (int)$itm['product_id'];
                    $qty = (int)$itm['quantity'];
                    $color = clean_input($conn, $itm['color'] ?? '');
                    $size = clean_input($conn, $itm['size'] ?? '');
                    
                    mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $pid");
                    if (!empty($color) || !empty($size)) {
                        $where_inv = "product_id = $pid";
                        if (!empty($color)) $where_inv .= " AND color_name = '$color'";
                        if (!empty($size)) $where_inv .= " AND size_name = '$size'";
                        mysqli_query($conn, "UPDATE product_inventory SET stock = stock + $qty WHERE $where_inv");
                    }
                }
                mysqli_query($conn, "UPDATE orders SET restocked = 1 WHERE id = $order_id");
            }
            
            mysqli_query($conn, "UPDATE orders SET status = 'Cancelled' WHERE id = $order_id");
            header("Location: ../user_panel.php?msg=" . urlencode("Order cancelled successfully."));
        } else {
            header("Location: ../user_panel.php?error=" . urlencode("Failed to cancel order or order is no longer pending."));
        }
        exit;
    }

    if ($action === 'update_order_location') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php?error=" . urlencode("Unauthorized."));
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $order_id = (int)$_POST['order_id'];
        $shipping_province = (int)($_POST['shipping_province'] ?? 0);
        $shipping_district = (int)($_POST['shipping_district'] ?? 0);
        $shipping_sector = (int)($_POST['shipping_sector'] ?? 0);
        $shipping_cell = (int)($_POST['shipping_cell'] ?? 0);
        $shipping_village = (int)($_POST['shipping_village'] ?? 0);

        if (!$shipping_village) {
            header("Location: ../user_panel.php?error=" . urlencode("Please select the complete delivery location, down to the specific village."));
            exit;
        }

        $order_q = mysqli_query($conn, "SELECT o.total_amount, o.shipping_province, o.shipping_district, o.shipping_sector, o.shipping_cell, o.shipping_village, o.order_number, o.shipping_name, u.email, u.first_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = $order_id AND o.user_id = $user_id AND o.status = 'Pending'");
        if (!$order_q || mysqli_num_rows($order_q) == 0) {
            header("Location: ../user_panel.php?error=" . urlencode("Failed to update location or order is no longer pending."));
            exit;
        }
        $order_row = mysqli_fetch_assoc($order_q);

        if (!function_exists('getLocationFee')) {
            function getLocationFee($conn, $locs) {
                $cost = 0;
                foreach ($locs as $loc_id) {
                    if ($loc_id > 0) {
                        $q = mysqli_query($conn, "SELECT delivery_fee FROM rwanda_locations WHERE id = $loc_id");
                        if ($q && mysqli_num_rows($q) > 0) {
                            $row = mysqli_fetch_assoc($q);
                            $fee = (float)$row['delivery_fee'];
                            if ($fee > 0 && $cost == 0) {
                                $cost = $fee;
                            }
                        }
                    }
                }
                return $cost;
            }
        }

        $old_locs = [
            (int)$order_row['shipping_village'],
            (int)$order_row['shipping_cell'],
            (int)$order_row['shipping_sector'],
            (int)$order_row['shipping_district'],
            (int)$order_row['shipping_province']
        ];
        $old_fee = getLocationFee($conn, $old_locs);

        $location_chain = [
            $shipping_village,
            $shipping_cell,
            $shipping_sector,
            $shipping_district,
            $shipping_province
        ];
        
        $loc_names = [0=>'', 1=>'', 2=>'', 3=>'', 4=>''];
        $new_fee = 0;
        foreach ($location_chain as $idx => $loc_id) {
            if ($loc_id > 0) {
                $loc_q = mysqli_query($conn, "SELECT name, delivery_fee FROM rwanda_locations WHERE id = $loc_id");
                if ($loc_q && mysqli_num_rows($loc_q) > 0) {
                    $loc_row = mysqli_fetch_assoc($loc_q);
                    $loc_names[$idx] = clean_input($conn, $loc_row['name']);
                    $fee = (float)$loc_row['delivery_fee'];
                    if ($fee > 0 && $new_fee == 0) {
                        $new_fee = $fee;
                    }
                }
            }
        }
        
        $shipping_address = ltrim(trim($loc_names[0] . ', ' . $loc_names[1], ' ,'), ','); // village, cell
        $shipping_city = ltrim(trim($loc_names[2] . ', ' . $loc_names[3], ' ,'), ',');    // sector, district
        $shipping_state = $loc_names[4];                          // province

        $new_total = (float)$order_row['total_amount'] - $old_fee + $new_fee;

        $sql = "UPDATE orders SET total_amount = '$new_total', shipping_address = '$shipping_address', shipping_city = '$shipping_city', shipping_state = '$shipping_state', shipping_province = $shipping_province, shipping_district = $shipping_district, shipping_sector = $shipping_sector, shipping_cell = $shipping_cell, shipping_village = $shipping_village WHERE id = $order_id";
        if (mysqli_query($conn, $sql)) {
            $to = $order_row['email'] ?? '';
            if (!empty($to)) {
                require_once __DIR__ . '/mailer.php';
                $order_num = !empty($order_row['order_number']) ? $order_row['order_number'] : $order_id;
                $name = !empty($order_row['first_name']) ? $order_row['first_name'] : $order_row['shipping_name'];
                
                $subject = "Delivery Location Updated - " . $order_num;
                $track_link = BASE_URL . "order_track.php?id=" . $order_id;
                
                $html = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>";
                $html .= "<div style='background-color: #fb7c00; padding: 20px; text-align: center; color: white;'>";
                $html .= "<h1 style='margin: 0; font-size: 24px;'>Zora Online Shopping Rwanda</h1>";
                $html .= "</div>";
                $html .= "<div style='padding: 20px;'>";
                $html .= "<p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>";
                $html .= "<p>Your delivery location for order <strong>" . htmlspecialchars($order_num) . "</strong> has been successfully updated.</p>";
                
                $html .= "<div style='background-color: #f9f9f9; padding: 20px; border-radius: 6px; margin: 20px 0;'>";
                $html .= "<p style='margin: 0; color: #666; font-size: 14px;'>New Delivery Location:</p>";
                $html .= "<h3 style='margin: 5px 0 0; color: #333;'>" . htmlspecialchars($shipping_address . ', ' . $shipping_city . ', ' . $shipping_state) . "</h3>";
                $html .= "</div>";
                
                if ($new_fee != $old_fee) {
                    $html .= "<p>Your new total including the updated delivery fee is <strong>" . number_format($new_total) . " RWF</strong>.</p>";
                }
                
                $html .= "<p>You can track your order using the link below:</p>";
                $html .= "<div style='text-align: center; margin-top: 30px;'>";
                $html .= "<a href='" . $track_link . "' style='background-color: #fb7c00; color: white; padding: 12px 25px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>Track Your Order</a>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "<div style='background-color: #f1f1f1; padding: 15px; text-align: center; color: #888; font-size: 12px;'>";
                $html .= "<p>&copy; " . date('Y') . " Zora Online Shopping Rwanda. All rights reserved.</p>";
                $html .= "</div>";
                $html .= "</div>";

                sendMail($to, $name, $subject, $html);
            }
            header("Location: ../user_panel.php?msg=" . urlencode("Location updated successfully. Total price adjusted."));
        } else {
            header("Location: ../user_panel.php?error=" . urlencode("Failed to update location."));
        }
        exit;
    }

    if ($action === 'delete_order_history') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php");
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $order_id = (int)$_POST['order_id'];
        
        $sql = "UPDATE orders SET user_id = NULL WHERE id = $order_id AND user_id = $user_id AND status IN ('Delivered', 'Cancelled')";
        if (mysqli_query($conn, $sql)) {
            header("Location: ../user_panel.php?msg=" . urlencode("Order removed from history."));
        } else {
            header("Location: ../user_panel.php?error=" . urlencode("Failed to remove order."));
        }
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

    if ($action === 'submit_review') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../product.php?id=" . (int)$_POST['product_id'] . "&error=" . urlencode("You must be logged in to leave a review."));
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $product_id = (int)$_POST['product_id'];
        $rating = (int)$_POST['rating'];
        $comment = clean_input($conn, $_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            header("Location: ../product.php?id=$product_id&error=" . urlencode("Invalid rating."));
            exit;
        }

        $sql = "INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES ($product_id, $user_id, $rating, '$comment')";
        if (mysqli_query($conn, $sql)) {
            // Recalculate average rating
            $avg_res = mysqli_query($conn, "SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = $product_id");
            if ($row = mysqli_fetch_assoc($avg_res)) {
                $new_avg = round($row['avg_rating'], 1);
                mysqli_query($conn, "UPDATE products SET rating = $new_avg WHERE id = $product_id");
            }
            header("Location: ../product.php?id=$product_id&msg=" . urlencode("Review submitted successfully."));
        } else {
            header("Location: ../product.php?id=$product_id&error=" . urlencode("Failed to submit review."));
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
    if ($_GET['action'] === 'admin_logout') {
        session_destroy();
        header("Location: ../admin/login.php?msg=" . urlencode("Logged out successfully."));
        exit;
    }
}
?>
