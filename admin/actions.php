<?php
<<<<<<< HEAD
require_once __DIR__ . '/../core/config.php';

if (!function_exists('generate_product_image_name')) {
    function generate_product_image_name($baseName, $ext, $uploadDir = '../uploads') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $baseName), '-'));
        if (empty($slug)) { $slug = 'image'; }
        $cleanExt = strtolower($ext);
        $fileName = $slug . '.' . $cleanExt;
        $counter = 1;
        while (file_exists($uploadDir . '/' . $fileName)) {
            $fileName = $slug . '-' . $counter . '.' . $cleanExt;
            $counter++;
        }
        return $fileName;
    }
}

if (!function_exists('process_image_upload_or_url')) {
    function process_image_upload_or_url($fileField, $urlField, $prefix) {
        if (!empty($_POST[$urlField])) {
            $url = trim($_POST[$urlField]);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $ctx = stream_context_create(['http' => ['timeout' => 5], 'https' => ['timeout' => 5]]);
                $imgData = @file_get_contents($url, false, $ctx);
                if ($imgData !== false) {
                    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (empty($ext) || !in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp'])) { $ext = 'jpg'; }
                    $img = generate_product_image_name($prefix, $ext);
                    if (file_put_contents('../uploads/' . $img, $imgData)) {
                        if (function_exists('optimize_and_save_image')) {
                            optimize_and_save_image('../uploads/' . $img, '../uploads/' . $img);
                        }
                        return $img;
                    }
                }
            }
        }
        if (isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] == 0) {
            $ext = pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION);
            $img = generate_product_image_name($prefix, $ext);
            if (function_exists('optimize_and_save_image') && optimize_and_save_image($_FILES[$fileField]['tmp_name'], '../uploads/' . $img)) {
                return $img;
            } elseif (move_uploaded_file($_FILES[$fileField]['tmp_name'], '../uploads/' . $img)) {
                return $img;
            }
        }
        return null;
    }
}

if (!function_exists('optimize_and_save_image')) {
    function optimize_and_save_image($tmpPath, $destPath, $maxDim = 1400, $quality = 82) {
        if (!file_exists($tmpPath) || !is_readable($tmpPath)) {
            return false;
        }
        
        // Fallback if GD is not available
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
        }

        $imageInfo = @getimagesize($tmpPath);
        if (!$imageInfo) {
            return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
        }

        $mime = $imageInfo['mime'];
        $srcWidth = $imageInfo[0];
        $srcHeight = $imageInfo[1];

        // If file is already small (e.g. < 400KB and within max dimensions), simply move it
        $fileSize = @filesize($tmpPath);
        if ($fileSize && $fileSize < 400000 && $srcWidth <= $maxDim && $srcHeight <= $maxDim) {
            return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
        }

        $srcImg = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $srcImg = @imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $srcImg = @imagecreatefrompng($tmpPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = @imagecreatefromwebp($tmpPath);
                }
                break;
            case 'image/gif':
                $srcImg = @imagecreatefromgif($tmpPath);
                break;
        }

        if (!$srcImg) {
            return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
        }

        $newWidth = $srcWidth;
        $newHeight = $srcHeight;

        if ($srcWidth > $maxDim || $srcHeight > $maxDim) {
            if ($srcWidth > $srcHeight) {
                $newWidth = $maxDim;
                $newHeight = (int)round(($srcHeight / $srcWidth) * $maxDim);
            } else {
                $newHeight = $maxDim;
                $newWidth = (int)round(($srcWidth / $srcHeight) * $maxDim);
            }
        }

        $dstImg = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and WEBP
        if ($mime === 'image/png' || $mime === 'image/webp' || $mime === 'image/gif') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);

        $saved = false;
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        if ($ext === 'png') {
            $saved = @imagepng($dstImg, $destPath, 8);
        } elseif ($ext === 'webp' && function_exists('imagewebp')) {
            $saved = @imagewebp($dstImg, $destPath, $quality);
        } else {
            $saved = @imagejpeg($dstImg, $destPath, $quality);
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        if (!$saved) {
            return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
        }

        return true;
    }
}

if (!function_exists('clean_tagify_tags')) {
    function clean_tagify_tags($rawTags) {
        if (empty($rawTags)) return '';
        $raw = html_entity_decode(trim($rawTags));
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = json_decode(stripslashes($raw), true);
        }
        $tags = [];
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded as $t) {
                $val = is_array($t) ? ($t['value'] ?? '') : $t;
                $cleaned = trim((string)$val);
                $cleaned = preg_replace('/^[\s\-\*#\d\.\)]+/', '', $cleaned);
                $cleaned = trim($cleaned, " \t\n\r\0\x0B\"',");
                if (!empty($cleaned)) {
                    $tags[] = $cleaned;
                }
            }
        } else {
            $parts = preg_split('/[,\n\r\t;]+/', $raw);
            foreach ($parts as $p) {
                $cleaned = trim($p);
                $cleaned = preg_replace('/^[\s\-\*#\d\.\)]+/', '', $cleaned);
                $cleaned = trim($cleaned, " \t\n\r\0\x0B\"',");
                if (!empty($cleaned)) {
                    $tags[] = $cleaned;
                }
            }
        }
        return implode(', ', array_unique($tags));
    }
}
=======
require_once '../core/config.php';
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=" . urlencode("Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

<<<<<<< HEAD


    if ($action === 'update_order') {
        $order_id = (int)$_POST['order_id'];
        $status = clean_input($conn, $_POST['status']);
        
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");

        // Automatically record in payments when order is Delivered
        if (strtolower($status) === 'delivered') {
            $o_res = mysqli_query($conn, "SELECT user_id, total_amount, payment_method, created_at FROM orders WHERE id = $order_id");
            if ($o_res && $o_row = mysqli_fetch_assoc($o_res)) {
                $u_id = !empty($o_row['user_id']) ? (int)$o_row['user_id'] : "NULL";
                $amt = (float)$o_row['total_amount'];
                $pm = clean_input($conn, $o_row['payment_method'] ?? 'Cash on Delivery');
                $tx_ref = 'TXN-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
                mysqli_query($conn, "INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status, payment_date) 
                    VALUES ($order_id, $u_id, $amt, '$pm', '$tx_ref', 'Paid', NOW()) 
                    ON DUPLICATE KEY UPDATE amount = VALUES(amount), payment_method = VALUES(payment_method), status = 'Paid'");
            }
        } elseif (in_array(strtolower($status), ['cancelled', 'failed', 'declined'])) {
            mysqli_query($conn, "UPDATE payments SET status = 'Cancelled' WHERE order_id = $order_id");
        }
        
        // Handle cancellation restocking & reactivation deduction
        if (in_array($status, ['Cancelled', 'Declined', 'Failed'])) {
            $chk_restocked = mysqli_query($conn, "SELECT stock_deducted, restocked FROM orders WHERE id = $order_id");
            $chk_row = mysqli_fetch_assoc($chk_restocked);
            if ($chk_row && $chk_row['stock_deducted'] == 1 && $chk_row['restocked'] == 0) {
                $items_res = mysqli_query($conn, "SELECT product_id, color, quantity, size FROM order_items WHERE order_id = $order_id");
                while ($itm = mysqli_fetch_assoc($items_res)) {
                    $pid = (int)$itm['product_id'];
                    $qty = (int)$itm['quantity'];
                    $color = clean_input($conn, $itm['color'] ?? '');
                    $size = clean_input($conn, $itm['size'] ?? '');
                    mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $pid");
                    if (!empty($size) || !empty($color)) {
                        $where_inv = "product_id = $pid";
                        if (!empty($color)) $where_inv .= " AND color_name = '$color'";
                        if (!empty($size)) $where_inv .= " AND size_name = '$size'";
                        mysqli_query($conn, "UPDATE product_inventory SET stock = stock + $qty WHERE $where_inv");
                    }
                }
                mysqli_query($conn, "UPDATE orders SET restocked = 1 WHERE id = $order_id");
            }
        } else {
            $chk_restocked = mysqli_query($conn, "SELECT stock_deducted, restocked FROM orders WHERE id = $order_id");
            $chk_row = mysqli_fetch_assoc($chk_restocked);
            if ($chk_row && $chk_row['restocked'] == 1) {
                $items_res = mysqli_query($conn, "SELECT product_id, color, quantity, size FROM order_items WHERE order_id = $order_id");
                while ($itm = mysqli_fetch_assoc($items_res)) {
                    $pid = (int)$itm['product_id'];
                    $qty = (int)$itm['quantity'];
                    $color = clean_input($conn, $itm['color'] ?? '');
                    $size = clean_input($conn, $itm['size'] ?? '');
                    mysqli_query($conn, "UPDATE products SET stock = GREATEST(0, stock - $qty) WHERE id = $pid");
                    if (!empty($size) || !empty($color)) {
                        $where_inv = "product_id = $pid";
                        if (!empty($color)) $where_inv .= " AND color_name = '$color'";
                        if (!empty($size)) $where_inv .= " AND size_name = '$size'";
                        mysqli_query($conn, "UPDATE product_inventory SET stock = GREATEST(0, stock - $qty) WHERE $where_inv");
                    }
                }
                mysqli_query($conn, "UPDATE orders SET restocked = 0, stock_deducted = 1 WHERE id = $order_id");
            }
        }
        
        // Send Email Notification
        $order_q = mysqli_query($conn, "SELECT o.order_number, o.user_id, o.guest_email, o.shipping_name, u.email, u.first_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = $order_id");
        $order_num = $order_id;
        if ($order_q && mysqli_num_rows($order_q) > 0) {
            $order_data = mysqli_fetch_assoc($order_q);
            $order_num = !empty($order_data['order_number']) ? $order_data['order_number'] : $order_id;
            $to = !empty($order_data['user_id']) ? $order_data['email'] : $order_data['guest_email'];
            $name = !empty($order_data['user_id']) ? $order_data['first_name'] : $order_data['shipping_name'];
            
            if (!empty($to)) {
                require_once __DIR__ . '/../core/mailer.php';

                $subject = "Order Status Update - " . $order_num;
                $track_link = BASE_URL . "order_track.php?id=" . $order_id;
                
                $statusColor = '#fb7c00';
                $statusBg = '#fff7ed';
                if (strtolower($status) === 'delivered') { $statusColor = '#16a34a'; $statusBg = '#f0fdf4'; }
                if (strtolower($status) === 'cancelled') { $statusColor = '#dc2626'; $statusBg = '#fef2f2'; }
                if (strtolower($status) === 'shipped') { $statusColor = '#0284c7'; $statusBg = '#f0f9ff'; }
                if (strtolower($status) === 'out for delivery') { $statusColor = '#d97706'; $statusBg = '#fffbeb'; }

                $html = "<div style='text-align: center; margin-bottom: 24px;'>";
                $html .= "<h2 style='margin: 0 0 6px; color: #012a5e; font-size: 22px; font-weight: 800;'>Order Status Update</h2>";
                $html .= "<p style='margin: 0; color: #64748b; font-size: 14px;'>Tracking Reference: <strong>" . htmlspecialchars($order_num) . "</strong></p>";
                $html .= "</div>";
                $html .= "<p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>";
                $html .= "<p>Your order (<strong>" . htmlspecialchars($order_num) . "</strong>) has an updated fulfillment status:</p>";
                
                $html .= "<div style='background-color: " . $statusBg . "; padding: 22px; text-align: center; border-radius: 12px; margin: 24px 0; border: 1.5px solid " . $statusColor . "33;'>";
                $html .= "<p style='margin: 0 0 4px; color: #64748b; font-size: 13px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;'>Current Status</p>";
                $html .= "<h2 style='margin: 0; color: " . $statusColor . "; font-size: 24px; font-weight: 800;'>" . strtoupper(htmlspecialchars($status)) . "</h2>";
                $html .= "</div>";

                $html .= "<p>You can track the live progress and delivery details of your order at any time using the link below:</p>";
                $html .= "<div style='text-align: center; margin: 30px 0 10px;'>";
                $html .= "<a href='" . $track_link . "' style='background-color: #fb7c00; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(251, 124, 0, 0.3);'>Track Your Order &rarr;</a>";
                $html .= "</div>";

                sendMail($to, $name, $subject, $html);
            }
        }
        
        header("Location: orders.php?msg=" . urlencode("Order $order_num updated."));
        exit;
    }



    if ($action === 'delete_order') {
        $order_id = (int)$_POST['order_id'];
        $order_q = mysqli_query($conn, "SELECT order_number FROM orders WHERE id = $order_id");
        $order_num = ($order_q && mysqli_num_rows($order_q) > 0) ? mysqli_fetch_assoc($order_q)['order_number'] : '#' . $order_id;
        mysqli_query($conn, "UPDATE orders SET deleted_at = NOW() WHERE id = $order_id");
        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'orders.php';
        header("Location: $redirect?msg=" . urlencode("Order $order_num moved to trash."));
        exit;
    }

    if ($action === 'bulk_delete_orders') {
        $order_ids = isset($_POST['order_ids']) ? (array)$_POST['order_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $order_ids));
        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'orders.php';
        if (!empty($clean_ids)) {
            $ids_str = implode(',', $clean_ids);
            mysqli_query($conn, "UPDATE orders SET deleted_at = NOW() WHERE id IN ($ids_str)");
            $count = count($clean_ids);
            header("Location: $redirect?msg=" . urlencode("$count order(s) moved to trash successfully."));
            exit;
        }
        header("Location: $redirect?error=" . urlencode("No orders selected for deletion."));
        exit;
    }

    if ($action === 'restore_order') {
        $order_id = (int)$_POST['order_id'];
        $order_q = mysqli_query($conn, "SELECT order_number FROM orders WHERE id = $order_id");
        $order_num = ($order_q && mysqli_num_rows($order_q) > 0) ? mysqli_fetch_assoc($order_q)['order_number'] : '#' . $order_id;
        mysqli_query($conn, "UPDATE orders SET deleted_at = NULL WHERE id = $order_id");
        header("Location: trash.php?msg=" . urlencode("Order $order_num restored successfully."));
        exit;
    }

    if ($action === 'bulk_restore_orders') {
        $order_ids = isset($_POST['order_ids']) ? (array)$_POST['order_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $order_ids));
        if (!empty($clean_ids)) {
            $ids_str = implode(',', $clean_ids);
            mysqli_query($conn, "UPDATE orders SET deleted_at = NULL WHERE id IN ($ids_str)");
            $count = count($clean_ids);
            header("Location: trash.php?msg=" . urlencode("$count order(s) restored successfully."));
            exit;
        }
        header("Location: trash.php?error=" . urlencode("No orders selected to restore."));
        exit;
    }

    if ($action === 'permanent_delete_order') {
        $order_id = (int)$_POST['order_id'];
        $order_q = mysqli_query($conn, "SELECT order_number FROM orders WHERE id = $order_id");
        $order_num = ($order_q && mysqli_num_rows($order_q) > 0) ? mysqli_fetch_assoc($order_q)['order_number'] : '#' . $order_id;
        mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");
        mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id");
        header("Location: trash.php?msg=" . urlencode("Order $order_num permanently deleted."));
        exit;
    }

    if ($action === 'bulk_permanent_delete_orders') {
        $order_ids = isset($_POST['order_ids']) ? (array)$_POST['order_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $order_ids));
        if (!empty($clean_ids)) {
            $ids_str = implode(',', $clean_ids);
            mysqli_query($conn, "DELETE FROM order_items WHERE order_id IN ($ids_str)");
            mysqli_query($conn, "DELETE FROM orders WHERE id IN ($ids_str)");
            $count = count($clean_ids);
            header("Location: trash.php?msg=" . urlencode("$count order(s) permanently deleted."));
            exit;
        }
        header("Location: trash.php?error=" . urlencode("No orders selected for permanent deletion."));
        exit;
    }

    if ($action === 'delete_order_item') {
        $item_id = (int)$_POST['item_id'];
        $order_id = (int)$_POST['order_id'];
        
        mysqli_query($conn, "DELETE FROM order_items WHERE id = $item_id AND order_id = $order_id");
        
        // Recalculate remaining items
        $rem_q = mysqli_query($conn, "SELECT COUNT(*) as cnt, COALESCE(SUM(price * quantity), 0) as new_subtotal FROM order_items WHERE order_id = $order_id");
        $rem = mysqli_fetch_assoc($rem_q);
        $rem_cnt = (int)($rem['cnt'] ?? 0);
        $new_subtotal = (float)($rem['new_subtotal'] ?? 0);
        
        if ($rem_cnt === 0) {
            mysqli_query($conn, "UPDATE orders SET total_amount = 0, deleted_at = NOW() WHERE id = $order_id");
        } else {
            // Keep shipping if total amount was higher than subtotal
            $old_ord = mysqli_fetch_assoc(mysqli_query($conn, "SELECT total_amount FROM orders WHERE id = $order_id"));
            mysqli_query($conn, "UPDATE orders SET total_amount = $new_subtotal WHERE id = $order_id");
        }
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'remaining_count' => $rem_cnt, 'new_subtotal' => $new_subtotal]);
            exit;
        }
        
        header("Location: orders.php?msg=" . urlencode("Product removed from order."));
        exit;
    }

    if ($action === 'bulk_delete_order_items') {
        $order_id = (int)$_POST['order_id'];
        $item_ids = isset($_POST['item_ids']) ? (array)$_POST['item_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $item_ids));
        
        if (!empty($clean_ids)) {
            $ids_str = implode(',', $clean_ids);
            mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id AND id IN ($ids_str)");
            
            $rem_q = mysqli_query($conn, "SELECT COUNT(*) as cnt, COALESCE(SUM(price * quantity), 0) as new_subtotal FROM order_items WHERE order_id = $order_id");
            $rem = mysqli_fetch_assoc($rem_q);
            $rem_cnt = (int)($rem['cnt'] ?? 0);
            $new_subtotal = (float)($rem['new_subtotal'] ?? 0);
            
            if ($rem_cnt === 0) {
                mysqli_query($conn, "UPDATE orders SET total_amount = 0, deleted_at = NOW() WHERE id = $order_id");
            } else {
                mysqli_query($conn, "UPDATE orders SET total_amount = $new_subtotal WHERE id = $order_id");
            }
        }
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'remaining_count' => $rem_cnt ?? 0]);
            exit;
        }
        
        header("Location: orders.php?msg=" . urlencode("Selected items removed from order."));
        exit;
    }

    if ($action === 'empty_trash') {
        mysqli_query($conn, "DELETE FROM order_items WHERE order_id IN (SELECT id FROM (SELECT id FROM orders WHERE deleted_at IS NOT NULL) as tmp)");
        mysqli_query($conn, "DELETE FROM orders WHERE deleted_at IS NOT NULL");
        header("Location: trash.php?msg=" . urlencode("Trash emptied successfully. All deleted orders removed."));
        exit;
    }

    if ($action === 'restore_all_trash') {
        mysqli_query($conn, "UPDATE orders SET deleted_at = NULL WHERE deleted_at IS NOT NULL");
        header("Location: trash.php?msg=" . urlencode("All orders restored successfully."));
        exit;
    }

    if ($action === 'delete_payment') {
        $order_id = (int)$_POST['order_id'];
        mysqli_query($conn, "UPDATE orders SET deleted_at = NOW() WHERE id = $order_id");
        header("Location: payments.php?msg=" . urlencode("Payment record moved to trash."));
        exit;
    }

    if ($action === 'delete_customer') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role = 'user'");
        header("Location: customers.php?msg=" . urlencode("Customer account removed."));
        exit;
    }

    if ($action === 'bulk_delete_customers') {
        $ids = isset($_POST['customer_ids']) ? (array)$_POST['customer_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $ids));
        if (!empty($clean_ids)) {
            $ids_str = implode(',', $clean_ids);
            mysqli_query($conn, "DELETE FROM users WHERE id IN ($ids_str) AND role = 'user'");
            $count = count($clean_ids);
            header("Location: customers.php?msg=" . urlencode("$count customer(s) removed successfully."));
            exit;
        }
        header("Location: customers.php?error=" . urlencode("No customers selected."));
        exit;
    }

    if ($action === 'bulk_delete_products') {
        $ids = isset($_POST['product_ids']) ? (array)$_POST['product_ids'] : [];
        $clean_ids = array_filter(array_map('intval', $ids));
        if (!empty($clean_ids)) {
            $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
            foreach ($clean_ids as $pid) {
                $filesToDelete = [];
                $res = mysqli_query($conn, "SELECT image FROM products WHERE id = $pid");
                if ($row = mysqli_fetch_assoc($res)) {
                    if (!empty($row['image']) && !in_array(strtolower($row['image']), $protected_files)) {
                        $filesToDelete[] = $row['image'];
                    }
                }
                $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $pid");
                if ($res) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        if (!empty($row['image_path']) && !in_array(strtolower($row['image_path']), $protected_files)) {
                            $filesToDelete[] = $row['image_path'];
                        }
                    }
                }
                
                mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $pid");
                mysqli_query($conn, "DELETE FROM product_inventory WHERE product_id = $pid");
                mysqli_query($conn, "DELETE FROM product_reviews WHERE product_id = $pid");
                mysqli_query($conn, "DELETE FROM wishlist WHERE product_id = $pid");
                mysqli_query($conn, "DELETE FROM products WHERE id = $pid");
                
                foreach ($filesToDelete as $file) {
                    $chk1 = mysqli_query($conn, "SELECT id FROM products WHERE image = '" . mysqli_real_escape_string($conn, $file) . "'");
                    $chk2 = mysqli_query($conn, "SELECT id FROM product_images WHERE image_path = '" . mysqli_real_escape_string($conn, $file) . "'");
                    if ((!$chk1 || mysqli_num_rows($chk1) == 0) && (!$chk2 || mysqli_num_rows($chk2) == 0)) {
                        $filePath = '../uploads/' . $file;
                        if (file_exists($filePath) && is_file($filePath)) {
                            @unlink($filePath);
                        }
                    }
                }
            }
            $count = count($clean_ids);
            header("Location: products.php?msg=" . urlencode("$count product(s) permanently removed."));
            exit;
        }
        header("Location: products.php?error=" . urlencode("No products selected."));
=======
    if ($action === 'update_order') {
        $order_id = (int)$_POST['order_id'];
        $status = clean_input($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
        header("Location: orders.php?msg=" . urlencode("Order #$order_id updated."));
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        exit;
    }

    if ($action === 'add_product') {
        $name = clean_input($conn, $_POST['name']);
<<<<<<< HEAD

        $check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name'");
        if(mysqli_num_rows($check) > 0) {
            header("Location: products.php?error=" . urlencode("Product '$name' already exists!"));
            exit;
        }
        $price = (float)$_POST['price'];
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : 'NULL';
        $discount_expiry = !empty($_POST['discount_expiry']) ? "'" . clean_input($conn, $_POST['discount_expiry']) . "'" : 'NULL';
        $stock = (int)$_POST['stock'];
        $cat_id = (int)$_POST['category_id'];
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        $tags = clean_input($conn, $_POST['tags'] ?? '');
        $img = process_image_upload_or_url('image', 'image_url', 'prod');
        $image = $img ? $img : 'default_product.jpg';
        
        mysqli_query($conn, "INSERT INTO products (category_id, name, description, price, discount_price, discount_expiry, stock, image, tags) VALUES ($cat_id, '$name', '$desc', $price, $discount_price, $discount_expiry, $stock, '$image', '$tags')");
        $product_id = mysqli_insert_id($conn);
        
        // Notify panel of new product (no email)
        $msg_new = mysqli_real_escape_string($conn, "New Arrival: " . htmlspecialchars($name) . " is now available!<br><img src='uploads/" . htmlspecialchars($image) . "' style='max-width:100px; margin-top:5px; border-radius:5px;'>");
        $link_new = "product.php?id=" . $product_id;
        mysqli_query($conn, "INSERT INTO user_notifications (message, link) VALUES ('$msg_new', '$link_new')");
        
        if ($discount_price !== 'NULL' && (float)$discount_price > 0) {
            $expiry_msg = !empty($_POST['discount_expiry']) ? " (Expires at: " . htmlspecialchars($_POST['discount_expiry']) . ")" : "";
            $msg = mysqli_real_escape_string($conn, "Special Offer: " . htmlspecialchars($name) . " is available with a discount!" . $expiry_msg . "<br><img src='uploads/" . htmlspecialchars($image) . "' style='max-width:100px; margin-top:5px; border-radius:5px;'>");
            $link = "product.php?id=" . $product_id;
            mysqli_query($conn, "INSERT INTO user_notifications (message, link, expires_at) VALUES ('$msg', '$link', $discount_expiry)");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, BASE_URL . "core/async_mass_mail.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['product_id' => $product_id, 'discount_price' => (float)$discount_price]));
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_exec($ch);
            curl_close($ch);
        }

=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        header("Location: products.php?msg=" . urlencode("Product added successfully."));
        exit;
    }

    if ($action === 'edit_product') {
        $product_id = (int)$_POST['product_id'];
        $name = clean_input($conn, $_POST['name']);
        $price = (float)$_POST['price'];
<<<<<<< HEAD
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : 'NULL';
        $discount_expiry = !empty($_POST['discount_expiry']) ? "'" . clean_input($conn, $_POST['discount_expiry']) . "'" : 'NULL';
        $stock = (int)$_POST['stock'];
        $cat_id = (int)$_POST['category_id'];
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        $tags = clean_input($conn, $_POST['tags'] ?? '');
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = generate_product_image_name($name, $ext);
            if (optimize_and_save_image($_FILES['image']['tmp_name'], '../uploads/' . $imageName)) {
                $imageUpdate = ", image = '$imageName'";
                $res = mysqli_query($conn, "SELECT image FROM products WHERE id=$product_id");
                if ($row = mysqli_fetch_assoc($res)) {
                    $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
                    if (!empty($row['image']) && !in_array(strtolower($row['image']), $protected_files) && $row['image'] !== $imageName) {
                        $oldPath = '../uploads/' . $row['image'];
                        if (file_exists($oldPath) && is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                }
            }
        }
        
        $old_res = mysqli_query($conn, "SELECT discount_price, image FROM products WHERE id=$product_id");
        $old_row = mysqli_fetch_assoc($old_res);
        $old_discount = $old_row ? (float)$old_row['discount_price'] : 0;
        $prod_image = $old_row ? $old_row['image'] : 'default_product.jpg';
        
        mysqli_query($conn, "UPDATE products SET category_id=$cat_id, name='$name', description='$desc', price=$price, discount_price=$discount_price, discount_expiry=$discount_expiry, stock=$stock, tags='$tags' $imageUpdate WHERE id=$product_id");
        
        if ($discount_price !== 'NULL' && (float)$discount_price > 0 && (float)$discount_price != $old_discount) {
            $expiry_msg = !empty($_POST['discount_expiry']) ? " (Expires at: " . htmlspecialchars($_POST['discount_expiry']) . ")" : "";
            $raw_msg = "Special Offer: " . htmlspecialchars($name) . " is now discounted!" . $expiry_msg . "<br><img src='uploads/" . htmlspecialchars($prod_image) . "' style='max-width:100px; margin-top:5px; border-radius:5px;'>";
            $msg = mysqli_real_escape_string($conn, $raw_msg);
            $link = "product.php?id=" . $product_id;
            mysqli_query($conn, "INSERT INTO user_notifications (message, link, expires_at) VALUES ('$msg', '$link', $discount_expiry)");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, BASE_URL . "core/async_mass_mail.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['product_id' => $product_id, 'discount_price' => (float)$discount_price]));
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_exec($ch);
            curl_close($ch);
        } elseif (($discount_price === 'NULL' || (float)$discount_price == 0) && $old_discount > 0) {
            $link = "product.php?id=" . $product_id;
            mysqli_query($conn, "DELETE FROM user_notifications WHERE link = '$link' AND message LIKE 'Special Offer:%'");
        }
        
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Product updated successfully."));
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        exit;
    }

    if ($action === 'delete_product') {
        $product_id = (int)$_POST['product_id'];
<<<<<<< HEAD
        $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
        $filesToDelete = [];

        // 1. Gather primary product image
        $res = mysqli_query($conn, "SELECT image FROM products WHERE id=$product_id");
        if ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['image']) && !in_array(strtolower($row['image']), $protected_files)) {
                $filesToDelete[] = $row['image'];
            }
        }
        
        // 2. Gather all gallery and variant images for this product
        $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id=$product_id");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                if (!empty($row['image_path']) && !in_array(strtolower($row['image_path']), $protected_files)) {
                    $filesToDelete[] = $row['image_path'];
                }
            }
        }
        
        $filesToDelete = array_unique($filesToDelete);

        // 3. Delete database records
        mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $product_id");
        mysqli_query($conn, "DELETE FROM product_inventory WHERE product_id = $product_id");
        mysqli_query($conn, "DELETE FROM product_reviews WHERE product_id = $product_id");
        mysqli_query($conn, "DELETE FROM wishlist WHERE product_id = $product_id");
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");

        // 4. Delete physical image files from disk to free up server storage
        foreach ($filesToDelete as $file) {
            $chk1 = mysqli_query($conn, "SELECT id FROM products WHERE image = '" . mysqli_real_escape_string($conn, $file) . "'");
            $chk2 = mysqli_query($conn, "SELECT id FROM product_images WHERE image_path = '" . mysqli_real_escape_string($conn, $file) . "'");
            $isUsedElsewhere = ($chk1 && mysqli_num_rows($chk1) > 0) || ($chk2 && mysqli_num_rows($chk2) > 0);
            
            if (!$isUsedElsewhere) {
                $filePath = '../uploads/' . $file;
                if (file_exists($filePath) && is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        
        header("Location: products.php?msg=" . urlencode("Product and its associated images have been permanently removed."));
        exit;
    }

    if ($action === 'add_product_advanced') {
        $name = clean_input($conn, $_POST['name']);

        $check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name'");
        if(mysqli_num_rows($check) > 0) {
            header("Location: add_product.php?error=" . urlencode("Product '$name' already exists!"));
            exit;
        }
        $price = (float)$_POST['price'];
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $discount_expiry = !empty($_POST['discount_expiry']) ? clean_input($conn, $_POST['discount_expiry']) : null;
        $stock = (int)$_POST['stock'];
        
        // Auto-calculate stock from variants if provided
        if (isset($_POST['variant_color']) && is_array($_POST['variant_color'])) {
            $total_variant_stock = 0;
            $has_variants = false;
            foreach ($_POST['variant_color'] as $i => $vColorRaw) {
                $has_variants = true;
                $sizeStocks = $_POST['variant_size_stocks'][$i] ?? [];
                foreach ($sizeStocks as $sStock) {
                    $total_variant_stock += (int)$sStock;
                }
            }
            if ($has_variants) {
                $stock = $total_variant_stock;
            }
        }
        $cat_id = (int)$_POST['category_id'];
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        $tags = clean_input($conn, clean_tagify_tags($_POST['tags'] ?? ''));
        
        $primaryImage = 'default_product.jpg';
        $uploadedImages = [];
        
        // Start database transaction for fast, atomic operations
        mysqli_begin_transaction($conn);
        
        try {
            // Handle Multiple Primary & Gallery Images
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $total_imgs = count($_FILES['images']['name']);
                for ($k = 0; $k < $total_imgs; $k++) {
                    if ($_FILES['images']['error'][$k] == 0) {
                        $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                        $imageName = generate_product_image_name($name . ($k > 0 ? "-$k" : ""), $ext);
                        if (optimize_and_save_image($_FILES['images']['tmp_name'][$k], '../uploads/' . $imageName)) {
                            $uploadedImages[] = $imageName;
                        }
                    }
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = generate_product_image_name($name, $ext);
                if (optimize_and_save_image($_FILES['image']['tmp_name'], '../uploads/' . $imageName)) {
                    $uploadedImages[] = $imageName;
                }
            }
            
            if (!empty($_POST['image_url'])) {
                $uploadedImages[] = clean_input($conn, $_POST['image_url']);
            }
            
            if (!empty($uploadedImages)) {
                $primaryImage = $uploadedImages[0];
            }
            
            // Insert parent product - Type string: "issddsiss"
            $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, discount_price, discount_expiry, stock, image, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issddsiss", $cat_id, $name, $desc, $price, $discount_price, $discount_expiry, $stock, $primaryImage, $tags);
            $stmt->execute();
            $product_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert all uploaded primary/gallery images into product_images
            if (!empty($uploadedImages)) {
                $vstmt = $conn->prepare("INSERT INTO product_images (product_id, color_name, image_path, sizes) VALUES (?, ?, ?, ?)");
                foreach ($uploadedImages as $imgIdx => $imgName) {
                    $imgColor = '';
                    $emptySizes = '';
                    $vstmt->bind_param("isss", $product_id, $imgColor, $imgName, $emptySizes);
                    $vstmt->execute();
                }
                $vstmt->close();
            }
            
            // Handle Variants
            $seen_variant_colors = [];
            if (isset($_POST['variant_color']) && is_array($_POST['variant_color'])) {
                $vimg_stmt = $conn->prepare("INSERT INTO product_images (product_id, color_name, image_path, sizes, price) VALUES (?, ?, ?, ?, ?)");
                $inv_stmt = $conn->prepare("INSERT INTO product_inventory (product_id, color_name, size_name, stock, price) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($_POST['variant_color'] as $i => $vColorRaw) {
                    $vColor = clean_input($conn, $vColorRaw);
                    $normalizedColor = strtolower(trim($vColor));
                    if (empty($vColor) || in_array($normalizedColor, $seen_variant_colors)) {
                        continue; // Prevent duplicate variant addition
                    }
                    $seen_variant_colors[] = $normalizedColor;
                    
                    $vPriceRaw = $_POST['variant_price'][$i] ?? null;
                    $vPrice = ($vPriceRaw !== null && $vPriceRaw !== '' && (float)$vPriceRaw > 0) ? (float)$vPriceRaw : null;
                    
                    $sizeNames = $_POST['variant_size_names'][$i] ?? [];
                    $sizeStocks = $_POST['variant_size_stocks'][$i] ?? [];
                    
                    // Filter genuine sizes
                    $cleanSizes = [];
                    if (!empty($sizeNames)) {
                        foreach ($sizeNames as $sNameRaw) {
                            $sn = clean_input($conn, $sNameRaw);
                            if ($sn !== '' && strtolower($sn) !== 'standard' && strtolower($sn) !== 'default') {
                                $cleanSizes[] = $sn;
                            }
                        }
                    }
                    $vSizes = implode(', ', $cleanSizes);
                    
                    $vImages = [];
                    if (isset($_FILES['variant_images']['name'][$i]) && is_array($_FILES['variant_images']['name'][$i])) {
                        $total_v_images = count($_FILES['variant_images']['name'][$i]);
                        for ($j = 0; $j < $total_v_images; $j++) {
                            if ($_FILES['variant_images']['error'][$i][$j] == 0) {
                                $ext = pathinfo($_FILES['variant_images']['name'][$i][$j], PATHINFO_EXTENSION);
                                $imageName = generate_product_image_name($name . '-' . $vColor . ($j > 0 ? "-$j" : ""), $ext);
                                if (optimize_and_save_image($_FILES['variant_images']['tmp_name'][$i][$j], '../uploads/' . $imageName)) {
                                    $vImages[] = $imageName;
                                }
                            }
                        }
                    }
                    
                    if (!empty($vColor)) {
                        if (!empty($vImages)) {
                            foreach ($vImages as $vImage) {
                                $vimg_stmt->bind_param("isssd", $product_id, $vColor, $vImage, $vSizes, $vPrice);
                                $vimg_stmt->execute();
                            }
                        }
                        
                        // Insert inventory for each size (or single row if no size specified)
                        if (!empty($sizeNames)) {
                            for ($s = 0; $s < count($sizeNames); $s++) {
                                $sName = clean_input($conn, $sizeNames[$s]);
                                if (strtolower($sName) === 'standard' || strtolower($sName) === 'default') $sName = '';
                                $sStockRaw = $sizeStocks[$s] ?? '';
                                $sStock = (int)$sStockRaw;
                                if ($sName !== '' || $sStockRaw !== '') {
                                    $inv_stmt->bind_param("issid", $product_id, $vColor, $sName, $sStock, $vPrice);
                                    $inv_stmt->execute();
                                }
                            }
                        } else {
                            $emptySize = '';
                            $inv_stmt->bind_param("issid", $product_id, $vColor, $emptySize, $stock, $vPrice);
                            $inv_stmt->execute();
                        }
                    }
                }
                
                $vimg_stmt->close();
                $inv_stmt->close();
            }
            
            // Commit transaction
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: add_product.php?error=" . urlencode("Error saving product: " . $e->getMessage()));
            exit;
        }
        
        // Notify panel of new product (no email)
        $msg_new = mysqli_real_escape_string($conn, "New Arrival: " . htmlspecialchars($name) . " is now available!<br><img src='uploads/" . htmlspecialchars($primaryImage) . "' style='max-width:100px; margin-top:5px; border-radius:5px;'>");
        $link_new = "product.php?id=" . $product_id;
        mysqli_query($conn, "INSERT INTO user_notifications (message, link) VALUES ('$msg_new', '$link_new')");
        
        if ($discount_price !== null && (float)$discount_price > 0) {
            $expiry_msg = !empty($_POST['discount_expiry']) ? " (Expires at: " . htmlspecialchars($_POST['discount_expiry']) . ")" : "";
            $msg = mysqli_real_escape_string($conn, "Special Offer: " . htmlspecialchars($name) . " is available with a discount!" . $expiry_msg . "<br><img src='uploads/" . htmlspecialchars($primaryImage) . "' style='max-width:100px; margin-top:5px; border-radius:5px;'>");
            $link = "product.php?id=" . $product_id;
            $exp_val = $discount_expiry ? $discount_expiry : 'NULL';
            mysqli_query($conn, "INSERT INTO user_notifications (message, link, expires_at) VALUES ('$msg', '$link', $exp_val)");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, BASE_URL . "core/async_mass_mail.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['product_id' => $product_id, 'discount_price' => (float)$discount_price]));
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_exec($ch);
            curl_close($ch);
        }
        
        header("Location: products.php?msg=" . urlencode("Product and variants added successfully."));
=======
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
        header("Location: products.php?msg=" . urlencode("Product deleted."));
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        exit;
    }

    if ($action === 'add_category') {
        $name = clean_input($conn, $_POST['name']);
<<<<<<< HEAD
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';

        $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$name'");
        if(mysqli_num_rows($check) > 0) {
            header("Location: categories.php?error=" . urlencode("Category '$name' already exists!"));
            exit;
        }
        
        $img = process_image_upload_or_url('image', 'image_url', 'cat');
        $imageName = $img ? "'$img'" : 'NULL';
        
        mysqli_query($conn, "INSERT INTO categories (name, parent_id, image) VALUES ('$name', $parent_id, $imageName)");
=======
        $icon = clean_input($conn, $_POST['icon']) ?: 'fas fa-box';
        mysqli_query($conn, "INSERT INTO categories (name, icon) VALUES ('$name', '$icon')");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        header("Location: categories.php?msg=" . urlencode("Category added."));
        exit;
    }
    
    if ($action === 'edit_category') {
        $cat_id = (int)$_POST['category_id'];
        $name = clean_input($conn, $_POST['name']);
<<<<<<< HEAD
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
        
        $imageUpdate = "";
        $img = process_image_upload_or_url('image', 'image_url', 'cat');
        if ($img) {
            $imageUpdate = ", image='$img'";
            $res = mysqli_query($conn, "SELECT image FROM categories WHERE id=$cat_id");
            if ($row = mysqli_fetch_assoc($res)) {
                if (!empty($row['image'])) {
                    $path = '../uploads/' . $row['image'];
                    if (file_exists($path)) unlink($path);
                }
            }
        }
        
        mysqli_query($conn, "UPDATE categories SET name='$name', parent_id=$parent_id $imageUpdate WHERE id=$cat_id");
=======
        $icon = clean_input($conn, $_POST['icon']) ?: 'fas fa-box';
        mysqli_query($conn, "UPDATE categories SET name='$name', icon='$icon' WHERE id=$cat_id");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        header("Location: categories.php?msg=" . urlencode("Category updated."));
        exit;
    }

    if ($action === 'delete_category') {
        $cat_id = (int)$_POST['category_id'];
<<<<<<< HEAD
        
        // Delete category image
        $res = mysqli_query($conn, "SELECT image FROM categories WHERE id=$cat_id");
        if ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['image'])) {
                $path = '../uploads/' . $row['image'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        mysqli_query($conn, "DELETE FROM categories WHERE id = $cat_id");
        header("Location: categories.php?msg=" . urlencode("Category deleted."));
        exit;
    }

<<<<<<< HEAD
    if ($action === 'update_product_inventory') {
        $product_id = (int)$_POST['product_id'];
        $color_name = clean_input($conn, $_POST['color_name']);
        
        $sizeNames = $_POST['size_names'] ?? [];
        $sizeStocks = $_POST['size_stocks'] ?? [];
        
        // Delete existing inventory for this color
        $del_stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id = ? AND color_name = ?");
        $del_stmt->bind_param("is", $product_id, $color_name);
        $del_stmt->execute();
        
        // Insert new
        $sizesList = [];
        for ($i=0; $i<count($sizeNames); $i++) {
            $sName = clean_input($conn, $sizeNames[$i]);
            $sStockRaw = $sizeStocks[$i] ?? '';
            $sStock = (int)$sStockRaw;
            if ($sName !== '' || $sStockRaw !== '') {
                $sizesList[] = ['size' => $sName, 'stock' => $sStock];
                $inv_stmt = $conn->prepare("INSERT INTO product_inventory (product_id, color_name, size_name, stock) VALUES (?, ?, ?, ?)");
                $inv_stmt->bind_param("issi", $product_id, $color_name, $sName, $sStock);
                $inv_stmt->execute();
            }
        }
        
        // Update product_images sizes column for legacy UI
        $sizesJson = json_encode($sizesList);
        $upd_img = $conn->prepare("UPDATE product_images SET sizes = ? WHERE product_id = ? AND color_name = ?");
        $upd_img->bind_param("sis", $sizesJson, $product_id, $color_name);
        $upd_img->execute();
        
        // Recalculate total product stock
        $sum_query = mysqli_query($conn, "SELECT SUM(stock) as total_stock FROM product_inventory WHERE product_id = $product_id");
        $total_stock = (int)mysqli_fetch_assoc($sum_query)['total_stock'];
        mysqli_query($conn, "UPDATE products SET stock = $total_stock WHERE id = $product_id");
        
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Stock updated successfully."));
        exit;
    }

    if ($action === 'add_product_color') {
        $product_id = (int)$_POST['product_id'];
        $color_name = clean_input($conn, $_POST['color_name']);
        $var_price_raw = $_POST['variation_price'] ?? null;
        $var_price = ($var_price_raw !== null && $var_price_raw !== '' && (float)$var_price_raw > 0) ? (float)$var_price_raw : null;
        
        if (empty($color_name)) {
            header("Location: product_colors.php?id=$product_id&error=" . urlencode("Variation name cannot be empty."));
            exit;
        }

        // Check if variation already exists for this product
        $cNameEsc = mysqli_real_escape_string($conn, $color_name);
        $check_inv = mysqli_query($conn, "SELECT 1 FROM product_inventory WHERE product_id = $product_id AND LOWER(TRIM(color_name)) = LOWER('$cNameEsc') LIMIT 1");
        $check_img = mysqli_query($conn, "SELECT 1 FROM product_images WHERE product_id = $product_id AND LOWER(TRIM(color_name)) = LOWER('$cNameEsc') LIMIT 1");
        if ((mysqli_num_rows($check_inv) > 0) || (mysqli_num_rows($check_img) > 0)) {
            header("Location: product_colors.php?id=$product_id&error=" . urlencode("Variation '$color_name' already exists for this product."));
            exit;
        }
        
        $prod_res = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = $product_id");
        $prod_row = ($prod_res && mysqli_num_rows($prod_res) > 0) ? mysqli_fetch_assoc($prod_res) : ['name' => 'product', 'stock' => 0];
        $prod_name = $prod_row['name'];
        
        $sizeNames = $_POST['size_names'] ?? [];
        $sizeStocks = $_POST['size_stocks'] ?? [];
        
        // Filter genuine sizes
        $cleanSizes = [];
        if (!empty($sizeNames)) {
            foreach ($sizeNames as $sNameRaw) {
                $sn = clean_input($conn, $sNameRaw);
                if ($sn !== '' && strtolower($sn) !== 'standard' && strtolower($sn) !== 'default') {
                    $cleanSizes[] = $sn;
                }
            }
        }
        $sizes = implode(', ', $cleanSizes);
        
        mysqli_begin_transaction($conn);
        try {
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $total_images = count($_FILES['images']['name']);
                $vimg_stmt = $conn->prepare("INSERT INTO product_images (product_id, color_name, image_path, sizes, price) VALUES (?, ?, ?, ?, ?)");
                for ($i = 0; $i < $total_images; $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $imageName = generate_product_image_name($prod_name . '-' . $color_name . ($i > 0 ? "-$i" : ""), $ext);
                        if (optimize_and_save_image($_FILES['images']['tmp_name'][$i], '../uploads/' . $imageName)) {
                            $vimg_stmt->bind_param("isssd", $product_id, $color_name, $imageName, $sizes, $var_price);
                            $vimg_stmt->execute();
                        }
                    }
                }
                $vimg_stmt->close();
            }
            
            // Insert into product_inventory for each size (or single row if no size specified)
            $inv_stmt = $conn->prepare("INSERT INTO product_inventory (product_id, color_name, size_name, stock, price) VALUES (?, ?, ?, ?, ?)");
            if (!empty($sizeNames)) {
                for ($s = 0; $s < count($sizeNames); $s++) {
                    $sName = clean_input($conn, $sizeNames[$s]);
                    if (strtolower($sName) === 'standard' || strtolower($sName) === 'default') $sName = '';
                    $sStockRaw = $sizeStocks[$s] ?? '';
                    $sStock = (int)$sStockRaw;
                    if ($sName !== '' || $sStockRaw !== '') {
                        $inv_stmt->bind_param("issid", $product_id, $color_name, $sName, $sStock, $var_price);
                        $inv_stmt->execute();
                    }
                }
            } else {
                $emptySize = '';
                $base_stk = (int)$prod_row['stock'];
                $inv_stmt->bind_param("issid", $product_id, $color_name, $emptySize, $base_stk, $var_price);
                $inv_stmt->execute();
            }
            $inv_stmt->close();
            
            $stock_res = mysqli_query($conn, "SELECT SUM(stock) as t_stock FROM product_inventory WHERE product_id = $product_id");
            $t_stock = mysqli_fetch_assoc($stock_res)['t_stock'] ?? 0;
            mysqli_query($conn, "UPDATE products SET stock = $t_stock WHERE id = $product_id");

            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: product_colors.php?id=$product_id&error=" . urlencode("Error saving variation: " . $e->getMessage()));
            exit;
        }
        
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Color variation and inventory saved successfully."));
        exit;
    }

    if ($action === 'delete_product_image') {
        $image_id = (int)$_POST['image_id'];
        $product_id = (int)$_POST['product_id'];
        $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
        
        $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE id = $image_id");
        $fileToDelete = null;
        if ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['image_path']) && !in_array(strtolower($row['image_path']), $protected_files)) {
                $fileToDelete = $row['image_path'];
=======
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
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            }
        }
        
        mysqli_query($conn, "DELETE FROM product_images WHERE id = $image_id");
<<<<<<< HEAD
        
        if ($fileToDelete) {
            $chk1 = mysqli_query($conn, "SELECT id FROM products WHERE image = '" . mysqli_real_escape_string($conn, $fileToDelete) . "'");
            $chk2 = mysqli_query($conn, "SELECT id FROM product_images WHERE image_path = '" . mysqli_real_escape_string($conn, $fileToDelete) . "'");
            if (mysqli_num_rows($chk1) == 0 && mysqli_num_rows($chk2) == 0) {
                $path = '../uploads/' . $fileToDelete;
                if (file_exists($path) && is_file($path)) {
                    @unlink($path);
                }
            }
        }
        
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Image permanently deleted from gallery and server."));
        exit;
    }

    if ($action === 'delete_product_variation') {
        $product_id = (int)$_POST['product_id'];
        $color_name = clean_input($conn, $_POST['color_name']);
        $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
        
        // Find all images for this color
        $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $product_id AND color_name = '$color_name'");
        $filesToDelete = [];
        while ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['image_path']) && !in_array(strtolower($row['image_path']), $protected_files)) {
                $filesToDelete[] = $row['image_path'];
            }
        }
        
        // Delete records from database
        mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $product_id AND color_name = '$color_name'");
        mysqli_query($conn, "DELETE FROM product_inventory WHERE product_id = $product_id AND color_name = '$color_name'");
        
        // Unlink files from server if not used elsewhere
        foreach ($filesToDelete as $file) {
            $chk1 = mysqli_query($conn, "SELECT id FROM products WHERE image = '" . mysqli_real_escape_string($conn, $file) . "'");
            $chk2 = mysqli_query($conn, "SELECT id FROM product_images WHERE image_path = '" . mysqli_real_escape_string($conn, $file) . "'");
            if (mysqli_num_rows($chk1) == 0 && mysqli_num_rows($chk2) == 0) {
                $path = '../uploads/' . $file;
                if (file_exists($path) && is_file($path)) {
                    @unlink($path);
                }
            }
        }
        
        // Update product stock
        $stock_res = mysqli_query($conn, "SELECT SUM(stock) as t_stock FROM product_inventory WHERE product_id = $product_id");
        $t_stock = mysqli_fetch_assoc($stock_res)['t_stock'] ?? 0;
        if(is_null($t_stock)) $t_stock = 0;
        mysqli_query($conn, "UPDATE products SET stock = $t_stock WHERE id = $product_id");

        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Variation and images permanently deleted."));
        exit;
    }

    if ($action === 'update_color_inventory') {
        $product_id = (int)$_POST['product_id'];
        $color_name = clean_input($conn, $_POST['color_name']);
        $var_price_raw = $_POST['variation_price'] ?? null;
        $var_price = ($var_price_raw !== null && $var_price_raw !== '' && (float)$var_price_raw > 0) ? (float)$var_price_raw : null;
        $sizeNames = $_POST['size_names'] ?? [];
        $sizeStocks = $_POST['size_stocks'] ?? [];

        mysqli_begin_transaction($conn);
        try {
            // First, clear existing inventory for this color
            $del_stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id = ? AND color_name = ?");
            $del_stmt->bind_param("is", $product_id, $color_name);
            $del_stmt->execute();
            $del_stmt->close();

            // Update price in product_images for this color
            $upd_price_stmt = $conn->prepare("UPDATE product_images SET price = ? WHERE product_id = ? AND color_name = ?");
            $upd_price_stmt->bind_param("dis", $var_price, $product_id, $color_name);
            $upd_price_stmt->execute();
            $upd_price_stmt->close();

            // Now insert the updated inventory
            $inv_stmt = $conn->prepare("INSERT INTO product_inventory (product_id, color_name, size_name, stock, price) VALUES (?, ?, ?, ?, ?)");
            for ($s = 0; $s < count($sizeNames); $s++) {
                $sName = clean_input($conn, $sizeNames[$s]);
                if (strtolower($sName) === 'standard' || strtolower($sName) === 'default') $sName = '';
                $sStockRaw = $sizeStocks[$s] ?? '';
                $sStock = (int)$sStockRaw;
                if ($sName !== '' || $sStockRaw !== '') {
                    $inv_stmt->bind_param("issid", $product_id, $color_name, $sName, $sStock, $var_price);
                    $inv_stmt->execute();
                }
            }
            $inv_stmt->close();

            $stock_res = mysqli_query($conn, "SELECT SUM(stock) as t_stock FROM product_inventory WHERE product_id = $product_id");
            $t_stock = mysqli_fetch_assoc($stock_res)['t_stock'] ?? 0;
            mysqli_query($conn, "UPDATE products SET stock = $t_stock WHERE id = $product_id");

            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: product_colors.php?id=$product_id&error=" . urlencode("Error updating inventory: " . $e->getMessage()));
            exit;
        }

        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Stock and variation price updated successfully."));
        exit;
    }

    if ($action === 'add_gallery_image') {
        $product_id = (int)$_POST['product_id'];
        $prod_res = mysqli_query($conn, "SELECT name FROM products WHERE id = $product_id");
        $prod_name = ($prod_res && mysqli_num_rows($prod_res) > 0) ? mysqli_fetch_assoc($prod_res)['name'] : 'product';

        if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
            $total_images = count($_FILES['gallery_images']['name']);
            for ($i = 0; $i < $total_images; $i++) {
                if ($_FILES['gallery_images']['error'][$i] == 0) {
                    $ext = pathinfo($_FILES['gallery_images']['name'][$i], PATHINFO_EXTENSION);
                    $imageName = generate_product_image_name($prod_name . '-gallery' . ($i > 0 ? "-$i" : ""), $ext);
                    if (optimize_and_save_image($_FILES['gallery_images']['tmp_name'][$i], '../uploads/' . $imageName)) {
                        mysqli_query($conn, "INSERT INTO product_images (product_id, color_name, image_path, sizes) VALUES ($product_id, '', '$imageName', '')");
                    }
                }
            }
            header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Gallery images added successfully."));
            exit;
        }
        header("Location: product_colors.php?id=$product_id&error=" . urlencode("No images selected."));
        exit;
    }

    if ($action === 'save_page_content') {
        $slug = clean_input($conn, $_POST['slug'] ?? '');
        $title = clean_input($conn, $_POST['title'] ?? '');
        $subtitle = clean_input($conn, $_POST['subtitle'] ?? '');
        $content = isset($_POST['content']) ? mysqli_real_escape_string($conn, trim($_POST['content'])) : '';
        $remove_banner = !empty($_POST['remove_banner']);
        
        if (empty($slug) || empty($title)) {
            header("Location: content.php?tab=" . urlencode($slug) . "&error=" . urlencode("Page title and slug are required."));
            exit;
        }

        $bannerImageUpdate = "";
        
        // Handle Banner Removal
        $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];
        if ($remove_banner) {
            $oldRes = mysqli_query($conn, "SELECT banner_image FROM pages WHERE slug = '$slug'");
            if ($oldRes && $oldRow = mysqli_fetch_assoc($oldRes)) {
                if (!empty($oldRow['banner_image']) && !in_array(strtolower($oldRow['banner_image']), $protected_files) && file_exists('../uploads/' . $oldRow['banner_image'])) {
                    @unlink('../uploads/' . $oldRow['banner_image']);
                }
            }
            $bannerImageUpdate = ", banner_image = NULL";
        }
        
        // Check if new banner image is being uploaded/provided
        $hasNewBanner = (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) || !empty($_POST['banner_image_url']);
        if ($hasNewBanner) {
            $oldRes = mysqli_query($conn, "SELECT banner_image FROM pages WHERE slug = '$slug'");
            if ($oldRes && $oldRow = mysqli_fetch_assoc($oldRes)) {
                if (!empty($oldRow['banner_image']) && !in_array(strtolower($oldRow['banner_image']), $protected_files) && file_exists('../uploads/' . $oldRow['banner_image'])) {
                    @unlink('../uploads/' . $oldRow['banner_image']);
                }
            }
        }

        // Rename banner image according to the Title of the content (e.g. "About Us" -> "about-us-banner.jpg")
        $bannerPrefix = $title . '-banner';
        $bannerImg = process_image_upload_or_url('banner_image', 'banner_image_url', $bannerPrefix);
        if ($bannerImg) {
            $bannerImageUpdate = ", banner_image = '$bannerImg'";
        }

        $chk = mysqli_query($conn, "SELECT id FROM pages WHERE slug = '$slug' LIMIT 1");
        if ($chk && mysqli_num_rows($chk) > 0) {
            mysqli_query($conn, "UPDATE pages SET title = '$title', subtitle = '$subtitle', content = '$content' $bannerImageUpdate WHERE slug = '$slug'");
        } else {
            $bannerVal = !empty($bannerImg) ? "'$bannerImg'" : "NULL";
            mysqli_query($conn, "INSERT INTO pages (slug, title, subtitle, content, banner_image) VALUES ('$slug', '$title', '$subtitle', '$content', $bannerVal)");
        }

        header("Location: content.php?tab=" . urlencode($slug) . "&msg=" . urlencode("Content for '$title' updated successfully."));
        exit;
    }

    // --- New Modules Actions --- //

    if ($action === 'update_stock') {
        $product_id = (int)$_POST['product_id'];
        $stock = (int)$_POST['stock'];
        mysqli_query($conn, "UPDATE products SET stock = $stock WHERE id = $product_id");
        echo "Success";
        exit;
    }

    if ($action === 'add_coupon') {
        $code = clean_input($conn, $_POST['code']);
        $type = clean_input($conn, $_POST['discount_type']);
        $val = (float)$_POST['discount_value'];
        $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : 'NULL';
        $exp = empty($_POST['expiry_date']) ? 'NULL' : "'" . clean_input($conn, $_POST['expiry_date']) . "'";
        mysqli_query($conn, "INSERT INTO coupons (product_id, code, discount_type, discount_value, expiry_date) VALUES ($product_id, '$code', '$type', $val, $exp)");
        header("Location: discounts.php?msg=" . urlencode("Coupon created successfully."));
        exit;
    }

    if ($action === 'toggle_coupon') {
        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'];
        mysqli_query($conn, "UPDATE coupons SET is_active = $status WHERE id = $id");
        header("Location: discounts.php?msg=" . urlencode("Coupon status updated."));
        exit;
    }

    if ($action === 'delete_coupon') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM coupons WHERE id = $id");
        header("Location: discounts.php?msg=" . urlencode("Coupon deleted."));
        exit;
    }

    if ($action === 'delete_customer') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role = 'user'");
        header("Location: customers.php?msg=" . urlencode("Customer account deleted."));
        exit;
    }

    if ($action === 'update_ticket') {
        $id = (int)$_POST['id'];
        $status = clean_input($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE support_tickets SET status = '$status' WHERE id = $id");
        header("Location: support.php?msg=" . urlencode("Ticket status updated."));
        exit;
    }

    if ($action === 'delete_ticket') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM support_tickets WHERE id = $id");
        header("Location: support.php?msg=" . urlencode("Ticket deleted."));
        exit;
    }

    if ($action === 'add_staff' || $action === 'add_admin') {
        $fname = clean_input($conn, $_POST['first_name']);
        $lname = clean_input($conn, $_POST['last_name']);
        $email = clean_input($conn, $_POST['email']);
        $role = clean_input($conn, $_POST['role'] ?? 'admin');
        if (!in_array($role, ['admin', 'store_manager', 'rider'])) {
            $role = 'admin';
        }
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            header("Location: roles.php?error=" . urlencode("User with email '$email' already exists."));
            exit;
        }

        mysqli_query($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES ('$fname', '$lname', '$email', '$pass', '$role')");
        header("Location: roles.php?msg=" . urlencode("Staff member added successfully."));
        exit;
    }

    if ($action === 'update_staff') {
        $id = (int)$_POST['id'];
        $fname = clean_input($conn, $_POST['first_name']);
        $lname = clean_input($conn, $_POST['last_name']);
        $email = clean_input($conn, $_POST['email']);
        
        // Check if email already used by another user
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $ref = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'roles.php';
            $ref_url = explode('?', $ref)[0];
            header("Location: $ref_url?error=" . urlencode("Email '$email' is already in use."));
            exit;
        }

        $updates = [
            "first_name = '$fname'",
            "last_name = '$lname'",
            "email = '$email'"
        ];

        if (isset($_POST['phone'])) {
            $phone = clean_input($conn, $_POST['phone']);
            $updates[] = "phone = '$phone'";
        }

        if (!empty($_POST['password'])) {
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $updates[] = "password = '$pass'";
        }

        if (!empty($_POST['role']) && in_array($_POST['role'], ['admin', 'store_manager', 'rider', 'user'])) {
            $role = clean_input($conn, $_POST['role']);
            $updates[] = "role = '$role'";
        }

        $update_sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $id";
        mysqli_query($conn, $update_sql);

        // If the logged-in admin updated their own name, update the session name
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['first_name'] = $fname;
        }

        $ref = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'roles.php';
        $ref_url = explode('?', $ref)[0];
        header("Location: $ref_url?msg=" . urlencode("Account details updated successfully."));
        exit;
    }

    if ($action === 'revoke_staff' || $action === 'revoke_admin') {
        $id = (int)$_POST['id'];
        if ($id === (int)$_SESSION['user_id']) {
            header("Location: roles.php?error=" . urlencode("You cannot remove or revoke your own account."));
            exit;
        }
        mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role IN ('admin', 'store_manager', 'rider')");
        header("Location: roles.php?msg=" . urlencode("Staff member removed successfully."));
        exit;
    }

    if ($action === 'assign_rider') {
        $order_id = (int)$_POST['order_id'];
        $rider_id = !empty($_POST['rider_id']) ? (int)$_POST['rider_id'] : 'NULL';
        
        mysqli_query($conn, "UPDATE orders SET rider_id = $rider_id WHERE id = $order_id");
        
        if ($rider_id !== 'NULL') {
            $rider_res = mysqli_query($conn, "SELECT email, first_name, last_name FROM users WHERE id = $rider_id");
            if ($rider_res && $rider_row = mysqli_fetch_assoc($rider_res)) {
                $order_q = mysqli_query($conn, "SELECT order_number FROM orders WHERE id = $order_id");
                $ord_num = ($order_q && mysqli_num_rows($order_q) > 0) ? mysqli_fetch_assoc($order_q)['order_number'] : '#' . $order_id;
                
                require_once __DIR__ . '/../core/mailer.php';
                $subject = "New Delivery Assigned - " . $ord_num;
                $dashboard_link = BASE_URL . "admin/rider_dashboard.php";
                
                $html = "<div style='text-align: center; margin-bottom: 24px;'>";
                $html .= "<h2 style='margin: 0 0 6px; color: #012a5e; font-size: 22px; font-weight: 800;'>New Delivery Assigned</h2>";
                $html .= "<p style='margin: 0; color: #64748b; font-size: 14px;'>Order <strong>" . htmlspecialchars($ord_num) . "</strong></p>";
                $html .= "</div>";
                $html .= "<p>Hello <strong>" . htmlspecialchars($rider_row['first_name']) . "</strong>,</p>";
                $html .= "<p>You have been assigned to deliver order <strong>" . htmlspecialchars($ord_num) . "</strong>. Please review customer contact info, delivery address, and items in your dashboard.</p>";
                $html .= "<div style='text-align: center; margin: 30px 0 10px;'>";
                $html .= "<a href='$dashboard_link' style='background-color: #012a5e; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(1, 42, 94, 0.25);'>Open Rider Dashboard &rarr;</a>";
                $html .= "</div>";
                sendMail($rider_row['email'], $rider_row['first_name'] . ' ' . $rider_row['last_name'], $subject, $html);
            }
        }
        
        header("Location: orders.php?msg=" . urlencode("Rider assignment updated successfully."));
        exit;
    }

    if ($action === 'add_page') {
        $title = clean_input($conn, $_POST['title']);
        $slug = clean_input($conn, $_POST['slug']);
        $content = clean_input($conn, $_POST['content']); // simple escape
        mysqli_query($conn, "INSERT INTO pages (title, slug, content) VALUES ('$title', '$slug', '$content')");
        header("Location: content.php?msg=" . urlencode("Page created."));
        exit;
    }

    if ($action === 'update_page') {
        $id = (int)$_POST['id'];
        $title = clean_input($conn, $_POST['title']);
        $slug = clean_input($conn, $_POST['slug']);
        $content = clean_input($conn, $_POST['content']);
        mysqli_query($conn, "UPDATE pages SET title='$title', slug='$slug', content='$content' WHERE id=$id");
        header("Location: content.php?msg=" . urlencode("Page updated."));
        exit;
    }

    if ($action === 'delete_page') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM pages WHERE id = $id");
        header("Location: content.php?msg=" . urlencode("Page deleted."));
        exit;
    }

    if ($action === 'clear_notifications') {
        mysqli_query($conn, "TRUNCATE TABLE admin_notifications");
        header("Location: notifications.php?msg=" . urlencode("Notifications cleared."));
        exit;
    }

    if ($action === 'update_settings') {
        $delivery_days_updated = false;
        $new_delivery_days = 0;
        
        foreach($_POST['settings'] as $key => $val) {
            $k = clean_input($conn, $key);
            $v = clean_input($conn, $val);
            if ($k === 'global_delivery_days') {
                $delivery_days_updated = true;
                $new_delivery_days = (int)$v;
            }
            $check = mysqli_query($conn, "SELECT id FROM settings WHERE setting_key = '$k'");
            if ($check && mysqli_num_rows($check) > 0) {
                mysqli_query($conn, "UPDATE settings SET setting_value = '$v' WHERE setting_key = '$k'");
            } else {
                mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$k', '$v')");
            }
        }
        
        if ($delivery_days_updated) {
            if ($new_delivery_days > 0) {
                mysqli_query($conn, "UPDATE orders SET delivery_deadline = DATE_ADD(created_at, INTERVAL $new_delivery_days DAY) WHERE status NOT IN ('Delivered', 'Cancelled', 'Declined', 'Failed') AND deleted_at IS NULL");
            } else {
                mysqli_query($conn, "UPDATE orders SET delivery_deadline = NULL WHERE status NOT IN ('Delivered', 'Cancelled', 'Declined', 'Failed') AND deleted_at IS NULL");
            }
        }
        
        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'settings.php';
        $sep = (strpos($redirect, '?') !== false) ? '&' : '?';
        header("Location: " . $redirect . $sep . "msg=" . urlencode("Settings updated successfully."));
        exit;
    }

    if ($action === 'update_order_settings') {
        $delivery_days_updated = false;
        $new_delivery_days = 0;
        
        foreach($_POST['settings'] as $key => $val) {
            $k = clean_input($conn, $key);
            $v = clean_input($conn, $val);
            if ($k === 'global_delivery_days') {
                $delivery_days_updated = true;
                $new_delivery_days = (int)$v;
            }
            $check = mysqli_query($conn, "SELECT id FROM settings WHERE setting_key = '$k'");
            if ($check && mysqli_num_rows($check) > 0) {
                mysqli_query($conn, "UPDATE settings SET setting_value = '$v' WHERE setting_key = '$k'");
            } else {
                mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$k', '$v')");
            }
        }
        
        // Sync active orders delivery_deadline automatically
        if ($delivery_days_updated) {
            if ($new_delivery_days > 0) {
                mysqli_query($conn, "UPDATE orders SET delivery_deadline = DATE_ADD(created_at, INTERVAL $new_delivery_days DAY) WHERE status NOT IN ('Delivered', 'Cancelled', 'Declined', 'Failed') AND deleted_at IS NULL");
            } else {
                mysqli_query($conn, "UPDATE orders SET delivery_deadline = NULL WHERE status NOT IN ('Delivered', 'Cancelled', 'Declined', 'Failed') AND deleted_at IS NULL");
            }
        }
        
        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : 'orders.php';
        $sep = (strpos($redirect, '?') !== false) ? '&' : '?';
        header("Location: " . $redirect . $sep . "msg=" . urlencode("Order settings & translations updated successfully. Active order deadlines have been synchronized."));
        exit;
    }

    if ($action === 'update_appearance') {
        $font = clean_input($conn, $_POST['font_family']);
        $remove_bg = !empty($_POST['remove_bg_image']);
        
        $check = mysqli_query($conn, "SELECT id FROM settings WHERE setting_key = 'site_font_family'");
        if ($check && mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE settings SET setting_value = '$font' WHERE setting_key = 'site_font_family'");
        } else {
            mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('site_font_family', '$font')");
        }

        $protected_files = ['default_product.jpg', 'logo.png', 'icon.png', 'placeholder.png', 'default_avatar.png'];

        // Handle Background Image Removal
        if ($remove_bg) {
            $res = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_background_image'");
            if ($res && $row = mysqli_fetch_assoc($res)) {
                $oldBg = $row['setting_value'];
                if (!empty($oldBg) && !in_array(strtolower($oldBg), $protected_files)) {
                    $path = '../uploads/' . $oldBg;
                    if (file_exists($path) && is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            mysqli_query($conn, "UPDATE settings SET setting_value = '' WHERE setting_key = 'site_background_image'");
        }

        // Handle Background Image Upload (named "hero-background-image")
        if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] == 0) {
            $ext = pathinfo($_FILES['bg_image']['name'], PATHINFO_EXTENSION);
            
            // Fetch and delete old bg_image file on replacement
            $res = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'site_background_image'");
            $oldBgFile = ($res && $row = mysqli_fetch_assoc($res)) ? $row['setting_value'] : '';
            if (!empty($oldBgFile) && !in_array(strtolower($oldBgFile), $protected_files)) {
                $oldPath = '../uploads/' . $oldBgFile;
                if (file_exists($oldPath) && is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            
            // Rename specifically according to requirement: "hero-background-image"
            $imageName = generate_product_image_name('hero-background-image', $ext);
            
            if (optimize_and_save_image($_FILES['bg_image']['tmp_name'], '../uploads/' . $imageName)) {
                mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('site_background_image', '$imageName') ON DUPLICATE KEY UPDATE setting_value = '$imageName'");
            }
        }
        
        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == 0) {
            $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
            $logoName = 'logo.png';
            if (function_exists('optimize_and_save_image')) {
                optimize_and_save_image($_FILES['logo_image']['tmp_name'], '../uploads/' . $logoName, 800, 90);
            } else {
                move_uploaded_file($_FILES['logo_image']['tmp_name'], '../uploads/' . $logoName);
            }
        }
        
        header("Location: roles.php?msg=" . urlencode("Site appearance updated successfully."));
        exit;
    }
}

=======
        header("Location: product_colors.php?id=$product_id&msg=" . urlencode("Color image deleted."));
        exit;
    }
}
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
