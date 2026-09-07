<?php
require_once 'core/config.php';
require_once 'includes/header.php';

$cart_items = [];
$total = 0;
$category_ids = [];

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_unique(array_column($_SESSION['cart'], 'product_id'));
    $ids = empty($product_ids) ? '0' : implode(',', array_map('intval', $product_ids));
<<<<<<< HEAD
    $result = mysqli_query($conn, "SELECT id, name, price, discount_price, image, category_id FROM products WHERE id IN ($ids)");
=======
    $result = mysqli_query($conn, "SELECT id, name, price, image, category_id FROM products WHERE id IN ($ids)");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    
    $products_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products_data[$row['id']] = $row;
        if ($row['category_id']) {
            $category_ids[] = $row['category_id'];
        }
    }
    
    $color_images = [];
    $img_res = mysqli_query($conn, "SELECT product_id, color_name, image_path FROM product_images WHERE product_id IN ($ids)");
    while ($img_row = mysqli_fetch_assoc($img_res)) {
        $color_images[$img_row['product_id']][$img_row['color_name']] = $img_row['image_path'];
    }

    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $pid = $item['product_id'];
        if (isset($products_data[$pid])) {
            $row = $products_data[$pid];
            $row['cart_key'] = $cart_key;
            $row['quantity'] = $item['quantity'];
<<<<<<< HEAD
            $actual_price = (!empty($item['custom_price']) && (float)$item['custom_price'] > 0) ? (float)$item['custom_price'] : ((!empty($row['discount_price']) && $row['discount_price'] > 0) ? $row['discount_price'] : $row['price']);
            $row['price_to_show'] = $actual_price;
            $row['subtotal'] = $item['quantity'] * $actual_price;
            $row['color'] = $item['color'] ?? '';
            $row['size'] = $item['size'] ?? '';
            
            if (!empty($item['selected_image'])) {
                $row['image'] = $item['selected_image'];
            } elseif (!empty($item['color']) && isset($color_images[$pid][$item['color']])) {
=======
            $row['subtotal'] = $item['quantity'] * $row['price'];
            $row['color'] = $item['color'];
            
            if (!empty($item['color']) && isset($color_images[$pid][$item['color']])) {
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                $row['image'] = $color_images[$pid][$item['color']];
            }
            
            $total += $row['subtotal'];
            $cart_items[] = $row;
        }
    }
}
<<<<<<< HEAD

$status_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_status'");
$store_order_status = ($status_q && mysqli_num_rows($status_q) > 0) ? mysqli_fetch_assoc($status_q)['setting_value'] : 'enable';

$msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_message'");
$store_order_message = ($msg_q && mysqli_num_rows($msg_q) > 0) ? mysqli_fetch_assoc($msg_q)['setting_value'] : 'Ordering is temporarily disabled.';
?>

<div class="container py-5">
    <?php if ($store_order_status !== 'enable'): ?>
    <div class="alert <?= $store_order_status === 'disable' ? 'alert-danger' : 'alert-warning' ?> d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
        <i class="fas <?= $store_order_status === 'disable' ? 'fa-ban' : 'fa-clock' ?> fs-4 me-3"></i>
        <div>
            <strong><?= __('notice') ?></strong> <?= htmlspecialchars($store_order_message) ?>
        </div>
    </div>
    <?php endif; ?>

    <h2 class="section-title mb-4"><?= __('shopping_cart') ?></h2>
=======
?>

<div class="container py-5">
    <h2 class="section-title mb-4">Your Shopping Cart</h2>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart text-muted" style="font-size:4rem"></i>
<<<<<<< HEAD
            <h3 class="mt-4"><?= __('cart_empty') ?></h3>
            <a href="shop.php" class="btn-hero mt-3 text-decoration-none"><?= __('continue_shopping') ?></a>
=======
            <h3 class="mt-4">Your cart is empty</h3>
            <a href="shop.php" class="btn-hero mt-3 text-decoration-none">Continue Shopping</a>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card p-3">
                    <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
<<<<<<< HEAD
                                <th><?= __('image') ?></th>
                                <th><?= __('product') ?></th>
                                <th><?= __('price') ?></th>
                                <th><?= __('quantity') ?></th>
                                <th><?= __('subtotal') ?></th>
                                <th><?= __('action') ?></th>
=======
                                <th>Image</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if(isset($item['image']) && $item['image'] && file_exists('uploads/' . $item['image'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                    <?php else: ?>
                                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:4px;"><i class="fas fa-box text-white"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
<<<<<<< HEAD
                                    <span class="notranslate"><?= htmlspecialchars($item['name']) ?></span>
                                    <?php if(!empty($item['color'])): ?>
                                        <br><small class="text-muted"><?= __('color') ?> <?= htmlspecialchars($item['color']) ?></small>
                                    <?php endif; ?>
                                    <?php if(!empty($item['size'])): ?>
                                        <br><small class="text-muted"><?= __('size') ?> <?= htmlspecialchars($item['size']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['discount_price']) && $item['discount_price'] > 0): ?>
                                        <del class="text-muted d-block" style="font-size:0.8rem;"><?= number_format($item['price'], 0) ?> RFW</del>
                                        <span class="text-danger fw-bold"><?= number_format($item['discount_price'], 0) ?> RFW</span>
                                    <?php else: ?>
                                        <?= number_format($item['price'], 0) ?> RFW
                                    <?php endif; ?>
                                </td>
=======
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?php if(!empty($item['color'])): ?>
                                        <br><small class="text-muted">Color: <?= htmlspecialchars($item['color']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($item['price'], 0) ?> RFW</td>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['subtotal'], 0) ?> RFW</td>
                                <td>
                                    <form action="core/cart_actions.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="cart_key" value="<?= htmlspecialchars($item['cart_key']) ?>">
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="order-summary-card">
<<<<<<< HEAD
                    <div class="admin-card-title mb-3"><?= __('order_summary') ?></div>
                    <div class="order-row"><span class="text-muted-custom"><?= __('subtotal') ?></span><span><?= number_format($total, 0) ?> RFW</span></div>
                    <div class="order-row"><span class="text-muted-custom"><?= __('shipping') ?></span><span class="gold"><?= __('free') ?></span></div>
                    <div class="order-row total"><span><?= __('total') ?></span><span><?= number_format($total, 0) ?> RFW</span></div>
                    
                    <?php if ($store_order_status === 'disable'): ?>
                        <button disabled class="btn btn-secondary w-100 mt-4 text-center border-0 py-3" style="font-weight: 600; cursor: not-allowed;"><?= __('checkout_disabled') ?></button>
                    <?php else: ?>
                        <a href="checkout.php" class="btn-hero w-100 mt-4 text-center text-decoration-none d-block"><?= __('proceed_checkout') ?></a>
=======
                    <div class="admin-card-title mb-3">Order Summary</div>
                    <div class="order-row"><span class="text-muted-custom">Subtotal</span><span><?= number_format($total, 0) ?> RFW</span></div>
                    <div class="order-row"><span class="text-muted-custom">Shipping</span><span class="gold">Free</span></div>
                    <div class="order-row total"><span>Total</span><span><?= number_format($total, 0) ?> RFW</span></div>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn-hero w-100 mt-4 text-center text-decoration-none d-block">Proceed to Checkout</a>
                    <?php else: ?>
                        <button onclick="openAuthModal()" class="btn-hero w-100 mt-4 text-center d-block border-0">Login to Checkout</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php 
        $cat_ids_str = !empty($category_ids) ? implode(',', array_unique($category_ids)) : '0';
<<<<<<< HEAD
        $related_query = "SELECT id, name, price, discount_price, image FROM products WHERE category_id IN ($cat_ids_str) AND id NOT IN ($ids) LIMIT 4";
=======
        $related_query = "SELECT id, name, price, image FROM products WHERE category_id IN ($cat_ids_str) AND id NOT IN ($ids) LIMIT 4";
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
        $related_result = mysqli_query($conn, $related_query);
        if(mysqli_num_rows($related_result) > 0):
        ?>
        <div class="mt-5">
<<<<<<< HEAD
            <h3 class="section-title mb-4" style="font-size: 1.5rem;"><?= __('related_products') ?></h3>
=======
            <h3 class="section-title mb-4" style="font-size: 1.5rem;">Related Products</h3>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            <div class="row g-4">
                <?php while($rp = mysqli_fetch_assoc($related_result)): ?>
                <div class="col-md-3 col-6">
                    <div class="product-card" onclick="window.location.href='product.php?id=<?= $rp['id'] ?>'">
                        <div class="product-image">
                            <?php if(isset($rp['image']) && $rp['image'] && file_exists('uploads/' . $rp['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($rp['image']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-box text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
<<<<<<< HEAD
                            <div class="product-name notranslate"><?= htmlspecialchars($rp['name']) ?></div>

                            <?php if (!empty($rp['discount_price']) && $rp['discount_price'] > 0): ?>
                                <div class="product-price">
                                    <span class="text-danger fw-bold"><?= number_format($rp['discount_price'], 0) ?> RFW</span>
                                    <del class="text-muted ms-2" style="font-size: 0.8rem;"><?= number_format($rp['price'], 0) ?> RFW</del>
                                </div>
                            <?php else: ?>
                                <div class="product-price"><?= number_format($rp['price'], 0) ?> RFW</div>
                            <?php endif; ?>
=======
                            <div class="product-name"><?= htmlspecialchars($rp['name']) ?></div>
                            <div class="product-price"><?= number_format($rp['price'], 0) ?> RFW</div>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                            <form action="core/actions.php" method="POST" class="mt-2" onclick="event.stopPropagation()">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $rp['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
<<<<<<< HEAD
                                <?php
                                global $store_order_status, $store_order_message;
                                $btn_disabled = ($store_order_status === 'disable') ? 'disabled' : '';
                                if ($store_order_status === 'disable') {
                                    $btn_text = htmlspecialchars($store_order_message);
                                } else {
                                    $btn_text = __('add_to_cart');
                                }
                                ?>
                                <button type="submit" class="btn-add-cart w-100 notranslate" <?= $btn_disabled ?>><i class="fas fa-shopping-cart me-2"></i><?= $btn_text ?></button>
=======
                                <button type="submit" class="btn-add-cart w-100">Add to Cart</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
