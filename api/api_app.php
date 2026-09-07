<?php
// api_app.php - Mobile API for Zora Rwanda App
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// require_once __DIR__ . '/core/config.php';
require_once dirname(__DIR__) . '/core/config.php';
// require_once dirname(__DIR__) . '/includes/header.php';


$action = isset($_GET['action']) ? $_GET['action'] : 'bootstrap';
$base_upload_url = BASE_URL . 'uploads/';

function format_product($p, $base_upload_url) {
    $img = !empty($p['image']) ? $p['image'] : 'default_product.jpg';
    $img_url = $base_upload_url . $img;
    return [
        'id' => (int)$p['id'],
        'name' => $p['name'],
        'category_id' => (int)($p['category_id'] ?? 0),
        'category_name' => $p['cat_name'] ?? 'General',
        'price' => (float)$p['price'],
        'discount_price' => !empty($p['discount_price']) && (float)$p['discount_price'] > 0 ? (float)$p['discount_price'] : null,
        'image' => $img,
        'image_url' => $img_url,
        'stock' => (int)($p['stock'] ?? 0),
        'rating' => (float)($p['rating'] ?? 5.0),
        'description' => $p['description'] ?? '',
        'is_featured' => (bool)($p['is_featured'] ?? 0),
        'is_new' => (bool)($p['is_new'] ?? 0),
        'sizes' => !empty($p['sizes']) ? array_filter(array_map('trim', explode(',', $p['sizes']))) : [],
        'colors' => !empty($p['colors']) ? array_filter(array_map('trim', explode(',', $p['colors']))) : [],
    ];
}

switch ($action) {
    case 'bootstrap':
    case 'home':
        // 1. Categories
        $cat_res = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY product_count DESC LIMIT 12");
        $categories = [];
        if ($cat_res) {
            while ($c = mysqli_fetch_assoc($cat_res)) {
                $categories[] = [
                    'id' => (int)$c['id'],
                    'name' => $c['name'],
                    'icon' => $c['icon'] ?? 'bag-handle-outline',
                    'image' => !empty($c['image']) ? $base_upload_url . $c['image'] : null,
                    'product_count' => (int)$c['product_count']
                ];
            }
        }

        // 2. Trending Products
        $trend_res = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.is_featured DESC, p.rating DESC, p.id DESC LIMIT 8");
        $trending = [];
        if ($trend_res) {
            while ($p = mysqli_fetch_assoc($trend_res)) {
                $trending[] = format_product($p, $base_upload_url);
            }
        }

        // 3. New Arrivals
        $new_res = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC, p.id DESC LIMIT 8");
        $new_arrivals = [];
        if ($new_res) {
            while ($p = mysqli_fetch_assoc($new_res)) {
                $new_arrivals[] = format_product($p, $base_upload_url);
            }
        }

        // 4. All/Popular Products preview
        $all_res = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC LIMIT 16");
        $all_products = [];
        if ($all_res) {
            while ($p = mysqli_fetch_assoc($all_res)) {
                $all_products[] = format_product($p, $base_upload_url);
            }
        }

        // 5. Store Settings & Banners
        $settings = [
            'store_name' => 'Zora Online Shopping',
            'country' => 'Rwanda',
            'city' => 'Kigali',
            'currency' => 'RWF',
            'phone' => '+250 788 000 000',
            'whatsapp' => '+250 788 000 000',
            'email' => 'support@zorashop.rw',
            'banners' => [
                [
                    'id' => 1,
                    'title' => 'Exclusive Rwandan Fashion & Tech',
                    'subtitle' => 'Swift delivery across Kigali & all provinces',
                    'button_text' => 'Shop Now',
                    'tag' => 'Trending'
                ],
                [
                    'id' => 2,
                    'title' => 'Special Deals & New Arrivals',
                    'subtitle' => 'Save up to 40% on select electronics & accessories',
                    'button_text' => 'Browse Deals',
                    'tag' => 'Sale'
                ]
            ]
        ];

        echo json_encode([
            'status' => 'success',
            'settings' => $settings,
            'categories' => $categories,
            'trending' => $trending,
            'new_arrivals' => $new_arrivals,
            'products' => $all_products
        ]);
        break;

    case 'products':
        $cat_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;

        $where = ["1=1"];
        if ($cat_id > 0) {
            $where[] = "p.category_id = $cat_id";
        }
        if (!empty($search)) {
            $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.tags LIKE '%$search%')";
        }
        $where_sql = implode(' AND ', $where);

        $order_sql = "p.id DESC";
        if ($sort === 'price_low') {
            $order_sql = "COALESCE(NULLIF(p.discount_price, 0), p.price) ASC";
        } elseif ($sort === 'price_high') {
            $order_sql = "COALESCE(NULLIF(p.discount_price, 0), p.price) DESC";
        } elseif ($sort === 'popular') {
            $order_sql = "p.rating DESC, p.id DESC";
        }

        $total_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM products p WHERE $where_sql");
        $total_row = mysqli_fetch_assoc($total_res);
        $total = (int)($total_row['total'] ?? 0);

        $query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where_sql ORDER BY $order_sql LIMIT $offset, $limit";
        $res = mysqli_query($conn, $query);
        $items = [];
        if ($res) {
            while ($p = mysqli_fetch_assoc($res)) {
                $items[] = format_product($p, $base_upload_url);
            }
        }

        echo json_encode([
            'status' => 'success',
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'products' => $items
        ]);
        break;

    case 'product':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $res = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $id");
        if (!$res || mysqli_num_rows($res) === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }
        $p = mysqli_fetch_assoc($res);
        $product = format_product($p, $base_upload_url);

        // Additional gallery images
        $img_res = mysqli_query($conn, "SELECT image FROM product_images WHERE product_id = $id");
        $gallery = [$product['image_url']];
        if ($img_res) {
            while ($im = mysqli_fetch_assoc($img_res)) {
                if (!empty($im['image'])) {
                    $gallery[] = $base_upload_url . $im['image'];
                }
            }
        }
        $product['gallery'] = array_values(array_unique($gallery));

        // Related products
        $cat_id = (int)$p['category_id'];
        $rel_res = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id != $id AND p.category_id = $cat_id LIMIT 6");
        $related = [];
        if ($rel_res) {
            while ($rp = mysqli_fetch_assoc($rel_res)) {
                $related[] = format_product($rp, $base_upload_url);
            }
        }

        echo json_encode([
            'status' => 'success',
            'product' => $product,
            'related' => $related
        ]);
        break;

    case 'streets':
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
        $sql = "SELECT id, code, name, district, sector, area, fee FROM kigali_streets";
        if (!empty($search)) {
            $sql .= " WHERE code LIKE '%$search%' OR name LIKE '%$search%' OR sector LIKE '%$search%' OR district LIKE '%$search%'";
        }
        $sql .= " ORDER BY code ASC LIMIT 50";
        $res = mysqli_query($conn, $sql);
        $streets = [];
        if ($res) {
            while ($s = mysqli_fetch_assoc($res)) {
                $streets[] = [
                    'id' => (int)$s['id'],
                    'code' => $s['code'],
                    'name' => $s['name'],
                    'district' => $s['district'],
                    'sector' => $s['sector'],
                    'area' => $s['area'],
                    'fee' => (float)$s['fee']
                ];
            }
        }
        echo json_encode(['status' => 'success', 'streets' => $streets]);
        break;

    case 'validate_coupon':
        $code = isset($_POST['code']) ? trim($_POST['code']) : (isset($_GET['code']) ? trim($_GET['code']) : '');
        $code = mysqli_real_escape_string($conn, strtoupper($code));
        if (empty($code)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter coupon code']);
            exit;
        }
        $now = date('Y-m-d H:i:s');
        $c_res = mysqli_query($conn, "SELECT * FROM coupons WHERE code = '$code' AND (expires_at IS NULL OR expires_at > '$now') LIMIT 1");
        if ($c_res && $coupon = mysqli_fetch_assoc($c_res)) {
            echo json_encode([
                'status' => 'success',
                'coupon' => [
                    'id' => (int)$coupon['id'],
                    'code' => $coupon['code'],
                    'type' => $coupon['type'] ?? 'percentage',
                    'value' => (float)($coupon['value'] ?? $coupon['discount'] ?? 10),
                    'min_spend' => (float)($coupon['min_spend'] ?? 0)
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired coupon code']);
        }
        break;

    case 'login':
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $identifier = mysqli_real_escape_string($conn, trim($data['email'] ?? $data['identifier'] ?? ''));
        $password = $data['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter your email/phone and password']);
            exit;
        }

        $u_res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$identifier' OR phone = '$identifier' LIMIT 1");
        if ($u_res && $user = mysqli_fetch_assoc($u_res)) {
            if (password_verify($password, $user['password'])) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Logged in successfully',
                    'user' => [
                        'id' => (int)$user['id'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'phone' => $user['phone'] ?? '',
                        'role' => $user['role'] ?? 'user'
                    ]
                ]);
                exit;
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        break;

    case 'register':
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $first_name = mysqli_real_escape_string($conn, trim($data['first_name'] ?? ''));
        $last_name = mysqli_real_escape_string($conn, trim($data['last_name'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($data['email'] ?? ''));
        $phone = mysqli_real_escape_string($conn, trim($data['phone'] ?? ''));
        $password = $data['password'] ?? '';

        if (empty($first_name) || empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'First name, email and password are required']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
            exit;
        }

        // Check if email already registered
        $chk = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if ($chk && mysqli_num_rows($chk) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This email is already registered. Please log in.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = "INSERT INTO users (first_name, last_name, email, phone, password, role, created_at) 
                VALUES ('$first_name', '$last_name', '$email', '$phone', '$hash', 'user', NOW())";

        if (mysqli_query($conn, $ins)) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode([
                'status' => 'success',
                'message' => 'Account created successfully!',
                'user' => [
                    'id' => $new_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => 'user'
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to register: ' . mysqli_error($conn)]);
        }
        break;

    case 'place_order':
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            $data = $_POST;
        }

        $items = $data['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['status' => 'error', 'message' => 'Cart items are required']);
            exit;
        }

        $user_id = !empty($data['user_id']) ? (int)$data['user_id'] : "NULL";
        $shipping_name = mysqli_real_escape_string($conn, $data['shipping_name'] ?? 'Guest Customer');
        $shipping_phone = mysqli_real_escape_string($conn, $data['shipping_phone'] ?? '');
        $shipping_address = mysqli_real_escape_string($conn, $data['shipping_address'] ?? 'Kigali');
        $shipping_city = mysqli_real_escape_string($conn, $data['shipping_city'] ?? 'Kigali');
        $payment_method = mysqli_real_escape_string($conn, $data['payment_method'] ?? 'MTN Mobile Money');
        $order_notes = mysqli_real_escape_string($conn, $data['order_notes'] ?? 'Order placed via Zora Mobile App');
        $total_amount = (float)($data['total_amount'] ?? 0);

        $order_number = 'ORD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, shipping_name, shipping_phone, shipping_address, shipping_city, payment_method, status, order_notes, created_at) 
                      VALUES ($user_id, '$order_number', $total_amount, '$shipping_name', '$shipping_phone', '$shipping_address', '$shipping_city', '$payment_method', 'Pending', '$order_notes', NOW())";
        
        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);
            foreach ($items as $item) {
                $pid = (int)($item['id'] ?? $item['product_id'] ?? 0);
                $qty = (int)($item['quantity'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                $size = mysqli_real_escape_string($conn, $item['selected_size'] ?? '');
                $color = mysqli_real_escape_string($conn, $item['selected_color'] ?? '');
                if ($pid > 0) {
                    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price, size, color) VALUES ($order_id, $pid, $qty, $price, '$size', '$color')");
                }
            }

            echo json_encode([
                'status' => 'success',
                'order_id' => $order_id,
                'order_number' => $order_number,
                'message' => 'Order placed successfully! We will contact you on ' . $shipping_phone
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to record order: ' . mysqli_error($conn)]);
        }
        break;

    case 'track_order':
        $query = isset($_GET['query']) ? mysqli_real_escape_string($conn, trim($_GET['query'])) : '';
        if (empty($query)) {
            echo json_encode(['status' => 'error', 'message' => 'Order number or phone is required']);
            exit;
        }

        $o_res = mysqli_query($conn, "SELECT * FROM orders WHERE order_number = '$query' OR shipping_phone = '$query' ORDER BY id DESC LIMIT 1");
        if ($o_res && $ord = mysqli_fetch_assoc($o_res)) {
            $oid = (int)$ord['id'];
            $items_res = mysqli_query($conn, "SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $oid");
            $items = [];
            if ($items_res) {
                while ($it = mysqli_fetch_assoc($items_res)) {
                    $items[] = [
                        'name' => $it['product_name'] ?? 'Product',
                        'quantity' => (int)$it['quantity'],
                        'price' => (float)$it['price'],
                        'image_url' => $base_upload_url . ($it['image'] ?? 'default_product.jpg')
                    ];
                }
            }

            echo json_encode([
                'status' => 'success',
                'order' => [
                    'id' => $oid,
                    'order_number' => $ord['order_number'],
                    'status' => $ord['status'],
                    'total_amount' => (float)$ord['total_amount'],
                    'shipping_name' => $ord['shipping_name'],
                    'shipping_phone' => $ord['shipping_phone'],
                    'shipping_address' => $ord['shipping_address'],
                    'payment_method' => $ord['payment_method'],
                    'created_at' => $ord['created_at'],
                    'items' => $items
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No order found with the provided details']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
