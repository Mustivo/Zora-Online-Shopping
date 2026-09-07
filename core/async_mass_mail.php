<?php
ignore_user_abort(true);
set_time_limit(0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

$product_id = $_POST['product_id'] ?? 0;
$discount_price = $_POST['discount_price'] ?? 0;

if ($product_id > 0 && $discount_price > 0) {
    // Add a small delay to ensure the calling script has closed the connection
    sleep(1);
    
    // Fetch product details
    $p_query = mysqli_query($conn, "SELECT name, price, image, discount_expiry, stock FROM products WHERE id = " . (int)$product_id);
    if ($p_query && mysqli_num_rows($p_query) > 0) {
        $product = mysqli_fetch_assoc($p_query);
        $product_name = $product['name'];
        $original_price = $product['price'];
        $product_image = $product['image'] ? $product['image'] : 'default_product.jpg';
        $discount_expiry = $product['discount_expiry'];
        $stock = $product['stock'];
        
        $subject = "Special Offer: Discount on " . $product_name . "!";
        $product_link = BASE_URL . "product.php?id=" . $product_id;
        $image_path = __DIR__ . '/../uploads/' . $product_image;
        
        // Fetch all registered users
        $u_query = mysqli_query($conn, "SELECT email, first_name FROM users WHERE role = 'user'");
        if ($u_query && mysqli_num_rows($u_query) > 0) {
            while ($user = mysqli_fetch_assoc($u_query)) {
                $to = $user['email'];
                $first_name = $user['first_name'];
                
                $html = "<div style='text-align: center; margin-bottom: 24px;'>";
                $html .= "<h2 style='margin: 0 0 6px; color: #012a5e; font-size: 24px; font-weight: 800;'>Special Offer Just For You!</h2>";
                $html .= "<p style='margin: 0; color: #fb7c00; font-size: 15px; font-weight: 600;'>Exclusive Price Drop on " . htmlspecialchars($product_name) . "</p>";
                $html .= "</div>";
                $html .= "<p>Hello <strong>" . htmlspecialchars($first_name) . "</strong>,</p>";
                $html .= "<p>We are excited to share a limited-time special offer with you on <strong>Zora Online Shopping Rwanda</strong>:</p>";
                
                $html .= "<div style='background-color: #f8fafc; padding: 24px; text-align: center; border-radius: 14px; margin: 24px 0; border: 1px solid #e2e8f0;'>";
                $html .= "<img src='cid:product_image_cid' alt='" . htmlspecialchars($product_name) . "' style='max-width: 220px; border-radius: 10px; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>";
                $html .= "<h3 style='margin: 5px 0 10px; color: #012a5e; font-size: 18px; font-weight: 800;'>" . htmlspecialchars($product_name) . "</h3>";
                $html .= "<p style='margin: 0 0 6px; color: #64748b; font-size: 14px;'>Original Price: <del>" . number_format($original_price, 0) . " RFW</del></p>";
                $html .= "<p style='margin: 0 0 12px; color: #fb7c00; font-weight: 800; font-size: 22px;'>Deal Price: " . number_format($discount_price, 0) . " RFW</p>";
                
                if ($stock > 0) {
                    $html .= "<p style='margin: 0; color: #16a34a; font-size: 13px; font-weight: 700;'><span style='background: #dcfce7; padding: 4px 10px; border-radius: 50px;'>In Stock (" . (int)$stock . " available)</span></p>";
                }
                
                if (!empty($discount_expiry)) {
                    $html .= "<div style='margin-top: 15px; padding: 10px 14px; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 8px; font-size: 13px; font-weight: 700;'>";
                    $html .= "&#9203; Offer Expires: " . date('F j, Y, g:i a', strtotime($discount_expiry));
                    $html .= "</div>";
                }
                
                $html .= "</div>";

                $html .= "<p style='text-align: center; color: #64748b;'>Hurry and order before stock runs out or the promotion ends!</p>";
                $html .= "<div style='text-align: center; margin: 30px 0 10px;'>";
                $html .= "<a href='" . $product_link . "' style='background-color: #fb7c00; color: #ffffff; padding: 14px 34px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(251, 124, 0, 0.3);'>Claim This Deal &rarr;</a>";
                $html .= "</div>";

                sendMail($to, $first_name, $subject, $html, ['product_image_cid' => $image_path]);
                
                // Pause slightly to avoid rate limits
                usleep(100000); // 0.1s
            }
        }
    }
}
