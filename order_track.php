<?php
require_once 'core/config.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order_number_get = isset($_GET['order_number']) ? clean_input($conn, $_GET['order_number']) : '';

if ($order_id === 0 && !empty($order_number_get)) {
    $q_get = "SELECT id FROM orders WHERE order_number = '$order_number_get'";
    $res_get = mysqli_query($conn, $q_get);
    if ($res_get && mysqli_num_rows($res_get) > 0) {
        $row_get = mysqli_fetch_assoc($res_get);
        $order_id = $row_get['id'];
    }
}

$is_guest_owner = false;

// Handle Search Submission
$search_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_order'])) {
    $search_input = trim($_POST['order_number']);
    $search_input = clean_input($conn, $search_input);
    
    if (is_numeric($search_input)) {
        $q = "SELECT id, order_number, user_id FROM orders WHERE id = " . (int)$search_input;
    } else {
        $q = "SELECT id, order_number, user_id FROM orders WHERE order_number = '$search_input'";
    }
    
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $order_owner_id = $row['user_id'];
        
        // Permission check: if the order belongs to a registered user, only they can track it.
        if (!empty($order_owner_id) && $order_owner_id != 0) {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $order_owner_id) {
                $search_error = __('access_denied_order');
                $order_id = 0;
            }
        }
        
        if (empty($search_error)) {
            $found_id = $row['id'];
            $found_num = !empty($row['order_number']) ? $row['order_number'] : $found_id;
            $_SESSION['last_order_id'] = $found_id; // Authenticate them for this session
            header("Location: order_track.php?order_number=" . urlencode($found_num));
            exit;
        }
    } else {
        $search_error = __('order_not_found_short');
        $order_id = 0; // Force to show form
    }
}

if ($order_id !== 0) {
    $chk_owner_q = mysqli_query($conn, "SELECT user_id FROM orders WHERE id = $order_id");
    if ($chk_owner_q && $chk_owner_row = mysqli_fetch_assoc($chk_owner_q)) {
        $order_user_id = $chk_owner_row['user_id'];
        if (empty($order_user_id) || (isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] == $order_id)) {
            $is_guest_owner = true;
        } elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $order_user_id) {
            $is_guest_owner = false;
        } else {
            header("Location: index.php?error=" . urlencode(__('login_to_track')));
            exit;
        }
    } else {
        $order_id = 0;
    }
}

// All redirects are done. We can safely include the header now.
require_once 'includes/header.php';
?>

<style>
    .track-hero {
        text-align: center;
        padding: 40px 15px 20px;
    }
    .track-title {
        font-weight: 800;
        font-size: 2.2rem;
        color: var(--text);
        margin-bottom: 5px;
    }
    .track-subtitle {
        color: var(--text2);
        font-size: 1rem;
    }
    .track-search-container {
        max-width: 650px;
        margin: 0 auto 40px;
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 15px;
    }
    .track-input {
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg3);
        color: var(--text);
        padding: 12px 20px;
        flex: 1;
        outline: none;
        font-size: 1rem;
    }
    .track-input::placeholder {
        color: var(--text3);
    }
    .track-btn {
        background-color: var(--accent);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }
    .track-btn:hover {
        background-color: var(--accent2);
        transform: translateY(-2px);
    }

    .track-result-card {
        max-width: 800px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        background: var(--bg2);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid var(--border);
    }
    .track-result-header {
        background-color: var(--accent);
        color: white;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .track-result-header .label {
        font-size: 0.85rem;
        opacity: 0.8;
        margin-bottom: 5px;
    }
    .track-result-header .value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .track-result-body {
        padding: 30px;
    }
    
    .track-details-row {
        display: flex;
        margin-bottom: 15px;
    }
    .track-details-label {
        width: 120px;
        color: var(--text2);
    }
    .track-details-value {
        font-weight: 600;
        color: var(--text);
        flex: 1;
    }
    
    .track-items-table th {
        background: var(--bg3);
        color: var(--text2);
        font-size: 0.85rem;
        text-transform: uppercase;
        padding: 10px;
        border-bottom: 1px solid var(--border);
    }
    .track-items-table td {
        padding: 15px 10px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }
    .track-items-table tr:last-child td {
        border-bottom: none;
    }
    .track-item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    
    @media (max-width: 768px) {
        .track-title {
            font-size: 1.8rem;
        }
        .track-details-row {
            flex-direction: column;
        }
        .track-details-label {
            width: 100%;
            margin-bottom: 2px;
        }
        .track-result-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        .track-result-header .text-end {
            text-align: center !important;
        }
        
        /* Mobile Table Styles */
        .track-items-table thead {
            display: none;
        }
        .track-items-table tbody tr {
            display: block;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 10px;
        }
        .track-items-table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border);
        }
        .track-items-table tbody td:last-child {
            border-bottom: none;
        }
        .track-items-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--text2);
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .track-items-table tbody td:first-child {
            display: block;
            text-align: left;
        }
        .track-items-table tbody td:first-child::before {
            display: none;
        }
        
        .track-items-table tfoot tr {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }
        .track-items-table tfoot tr:last-child {
            border-bottom: none;
        }
        .track-items-table tfoot td {
            display: block;
            padding: 10px 0 !important;
            border: none !important;
            text-align: right;
        }
        .track-items-table tfoot td[colspan="3"] {
            text-align: left;
        }
    }
</style>

<div class="container py-4" style="min-height: 70vh;">
    <div class="track-hero">
        <h2 class="track-title"><?= __('track_your_order') ?></h2>
        <p class="track-subtitle"><?= __('track_order_subtitle') ?></p>
    </div>

    <div class="track-search-container shadow-sm">
        <?php if ($search_error): ?>
            <div class="alert alert-danger py-2 mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($search_error) ?></div>
        <?php endif; ?>
        <form action="order_track.php" method="POST" class="d-flex flex-column flex-sm-row gap-2 w-100">
            <input type="hidden" name="search_order" value="1">
            <input type="text" name="order_number" class="track-input" placeholder="ORD-545514-825" required>
            <button type="submit" class="track-btn"><i class="fas fa-search me-2"></i> <?= __('track') ?></button>
        </form>
    </div>

    <?php
    if ($order_id !== 0) {
        $query = "
            SELECT o.*, 
                   p.name AS province_name, 
                   d.name AS district_name, 
                   s.name AS sector_name, 
                   c.name AS cell_name, 
                   v.name AS village_name
            FROM orders o
            LEFT JOIN rwanda_locations p ON o.shipping_province = p.id
            LEFT JOIN rwanda_locations d ON o.shipping_district = d.id
            LEFT JOIN rwanda_locations s ON o.shipping_sector = s.id
            LEFT JOIN rwanda_locations c ON o.shipping_cell = c.id
            LEFT JOIN rwanda_locations v ON o.shipping_village = v.id
            WHERE o.id = $order_id " . ($is_guest_owner || empty($_SESSION['user_id']) ? "" : "AND o.user_id = " . (int)$_SESSION['user_id']) . "
        ";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $order = mysqli_fetch_assoc($result);
            
            // Fetch order items
            $items_query = "SELECT oi.*, p.name, p.image AS default_image, 
                            (SELECT image_path FROM product_images WHERE product_id = oi.product_id AND color_name = oi.color LIMIT 1) AS color_image 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = $order_id";
            $items_result = mysqli_query($conn, $items_query);
            
            $items = [];
            $itemsSubtotal = 0;
            while ($item = mysqli_fetch_assoc($items_result)) {
                $itemsSubtotal += $item['price'] * $item['quantity'];
                $items[] = $item;
            }
            
            $shippingOther = $order['total_amount'] - $itemsSubtotal;
            if ($shippingOther < 0) $shippingOther = 0;
            
            $status = strtolower($order['status'] ?? 'pending');
            $current_step = 0;
            if ($status === 'pending' || $status === 'awaiting payment') $current_step = 0;
            elseif ($status === 'payment confirmed') $current_step = 1;
            elseif ($status === 'processing' || $status === 'shipped') $current_step = 2;
            elseif ($status === 'delivered') $current_step = 3;
            else $current_step = 0; // Default or cancelled

            $location_str = '';
            if (!empty($order['province_name'])) {
                $parts = array_filter([$order['village_name'], $order['cell_name'], $order['sector_name'], $order['district_name'], $order['province_name']]);
                $location_str = implode(', ', $parts);
            } else {
                $parts = array_filter([$order['shipping_address'], $order['shipping_city'], $order['shipping_state']], function($val) {
                    return !empty($val) && $val !== 'N/A';
                });
                $location_str = implode(', ', $parts);
            }
            if (empty($location_str)) $location_str = "N/A";
            
            $display_order_number = !empty($order['order_number']) ? $order['order_number'] : "ORD-" . $order['id'];
            ?>
            <div class="track-result-card">
                <div class="track-result-header">
                    <div>
                        <div class="label"><?= __('order_number') ?></div>
                        <div class="value"><?= htmlspecialchars($display_order_number) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="label"><?= __('total') ?></div>
                        <div class="value"><?= number_format($order['total_amount'], 0) ?> RWF</div>
                    </div>
                </div>
                
                <div class="track-result-body">
                    <h5 class="fw-bold mb-3" style="color: var(--text);"><?= __('order_status') ?> <span class="badge" style="background-color: var(--accent);"><?= htmlspecialchars(ucwords($status)) ?></span></h5>
                    <hr style="border-color: var(--border); margin: 20px 0 30px;">
                    
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="track-details-row">
                                <div class="track-details-label"><?= __('customer') ?></div>
                                <div class="track-details-value"><?= htmlspecialchars($order['shipping_name']) ?></div>
                            </div>
                            <div class="track-details-row">
                                <div class="track-details-label"><?= __('phone') ?></div>
                                <div class="track-details-value"><?= htmlspecialchars($order['shipping_phone']) ?></div>
                            </div>
                            <div class="track-details-row">
                                <div class="track-details-label"><?= __('time') ?></div>
                                <div class="track-details-value"><?= date('M j, Y, g:i A', strtotime($order['created_at'])) ?></div>
                            </div>
                            <?php if ($order['delivery_deadline'] && $status !== 'cancelled' && $status !== 'delivered'): 
                                $deadline_ts = strtotime($order['delivery_deadline']);
                                $diff_sec = $deadline_ts - time();
                                $days_left = floor($diff_sec / 86400);
                                $hours_left = floor(($diff_sec % 86400) / 3600);
                                
                                $custom_title = !empty($is_rw) ? get_setting('auto_cancel_title_rw') : get_setting('auto_cancel_title_en');
                                $cancel_title = !empty($custom_title) ? $custom_title : __('auto_cancel_deadline');
                                
                                $custom_msg = !empty($is_rw) ? get_setting('auto_cancel_message_rw') : get_setting('auto_cancel_message_en');
                                $cancel_msg = !empty($custom_msg) ? $custom_msg : __('auto_cancel_notice');
                                
                                if ($diff_sec > 0) {
                                    if (!empty($is_rw)) {
                                        if ($days_left > 0) {
                                            $remain_text = "Hasigaye iminsi {$days_left}" . ($hours_left > 0 ? " n'amasaha {$hours_left}" : '');
                                        } else {
                                            $remain_text = "Hasigaye amasaha " . max(1, $hours_left);
                                        }
                                    } else {
                                        if ($days_left > 0) {
                                            $remain_text = $days_left . ' day' . ($days_left > 1 ? 's' : '') . ($hours_left > 0 ? ', ' . $hours_left . ' hr' . ($hours_left > 1 ? 's' : '') : '') . ' remaining';
                                        } else {
                                            $remain_text = max(1, $hours_left) . ' hour' . ($hours_left > 1 ? 's' : '') . ' remaining';
                                        }
                                    }
                                } else {
                                    $remain_text = __('expired_deadline');
                                }
                            ?>
                            <div class="track-details-row">
                                <div class="track-details-label text-danger fw-bold"><?= htmlspecialchars($cancel_title) ?></div>
                                <div class="track-details-value text-danger fw-bold">
                                    <i class="far fa-clock me-1"></i> <?= date('M j, Y, g:i A', $deadline_ts) ?>
                                    <span class="badge bg-danger text-white rounded-pill ms-2 px-2 py-1 shadow-sm" style="font-size: 0.78rem;">
                                        <i class="fas fa-hourglass-half me-1"></i> <?= $remain_text ?>
                                    </span>
                                    <?php if (!empty($cancel_msg)): ?>
                                    <div class="small fw-normal text-muted mt-1" style="font-size: 0.82rem; line-height: 1.35;">
                                        <i class="fas fa-info-circle me-1 text-danger"></i> <?= htmlspecialchars($cancel_msg) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="track-details-row align-items-center">
                                <div class="track-details-label"><?= __('payment') ?></div>
                                <div class="track-details-value">
                                    <?= htmlspecialchars($order['payment_method']) ?>
                                    <br>
                                    <span class="badge" style="background-color: var(--bg3); color: var(--text); border: 1px solid var(--border); margin-top: 5px; font-weight: normal;"><?= __('payment_on_delivery') ?></span>
                                </div>
                            </div>
                            <div class="track-details-row">
                                <div class="track-details-label"><?= __('address') ?></div>
                                <div class="track-details-value"><?= htmlspecialchars($location_str) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <hr style="border-color: var(--border); margin: 30px 0 20px;">
                    <h5 class="fw-bold mb-3" style="color: var(--text);"><?= __('order_details') ?></h5>
                    
                    <div class="table-responsive">
                        <table class="table track-items-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th><?= __('product') ?></th>
                                    <th><?= __('price') ?></th>
                                    <th><?= __('qty') ?></th>
                                    <th class="text-end"><?= __('total') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    $img = !empty($item['color_image']) ? $item['color_image'] : $item['default_image'];
                                    if(empty($img)) $img = 'default_product.jpg';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="uploads/<?= htmlspecialchars($img) ?>" class="track-item-img" alt="<?= htmlspecialchars($item['name']) ?>">
                                            <div>
                                                <div class="fw-bold" style="color: var(--text);"><?= htmlspecialchars($item['name']) ?></div>
                                                <?php if(!empty($item['color']) || !empty($item['size'])): ?>
                                                    <div style="font-size: 0.85rem; color: var(--text2);">
                                                        <?= htmlspecialchars(trim($item['color'] . ' ' . $item['size'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="<?= __('price') ?>"><?= number_format($item['price'], 0) ?> RWF</td>
                                    <td data-label="<?= __('qty') ?>"><?= $item['quantity'] ?></td>
                                    <td data-label="<?= __('total') ?>" class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity'], 0) ?> RWF</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="border-top: 2px solid var(--border);">
                                <tr>
                                    <td colspan="3" class="text-end" style="color: var(--text2); padding: 15px 10px 5px;"><?= __('subtotal') ?></td>
                                    <td class="text-end fw-bold" style="color: var(--text); padding: 15px 10px 5px;"><?= number_format($itemsSubtotal, 0) ?> RWF</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end" style="color: var(--text2); padding: 5px 10px;"><?= __('shipping_fee') ?></td>
                                    <td class="text-end fw-bold" style="color: var(--text); padding: 5px 10px;"><?= number_format($shippingOther, 0) ?> RWF</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end" style="color: var(--text); padding: 5px 10px 15px; font-size: 1.1rem; font-weight: 800;"><?= __('total') ?></td>
                                    <td class="text-end fw-bold text-primary" style="padding: 5px 10px 15px; font-size: 1.1rem;"><?= number_format($order['total_amount'], 0) ?> RWF</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
            <?php
        } else {
            echo "<div class='alert alert-warning text-center mx-auto' style='max-width: 650px;'><i class='fas fa-exclamation-triangle me-2'></i>" . __('order_not_found') . "</div>";
        }
    }
    ?>
</div>

<?php require_once 'includes/footer.php'; ?>
