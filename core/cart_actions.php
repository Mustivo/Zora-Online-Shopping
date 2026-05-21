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
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../index.php?error=" . urlencode("Please login to checkout."));
            exit;
        }

        if (empty($_SESSION['cart'])) {
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

        // Calculate total
        $total = 0;
        $product_ids = array_unique(array_column($_SESSION['cart'], 'product_id'));
        $ids = empty($product_ids) ? '0' : implode(',', array_map('intval', $product_ids));
        $result = mysqli_query($conn, "SELECT id, price FROM products WHERE id IN ($ids)");
        $products_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products_data[$row['id']] = $row;
        }

        $cart_items = [];
        foreach ($_SESSION['cart'] as $item) {
            $pid = $item['product_id'];
            if (isset($products_data[$pid])) {
                $qty = $item['quantity'];
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
            }

            // Clear Cart
            unset($_SESSION['cart']);

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
            header("Location: ../checkout.php?error=" . urlencode("Order failed to place."));
            exit;
        }
    }
}
