<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'remove_item') {
        $cart_key = clean_input($conn, $_POST['cart_key']);
        if (isset($_SESSION['cart'][$cart_key])) {
            unset($_SESSION['cart'][$cart_key]);
        }
        header("Location: ../cart_view.php?msg=" . urlencode("Item removed from cart."));
        exit;
    }

    if ($action === 'checkout') {
<<<<<<< HEAD
        // Check global order status
        $status_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_status'");
        $order_status = ($status_q && mysqli_num_rows($status_q) > 0) ? mysqli_fetch_assoc($status_q)['setting_value'] : 'enable';
        
        if ($order_status === 'disable') {
            $msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_message'");
            $disable_msg = ($msg_q && mysqli_num_rows($msg_q) > 0) ? mysqli_fetch_assoc($msg_q)['setting_value'] : 'Ordering is temporarily disabled.';
            header("Location: ../cart_view.php?error=" . urlencode($disable_msg));
=======
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php?error=" . urlencode("Please login to checkout."));
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            exit;
        }

        if (empty($_SESSION['cart'])) {
<<<<<<< HEAD
            header("Location: ../index.php");
            exit;
        }

        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
        
        $shipping_name = clean_input($conn, $_POST['shipping_name'] ?? '');
        $guest_email = clean_input($conn, $_POST['guest_email'] ?? '');
        
        $country_code = clean_input($conn, $_POST['country_code'] ?? '+250');
        $phone_number = clean_input($conn, $_POST['shipping_phone'] ?? '');
        $shipping_phone = $country_code . ' ' . $phone_number;
        
        $shipping_province = (int)($_POST['shipping_province'] ?? 0);
        $shipping_district = (int)($_POST['shipping_district'] ?? 0);
        $shipping_sector = (int)($_POST['shipping_sector'] ?? 0);
        $shipping_cell = (int)($_POST['shipping_cell'] ?? 0);
        $shipping_village = (int)($_POST['shipping_village'] ?? 0);
        $shipping_gate = mb_substr(clean_input($conn, $_POST['shipping_gate'] ?? ''), 0, 250);
        
        $delivery_location_type = $_POST['delivery_location_type'] ?? 'code';
        $location_code = clean_input($conn, $_POST['location_code'] ?? '');
        
        $delivery_method_id = (int)($_POST['delivery_method_id'] ?? 0);
        $applied_coupon_code = clean_input($conn, $_POST['applied_coupon'] ?? '');

        $payment_method = clean_input($conn, $_POST['payment_method'] ?? 'Cash on Delivery');
        $order_notes = isset($_POST['order_notes']) ? clean_input($conn, $_POST['order_notes']) : '';

        // Calculate shipping fee from the selected location chain (fallback)
        $shipping_cost = 0;
        
        $location_chain = [
            $shipping_village,
            $shipping_cell,
            $shipping_sector,
            $shipping_district,
            $shipping_province
        ];
        
        $loc_names = [0=>'', 1=>'', 2=>'', 3=>'', 4=>''];
        
        foreach ($location_chain as $idx => $loc_id) {
            if ($loc_id > 0) {
                $loc_q = mysqli_query($conn, "SELECT name, delivery_fee FROM rwanda_locations WHERE id = $loc_id");
                if ($loc_q && mysqli_num_rows($loc_q) > 0) {
                    $loc_row = mysqli_fetch_assoc($loc_q);
                    $loc_names[$idx] = $loc_row['name'];
                    $fee = (float)$loc_row['delivery_fee'];
                    if ($fee > 0 && $shipping_cost == 0) {
                        $shipping_cost = $fee;
                    }
                }
            }
        }
        
        $shipping_address = '';
        $shipping_city = '';
        $shipping_state = '';
        $shipping_country = 'Rwanda';

        if ($delivery_location_type === 'gate' || !empty($shipping_gate)) {
            $shipping_address = !empty($shipping_gate) ? $shipping_gate : ltrim(trim($loc_names[0] . ', ' . $loc_names[1], ' ,'), ',');
            $shipping_city = !empty($loc_names[3]) ? ($loc_names[2] ? $loc_names[2] . ', ' . $loc_names[3] : $loc_names[3]) : 'Kigali';
            $shipping_state = !empty($loc_names[4]) ? $loc_names[4] : 'Kigali';
        } else if ($delivery_location_type === 'code') {
            $shipping_address = $location_code;
            $shipping_city = 'N/A';
            $shipping_state = 'N/A';
        } else {
            // location_chain is [village, cell, sector, district, province]
            $shipping_address = ltrim(trim($loc_names[0] . ', ' . $loc_names[1], ' ,'), ','); // village, cell
            $shipping_city = ltrim(trim($loc_names[2] . ', ' . $loc_names[3], ' ,'), ',');    // sector, district
            $shipping_state = $loc_names[4];                          // province
        }

        // Add delivery method extra cost
        $shipping_method_name = 'Standard Delivery';
        if ($delivery_method_id > 0) {
            $dm_q = mysqli_query($conn, "SELECT name, price FROM delivery_methods WHERE id = $delivery_method_id");
            if ($dm_q && mysqli_num_rows($dm_q) > 0) {
                $dm_row = mysqli_fetch_assoc($dm_q);
                $shipping_cost += (float)$dm_row['price'];
                $shipping_method_name = $dm_row['name'];
            }
        }

=======
            header("Location: ../shop.php?error=" . urlencode("Your cart is empty."));
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $ship_name = clean_input($conn, $_POST['ship_name']);
        $ship_addr = clean_input($conn, $_POST['ship_addr']);
        $ship_city = clean_input($conn, $_POST['ship_city']);
        $ship_state = clean_input($conn, $_POST['ship_state']);
        $ship_zip = clean_input($conn, $_POST['ship_zip']);
        $ship_country = clean_input($conn, $_POST['ship_country']);
        $payment_method = clean_input($conn, $_POST['payment_method']);
        $shipping_method = clean_input($conn, $_POST['shipping_method']);
        $order_notes = isset($_POST['order_notes']) ? clean_input($conn, $_POST['order_notes']) : '';

>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        // Calculate total
        $total = 0;
        $product_ids = array_unique(array_column($_SESSION['cart'], 'product_id'));
        $ids = empty($product_ids) ? '0' : implode(',', array_map('intval', $product_ids));
<<<<<<< HEAD
        $result = mysqli_query($conn, "SELECT id, price, discount_price, name FROM products WHERE id IN ($ids)");
=======
        $result = mysqli_query($conn, "SELECT id, price FROM products WHERE id IN ($ids)");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        $products_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products_data[$row['id']] = $row;
        }

        $cart_items = [];
        foreach ($_SESSION['cart'] as $item) {
            $pid = $item['product_id'];
            if (isset($products_data[$pid])) {
                $qty = $item['quantity'];
<<<<<<< HEAD
                $actual_price = (!empty($item['custom_price']) && (float)$item['custom_price'] > 0) ? (float)$item['custom_price'] : ((!empty($products_data[$pid]['discount_price']) && $products_data[$pid]['discount_price'] > 0) ? $products_data[$pid]['discount_price'] : $products_data[$pid]['price']);
                $name = $products_data[$pid]['name'];
                $color = isset($item['color']) ? $item['color'] : '';
                $size = isset($item['size']) ? $item['size'] : '';
                $total += $qty * $actual_price;
                $cart_items[] = ['id' => $pid, 'qty' => $qty, 'price' => $actual_price, 'name' => $name, 'color' => $color, 'size' => $size];
            }
        }

        // Calculate discount
        $discount = 0;
        if (!empty($applied_coupon_code)) {
            $coupon_q = mysqli_query($conn, "SELECT discount_type, discount_value, expiry_date, product_id FROM coupons WHERE code = '$applied_coupon_code' AND is_active = 1");
            if ($coupon_q && mysqli_num_rows($coupon_q) > 0) {
                $coupon_row = mysqli_fetch_assoc($coupon_q);
                $today = date('Y-m-d');
                if (empty($coupon_row['expiry_date']) || $today <= $coupon_row['expiry_date']) {
                    
                    $eligible_subtotal = $total;
                    $c_pid = $coupon_row['product_id'];
                    
                    if (!empty($c_pid)) {
                        $eligible_subtotal = 0;
                        foreach ($cart_items as $itm) {
                            if ($itm['id'] == $c_pid) {
                                $eligible_subtotal += $itm['qty'] * $itm['price'];
                            }
                        }
                    }
                    
                    if ($eligible_subtotal > 0) {
                        if ($coupon_row['discount_type'] === 'percentage') {
                            $discount = $eligible_subtotal * ((float)$coupon_row['discount_value'] / 100);
                        } else {
                            $discount = min((float)$coupon_row['discount_value'], $eligible_subtotal);
                        }
                    }
                }
            }
        }
        
        if ($discount > $total) {
            $discount = $total;
        }

        $final_total = $total + $shipping_cost - $discount;

        // Generate a unique order number (e.g. Ord-878-FG67)
        $order_number = 'Ord-' . mt_rand(100, 999) . '-' . strtoupper(bin2hex(random_bytes(2)));

        // Get global delivery days
        $res = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'global_delivery_days'");
        $global_delivery_days = 0;
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $global_delivery_days = (int)$row['setting_value'];
        }
        $delivery_deadline_sql = "NULL";
        if ($global_delivery_days > 0) {
            $delivery_deadline_sql = "DATE_ADD(NOW(), INTERVAL $global_delivery_days DAY)";
        }

        // Insert Order
        $sql = "INSERT INTO orders (order_number, user_id, total_amount, shipping_name, guest_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_country, shipping_province, shipping_district, shipping_sector, shipping_cell, shipping_village, shipping_gate, shipping_method, delivery_method_id, payment_method, order_notes, delivery_deadline) 
                VALUES ('$order_number', $user_id, '$final_total', '$shipping_name', '$guest_email', '$shipping_phone', '$shipping_address', '$shipping_city', '$shipping_state', '$shipping_country', '$shipping_province', '$shipping_district', '$shipping_sector', '$shipping_cell', '$shipping_village', '$shipping_gate', '$shipping_method_name', '$delivery_method_id', '$payment_method', '$order_notes', $delivery_deadline_sql)";
        
        if (mysqli_query($conn, $sql)) {
            $order_id = mysqli_insert_id($conn);
            $_SESSION['last_order_id'] = $order_id;
            // Insert Items & Deduct Stock
            foreach ($cart_items as $item) {
                $pid = (int)$item['id'];
                $qty = (int)$item['qty'];
                $price = (float)$item['price'];
                $color = clean_input($conn, $item['color'] ?? '');
                $size = clean_input($conn, $item['size'] ?? '');

                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, color, size, quantity, price) VALUES ('$order_id', '$pid', '$color', '$size', '$qty', '$price')");

                // Deduct stock from main product
                mysqli_query($conn, "UPDATE products SET stock = GREATEST(0, stock - $qty) WHERE id = $pid");

                // Deduct stock from variant if specific color/size
                if (!empty($color) || !empty($size)) {
                    $where_inv = "product_id = $pid";
                    if (!empty($color)) $where_inv .= " AND color_name = '$color'";
                    if (!empty($size)) $where_inv .= " AND size_name = '$size'";
                    mysqli_query($conn, "UPDATE product_inventory SET stock = GREATEST(0, stock - $qty) WHERE $where_inv");
                }
            }

            // Mark stock as deducted on this order
            mysqli_query($conn, "UPDATE orders SET stock_deducted = 1 WHERE id = $order_id");

            // Clear Cart
            unset($_SESSION['cart']);
            
            // Get customer details for notifications
            $to = '';
            $first_name = '';
            
            if ($user_id !== 'NULL') {
                $user_query = mysqli_query($conn, "SELECT email, first_name FROM users WHERE id = $user_id");
                if ($user_query && mysqli_num_rows($user_query) > 0) {
                    $u_row = mysqli_fetch_assoc($user_query);
                    $to = $u_row['email'];
                    $first_name = $u_row['first_name'];
                }
            } else {
                $to = $guest_email;
                $first_name = $shipping_name;
=======
                $price = $products_data[$pid]['price'];
                $color = isset($item['color']) ? $item['color'] : '';
                $total += $qty * $price;
                $cart_items[] = ['id' => $pid, 'qty' => $qty, 'price' => $price, 'color' => $color];
            }
        }

        // Calculate shipping and discount
        $shipping_cost = ($shipping_method === 'Fast Delivery') ? 5000 : 2000;
        $discount = $total * 0.10;
        $final_total = $total + $shipping_cost - $discount;

        // Insert Order
        $sql = "INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, shipping_method, payment_method, order_notes) 
                VALUES ('$user_id', '$final_total', '$ship_name', '$ship_addr', '$ship_city', '$ship_state', '$ship_zip', '$ship_country', '$shipping_method', '$payment_method', '$order_notes')";
        
        if (mysqli_query($conn, $sql)) {
            $order_id = mysqli_insert_id($conn);

            // Insert Items
            foreach ($cart_items as $item) {
                $pid = $item['id'];
                $qty = $item['qty'];
                $price = $item['price'];
                $color = clean_input($conn, $item['color']);
                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, color, quantity, price) VALUES ('$order_id', '$pid', '$color', '$qty', '$price')");
                
                // Reduce stock
                mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            }

            // Clear Cart
            unset($_SESSION['cart']);
<<<<<<< HEAD
            
            $notif_name = !empty($first_name) ? $first_name : $shipping_name;
            $notif_msg = clean_input($conn, "New order placed by $notif_name ($order_number) for " . number_format($final_total, 0) . " RFW");
            mysqli_query($conn, "INSERT INTO admin_notifications (message, link) VALUES ('$notif_msg', 'orders.php')");

            // Send Email Confirmation
            if (!empty($to)) {
                require_once __DIR__ . '/mailer.php';

                $subject = "Order Confirmation - " . $order_number;
                $track_link = BASE_URL . "order_track.php?id=" . $order_id;

                $html = "<div style='text-align: center; margin-bottom: 24px;'>";
                $html .= "<h2 style='margin: 0 0 6px; color: #012a5e; font-size: 24px; font-weight: 800;'>Thank You for Your Order!</h2>";
                $html .= "<p style='margin: 0; color: #16a34a; font-size: 15px; font-weight: 600;'>&#10004; Order " . htmlspecialchars($order_number) . " Placed Successfully</p>";
                $html .= "</div>";
                $html .= "<p>Hello <strong>" . htmlspecialchars($first_name) . "</strong>,</p>";
                $html .= "<p>We have received your order and are currently preparing it for delivery across Rwanda. Below is your complete order summary:</p>";
                
                $html .= "<div style='background-color: #f8fafc; padding: 20px; border-radius: 12px; margin: 24px 0; border: 1px solid #e2e8f0;'>";
                $html .= "<h3 style='margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; color: #012a5e; font-size: 16px;'>Order Items</h3>";
                $html .= "<table style='width: 100%; border-collapse: collapse;'>";
                
                foreach ($cart_items as $itm) {
                    $details = [];
                    if (!empty($itm['color'])) $details[] = "Color: " . htmlspecialchars($itm['color']);
                    if (!empty($itm['size'])) $details[] = "Size: " . htmlspecialchars($itm['size']);
                    $detail_str = !empty($details) ? "<br><small style='color:#64748b; font-size: 12px;'>" . implode(" | ", $details) . "</small>" : "";
                    
                    $html .= "<tr>";
                    $html .= "<td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; color: #1e293b;'><strong>" . htmlspecialchars($itm['name']) . "</strong>" . $detail_str . "<br><span style='color: #64748b; font-size: 13px;'>Qty: " . $itm['qty'] . " &times; " . number_format($itm['price']) . " RFW</span></td>";
                    $html .= "<td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: top; font-weight: 600; color: #012a5e;'>" . number_format($itm['qty'] * $itm['price']) . " RFW</td>";
                    $html .= "</tr>";
                }

                $html .= "</table>";
                $html .= "<table style='width: 100%; margin-top: 15px;'>";
                $html .= "<tr><td style='color: #64748b; padding: 4px 0;'>Subtotal</td><td style='text-align: right; font-weight: 600; padding: 4px 0;'>" . number_format($total) . " RFW</td></tr>";
                if ($discount > 0) {
                    $html .= "<tr><td style='color: #16a34a; padding: 4px 0;'>Discount</td><td style='text-align: right; color: #16a34a; font-weight: 600; padding: 4px 0;'>-" . number_format($discount) . " RFW</td></tr>";
                }
                $html .= "<tr><td style='color: #64748b; padding: 4px 0;'>Shipping (" . htmlspecialchars($shipping_method_name) . ")</td><td style='text-align: right; font-weight: 600; padding: 4px 0;'>" . number_format($shipping_cost) . " RFW</td></tr>";
                $html .= "<tr><td style='font-weight: 800; padding-top: 12px; border-top: 2px solid #e2e8f0; font-size: 17px; color: #012a5e;'>Total Paid / Due</td><td style='text-align: right; font-weight: 800; padding-top: 12px; border-top: 2px solid #e2e8f0; font-size: 18px; color: #fb7c00;'>" . number_format($final_total) . " RFW</td></tr>";
                $html .= "</table>";
                $html .= "</div>";

                $html .= "<div style='background-color: #f8fafc; padding: 16px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #e2e8f0; font-size: 13.5px;'>";
                $html .= "<div style='margin-bottom: 6px;'><strong><i class='fas fa-map-marker-alt'></i> Delivery Destination:</strong> " . htmlspecialchars($shipping_address . ', ' . $shipping_city . ', ' . $shipping_state) . "</div>";
                $html .= "<div style='margin-bottom: 6px;'><strong><i class='fas fa-truck'></i> Delivery Option:</strong> " . htmlspecialchars($shipping_method_name) . "</div>";
                $html .= "<div><strong><i class='fas fa-credit-card'></i> Payment Mode:</strong> " . htmlspecialchars($payment_method) . "</div>";
                $html .= "</div>";

                $html .= "<div style='text-align: center; margin: 30px 0 10px;'>";
                $html .= "<a href='" . $track_link . "' style='background-color: #fb7c00; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(251, 124, 0, 0.3);'>Track Your Order &rarr;</a>";
                $html .= "</div>";

                // Send email asynchronously to prevent checkout delay
                $async_url = BASE_URL . "core/async_mail.php";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $async_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['to' => $to, 'name' => $first_name, 'subject' => $subject, 'body' => $html]));
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200); // 200ms timeout
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                @curl_exec($ch);
                curl_close($ch);
            }

            if (isset($_POST['ajax'])) {
                ob_clean(); // Ensure no output before json
                echo json_encode([
                    'status' => 'success',
                    'order_id' => $order_id,
                    'order_number' => $order_number,
                    'email' => $to,
                    'order_status' => 'Pending',
                    'subtotal' => $total,
                    'shipping' => $shipping_cost,
                    'discount' => $discount,
                    'total' => $final_total,
                    'payment_method' => $payment_method
                ]);
                exit;
            }

            if ($user_id !== 'NULL') {
                header("Location: ../order_success.php?id=$order_id");
            } else {
                header("Location: ../order_success.php?id=$order_id");
            }
            exit;
        } else {
            if (isset($_POST['ajax'])) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Order failed to place.']);
                exit;
            }
=======

            // Send Email Confirmation
            $user_query = mysqli_query($conn, "SELECT email, first_name FROM users WHERE id = $user_id");
            if ($user_query && mysqli_num_rows($user_query) > 0) {
                $u_row = mysqli_fetch_assoc($user_query);
                $to = $u_row['email'];
                $subject = "Order Confirmation - #" . $order_id;
                
                $message = "Hello " . $u_row['first_name'] . ",\n\n";
                $message .= "Thank you for shopping with us! Your order has been successfully placed.\n\n";
                $message .= "Order Details:\n";
                $message .= "----------------------\n";
                $message .= "Order ID: #" . $order_id . "\n";
                $message .= "Total Amount: " . number_format($final_total, 0) . " RFW\n";
                $message .= "Shipping Method: " . $shipping_method . "\n";
                $message .= "Payment Method: " . $payment_method . "\n";
                $message .= "----------------------\n\n";
                $message .= "We will notify you once your order is on its way!\n";
                
                $headers = "From: no-reply@zorashop.com\r\n";
                $headers .= "Reply-To: support@zorashop.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                
                // Use @ to suppress errors on local environments without SMTP configured
                @mail($to, $subject, $message, $headers);
            }

            header("Location: ../user_panel.php?msg=" . urlencode("Order placed successfully! Order ID: #$order_id"));
            exit;
        } else {
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            header("Location: ../checkout.php?error=" . urlencode("Order failed to place."));
            exit;
        }
    }
}
