<?php
require_once 'core/config.php';

header('Content-Type: application/json');

if (!isset($_GET['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'No coupon code provided.']);
    exit;
}

$code = clean_input($conn, strtoupper($_GET['code']));

$query = "SELECT * FROM coupons WHERE code = '$code' AND is_active = 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $coupon = mysqli_fetch_assoc($result);
    
    // Check expiry
    if (!empty($coupon['expiry_date'])) {
        $today = date('Y-m-d');
        if ($today > $coupon['expiry_date']) {
            echo json_encode(['status' => 'error', 'message' => 'This coupon has expired.']);
            exit;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'type' => $coupon['discount_type'], // 'percentage' or 'fixed'
        'value' => (float)$coupon['discount_value'],
        'product_id' => $coupon['product_id'] ? (int)$coupon['product_id'] : null,
        'message' => 'Coupon applied successfully!'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or inactive coupon.']);
}
