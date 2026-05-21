<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to access checkout."));
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: shop.php?error=" . urlencode("Your cart is empty."));
    exit;
}

$total = 0;
$product_ids = array_unique(array_column($_SESSION['cart'], 'product_id'));
$ids = empty($product_ids) ? '0' : implode(',', array_map('intval', $product_ids));
$result = mysqli_query($conn, "SELECT id, name, price, image FROM products WHERE id IN ($ids)");
$products_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products_data[$row['id']] = $row;
}

$color_images = [];
$img_res = mysqli_query($conn, "SELECT product_id, color_name, image_path FROM product_images WHERE product_id IN ($ids)");
while ($img_row = mysqli_fetch_assoc($img_res)) {
    $color_images[$img_row['product_id']][$img_row['color_name']] = $img_row['image_path'];
}

foreach ($_SESSION['cart'] as $item) {
    if (isset($products_data[$item['product_id']])) {
        $total += $item['quantity'] * $products_data[$item['product_id']]['price'];
    }
}
?>

<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-7">
      <form action="core/cart_actions.php" method="POST" id="checkoutForm">
          <input type="hidden" name="action" value="checkout">
          
          <!-- Shipping Details -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-map-marker-alt me-2" style="color: var(--accent);"></i>Shipping Details</span></div>
            <div class="p-4">
              <div class="row g-3">
                <div class="col-12"><label class="form-label-custom">Full Name</label><input class="form-input" type="text" name="ship_name" required></div>
                <div class="col-12"><label class="form-label-custom">Street Address</label><input class="form-input" type="text" name="ship_addr" required></div>
                <div class="col-6"><label class="form-label-custom">City</label><input class="form-input" type="text" name="ship_city" required></div>
                <div class="col-6"><label class="form-label-custom">State</label><input class="form-input" type="text" name="ship_state" required></div>
                <div class="col-6"><label class="form-label-custom">ZIP Code</label><input class="form-input" type="text" name="ship_zip" required></div>
                <div class="col-6"><label class="form-label-custom">Country</label><input class="form-input" type="text" name="ship_country" required></div>
              </div>
            </div>
          </div>

          <!-- Delivery Method -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-truck me-2" style="color: var(--accent);"></i>Delivery Method</span></div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="delivery-option payment-method selected" onclick="selectOption(this, 'shipping_method'); calculateTotal();">
                            <input type="radio" name="shipping_method" value="Home Delivery" checked style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-home" style="color: var(--text3);"></i>
                                <div>
                                    <div class="fw-bold" style="color: var(--primary);">Home Delivery</div>
                                    <small class="text-muted">2,000 RFW</small>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="delivery-option payment-method" onclick="selectOption(this, 'shipping_method'); calculateTotal();">
                            <input type="radio" name="shipping_method" value="Fast Delivery" style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-shipping-fast" style="color: var(--text3);"></i>
                                <div>
                                    <div class="fw-bold" style="color: var(--primary);">Fast Delivery</div>
                                    <small class="text-muted">5,000 RFW</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-credit-card me-2" style="color: var(--accent);"></i>Payment Method</span></div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="payment-option payment-method selected" onclick="selectOption(this, 'payment_method')">
                            <input type="radio" name="payment_method" value="MTN Mobile Money" checked style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-mobile-alt" style="color: var(--text3);"></i>
                                <div>
                                    <div class="fw-bold mb-1" style="color: var(--primary); line-height: 1;">MTN Mobile Money</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">*182*1*1#</small>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="payment-option payment-method" onclick="selectOption(this, 'payment_method')">
                            <input type="radio" name="payment_method" value="Cash on Delivery" style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-money-bill-wave" style="color: var(--text3);"></i>
                                <div>
                                    <div class="fw-bold mb-1" style="color: var(--primary); line-height: 1;">Cash on Delivery</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Pay at door</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
          </div>

          <!-- Order Notes -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-clipboard-list me-2" style="color: var(--accent);"></i>Order Notes (Optional)</span></div>
            <div class="p-4">
                <textarea class="form-input" name="order_notes" rows="3" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
            </div>
          </div>

      </form>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-5">
        <div class="order-summary-card">
            <h4 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif; color: var(--primary);">Order Summary</h4>
            <div class="cart-items-summary mb-4">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php if (isset($products_data[$item['product_id']])): 
                        $p = $products_data[$item['product_id']];
                        $sub = $item['quantity'] * $p['price'];
                        $display_image = $p['image'];
                        if (!empty($item['color']) && isset($color_images[$item['product_id']][$item['color']])) {
                            $display_image = $color_images[$item['product_id']][$item['color']];
                        }
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted overflow-hidden" style="width: 50px; height: 50px;">
                                <?php if(!empty($display_image) && file_exists('uploads/' . $display_image)): ?>
                                    <img src="uploads/<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-box"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:0.9rem; color: var(--text);"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    Qty: <?= $item['quantity'] ?>
                                    <?php if (!empty($item['color'])): ?>
                                        | Color: <?= htmlspecialchars($item['color']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="fw-bold text-accent"><?= number_format($sub, 0) ?> RFW</div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="order-row">
                <span class="text-muted">Subtotal</span>
                <span class="fw-bold" style="color: var(--text);" id="summarySubtotal" data-subtotal="<?= $total ?>"><?= number_format($total, 0) ?> RFW</span>
            </div>
            <div class="order-row">
                <span class="text-muted">Discount (10%)</span>
                <span class="fw-bold text-success" id="summaryDiscount">-<?= number_format($total * 0.10, 0) ?> RFW</span>
            </div>
            <div class="order-row">
                <span class="text-muted">Shipping</span>
                <span class="fw-bold text-primary" id="summaryShipping">2,000 RFW</span>
            </div>
            <div class="order-row mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                <span style="font-size: 1.1rem; color: var(--text); font-weight: 600;">Total</span>
                <span class="fs-4" style="color: var(--accent); font-family: 'Playfair Display', serif; font-weight: 700;" id="summaryTotal"><?= number_format($total + 2000 - ($total * 0.10), 0) ?> RFW</span>
            </div>
            
            <button type="submit" form="checkoutForm" class="btn-hero w-100 mt-4">Place Order</button>
        </div>
    </div>
  </div>
</div>

<script>
function selectOption(element, groupName) {
    // Remove selected class from all options in this group
    document.querySelectorAll('input[name="'+groupName+'"]').forEach(radio => {
        radio.closest('.payment-method').classList.remove('selected');
        radio.closest('.payment-method').querySelector('i:first-child').style.color = 'var(--text3)';
    });
    // Add selected class to clicked option
    element.classList.add('selected');
    element.querySelector('i:first-child').style.color = 'var(--accent)';
    // Check the radio input
    element.querySelector('input[type="radio"]').checked = true;
}
</script>
<script>
function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('summarySubtotal').getAttribute('data-subtotal'));
    const discount = subtotal * 0.10;
    
    // Determine shipping cost
    let shipping = 2000;
    const expressRadio = document.querySelector('input[name="shipping_method"][value="Fast Delivery"]');
    if (expressRadio && expressRadio.checked) {
        shipping = 5000;
    }
    
    // Update UI
    document.getElementById('summaryShipping').innerText = new Intl.NumberFormat().format(shipping) + ' RFW';
    
    const finalTotal = subtotal - discount + shipping;
    document.getElementById('summaryTotal').innerText = new Intl.NumberFormat().format(finalTotal) + ' RFW';
}

// Run on page load
window.addEventListener('DOMContentLoaded', calculateTotal);
</script>

<?php require_once 'includes/footer.php'; ?>
