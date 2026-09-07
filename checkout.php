<?php
require_once 'core/config.php';
require_once 'includes/header.php';

if (empty($_SESSION['cart'])) {
    header("Location: shop.php?error=" . urlencode("Your cart is empty."));
    exit;
}

$is_guest = !isset($_SESSION['user_id']);
$full_name = '';
$email = '';
$phone = '';

if (!$is_guest) {
    $uid = $_SESSION['user_id'];
    $u_q = mysqli_query($conn, "SELECT first_name, last_name, email, phone FROM users WHERE id = $uid");
    if ($u_q && $user_info = mysqli_fetch_assoc($u_q)) {
        $full_name = htmlspecialchars(trim(($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? '')));
        $email = htmlspecialchars($user_info['email'] ?? '');
        $phone = htmlspecialchars($user_info['phone'] ?? '');
    }
}

// Fetch dynamic delivery methods
$del_methods = [];
$dm_q = mysqli_query($conn, "SELECT id, name, price FROM delivery_methods WHERE is_active = 1 ORDER BY price ASC");
if ($dm_q) {
    while($r = mysqli_fetch_assoc($dm_q)){
        $del_methods[] = $r;
    }
}

$total = 0;
$product_ids = array_unique(array_column($_SESSION['cart'], 'product_id'));
$ids = empty($product_ids) ? '0' : implode(',', array_map('intval', $product_ids));
$result = mysqli_query($conn, "SELECT id, name, price, discount_price, image FROM products WHERE id IN ($ids)");

$products_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products_data[$row['id']] = $row;
}

$color_images = [];
$img_res = mysqli_query($conn, "SELECT product_id, color_name, image_path FROM product_images WHERE product_id IN ($ids)");
while ($img_row = mysqli_fetch_assoc($img_res)) {
    $color_images[$img_row['product_id']][$img_row['color_name']] = $img_row['image_path'];
}

$productSubtotals = [];
foreach ($_SESSION['cart'] as $item) {
    if (isset($products_data[$item['product_id']])) {
        $actual_price = (!empty($item['custom_price']) && (float)$item['custom_price'] > 0) ? (float)$item['custom_price'] : ((!empty($products_data[$item['product_id']]['discount_price']) && $products_data[$item['product_id']]['discount_price'] > 0) ? $products_data[$item['product_id']]['discount_price'] : $products_data[$item['product_id']]['price']);
        $total += $item['quantity'] * $actual_price;
        
        if (!isset($productSubtotals[$item['product_id']])) {
            $productSubtotals[$item['product_id']] = 0;
        }
        $productSubtotals[$item['product_id']] += $item['quantity'] * $actual_price;
    }
}

$status_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_status'");
$store_order_status = ($status_q && mysqli_num_rows($status_q) > 0) ? mysqli_fetch_assoc($status_q)['setting_value'] : 'enable';

$msg_q = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'store_order_message'");
$store_order_message = ($msg_q && mysqli_num_rows($msg_q) > 0) ? mysqli_fetch_assoc($msg_q)['setting_value'] : 'Ordering is temporarily disabled.';

// Fetch and localize provinces list directly for fast server-side rendering
$prov_query = mysqli_query($conn, "SELECT id, name, delivery_fee FROM rwanda_locations WHERE type = 'province' ORDER BY name ASC");
$provinces_list = [];
$prov_translations_map = [
    'rw' => [
        'KIGALI' => 'Umujyi wa Kigali',
        'SOUTH' => 'Intara y\'Amajyepfo',
        'WEST' => 'Intara y\'Iburengerazuba',
        'NORTH' => 'Intara y\'Amajyaruguru',
        'EAST' => 'Intara y\'Iburasirazuba',
    ],
    'en' => [
        'KIGALI' => 'Kigali City',
        'SOUTH' => 'Southern Province',
        'WEST' => 'Western Province',
        'NORTH' => 'Northern Province',
        'EAST' => 'Eastern Province',
    ]
];
$curr_lang = $_SESSION['lang'] ?? 'en';
if ($prov_query) {
    while ($pr = mysqli_fetch_assoc($prov_query)) {
        $pKey = strtoupper(trim($pr['name']));
        $pName = $pr['name'];
        if (isset($prov_translations_map[$curr_lang][$pKey])) {
            $pName = $prov_translations_map[$curr_lang][$pKey];
        } elseif (isset($prov_translations_map['en'][$pKey])) {
            $pName = $prov_translations_map['en'][$pKey];
        }
        $provinces_list[] = [
            'id' => $pr['id'],
            'name' => $pName,
            'fee' => (float)$pr['delivery_fee']
        ];
    }
}
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

  <div class="row g-4">
    <div class="col-lg-7">
      <form action="core/cart_actions.php" method="POST" id="checkoutForm">
          <input type="hidden" name="action" value="checkout">
          <!-- Discount Tracking -->
          <input type="hidden" name="applied_coupon" id="applied_coupon" value="">
          
          <!-- Customer Details -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-user me-2" style="color: var(--accent);"></i><?= __('customer_details') ?></span></div>
            <div class="p-4">
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label-custom"><?= __('full_name') ?> <span class="text-danger">*</span></label><input class="form-input" type="text" name="shipping_name" value="<?= $full_name ?>" required placeholder="<?= __('full_name') ?>"></div>
                <div class="col-md-6"><label class="form-label-custom"><?= __('email') ?> <span class="text-danger">*</span></label><input class="form-input" type="email" name="guest_email" value="<?= $email ?>" required placeholder="name@zora-shopping.com"></div>
                <div class="col-12">
                    <label class="form-label mb-2 fw-medium text-muted-custom"><?= __('phone_number') ?> <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2">
                        <select name="country_code" class="form-select form-input" style="width: auto; background: var(--bg3); font-weight: bold;">
                            <option value="+250" selected>RW (+250)</option>
                            <option value="+254">KE (+254)</option>
                            <option value="+255">TZ (+255)</option>
                            <option value="+256">UG (+256)</option>
                            <option value="+257">BI (+257)</option>
                            <option value="+243">CD (+243)</option>
                            <option value="+1">US (+1)</option>
                            <option value="+44">UK (+44)</option>
                            <option value="+33">FR (+33)</option>
                        </select>
                        <input class="form-input" type="text" name="shipping_phone" value="<?= $phone ?>" placeholder="780000000" pattern="^[0-9]{8,15}$" title="Enter a valid phone number without the country code" required>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Delivery Location -->
          <style>
          .location-gradient-text {
              background: linear-gradient(90deg, var(--primary) 0%, rgba(139, 92, 246, 0.8) 100%);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              opacity: 0.95;
          }
          .location-tabs {
              display: flex;
              background: var(--bg3);
              border-radius: 50px;
              padding: 6px;
              gap: 5px;
              width: 100%;
          }
          .loc-tab {
              flex: 1;
              justify-content: center;
              padding: 10px 16px;
              border-radius: 50px;
              font-size: 0.9rem;
              font-weight: 600;
              color: var(--text2);
              cursor: pointer;
              transition: all 0.3s;
              display: flex;
              align-items: center;
              text-align: center;
              border: none;
              background: transparent;
          }
          .loc-tab.active {
              background: linear-gradient(135deg, var(--primary) 0%, rgba(139, 92, 246, 0.85) 100%);
              color: #fff;
              box-shadow: 0 4px 12px rgba(139,92,246,0.3);
          }
          .loc-tab.active i {
              color: #fff !important;
          }
          @media (max-width: 768px) {
              .loc-tab {
                  flex-direction: column;
                  font-size: 0.75rem;
                  padding: 8px 4px;
                  gap: 4px;
                  line-height: 1.2;
              }
              .loc-tab i {
                  margin-right: 0 !important;
                  font-size: 1.2rem;
              }
          }
          
          .payment-method .check-icon {
              display: none;
              color: var(--accent);
          }
          .payment-method.selected .check-icon {
              display: block;
          }
          
          </style>
          <div class="admin-card mb-4 notranslate" translate="no">
            <div class="admin-card-header d-flex align-items-center justify-content-between">
                <span class="admin-card-title location-gradient-text" style="font-size: 1.35rem; font-family: 'Playfair Display', serif; font-weight: 800;">
                    <i class="fas fa-map-marker-alt me-2" style="-webkit-text-fill-color: var(--primary);"></i><?= __('delivery_location') ?>
                </span>
            </div>
            <div class="p-4">
                <input type="hidden" name="delivery_location_type" id="deliveryLocationType" value="dropdowns">
                <input type="hidden" name="shipping_gate" id="input_shipping_gate" value="">
                
                <!-- Delivery Location Dropdowns -->
                <div id="locTabDropdowns">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom"><?= __('province') ?> <span class="text-danger">*</span></label>
                        <select class="form-select-custom" name="shipping_province" id="sel_province" onchange="updateLocationFee('province', this); loadLocations('district', this.value)" required>
                            <option value=""><?= __('select_province') ?></option>
                            <?php foreach($provinces_list as $prv): ?>
                                <option value="<?= $prv['id'] ?>" data-fee="<?= $prv['fee'] ?>"><?= htmlspecialchars($prv['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom"><?= __('district') ?> <span class="text-danger">*</span></label>
                        <select class="form-select-custom" name="shipping_district" id="sel_district" onchange="updateLocationFee('district', this); loadLocations('sector', this.value)" disabled required>
                            <option value=""><?= __('select_district') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom"><?= __('sector') ?> <span class="text-danger">*</span></label>
                        <select class="form-select-custom" name="shipping_sector" id="sel_sector" onchange="updateLocationFee('sector', this); loadLocations('cell', this.value)" disabled required>
                            <option value=""><?= __('select_sector') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom"><?= __('cell') ?> <span class="text-danger">*</span></label>
                        <select class="form-select-custom" name="shipping_cell" id="sel_cell" onchange="updateLocationFee('cell', this); loadLocations('village', this.value)" disabled required>
                            <option value=""><?= __('select_cell') ?></option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom"><?= __('village') ?> <span class="text-danger">*</span></label>
                        <select class="form-select-custom" name="shipping_village" id="sel_village" onchange="villageSelected(this)" disabled required>
                            <option value=""><?= __('select_village') ?></option>
                        </select>
                    </div>
                </div> <!-- row -->
                </div> <!-- locTabDropdowns -->
            </div> <!-- p-4 -->
          </div> <!-- admin-card -->

          <!-- Delivery Method -->
          <div class="admin-card mb-4" id="deliveryMethodContainer" style="display: none; position: relative; z-index: 1;">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-truck me-2" style="color: var(--accent);"></i><?= __('delivery_method') ?></span></div>
            <div class="p-4">
                <div class="row g-3">
                    <?php 
                    $isFirst = true;
                    foreach($del_methods as $dm): 
                        $icon = (stripos($dm['name'], 'Express') !== false || stripos($dm['name'], 'Fast') !== false) ? 'fa-bolt' : 'fa-shipping-fast';
                        $colorClass = (stripos($dm['name'], 'Express') !== false || stripos($dm['name'], 'Fast') !== false) ? 'text-warning' : 'text-primary';
                    ?>
                    <div class="col-md-6">
                        <label class="payment-option payment-method <?= $isFirst ? 'selected' : '' ?>" onclick="selectDeliveryMethod(this, <?= $dm['price'] ?>)">
                            <input type="radio" name="delivery_method_id" value="<?= $dm['id'] ?>" <?= $isFirst ? 'checked' : '' ?> style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas <?= $icon ?> fs-4" style="color: <?= $isFirst ? 'var(--accent)' : 'var(--text3)' ?>;"></i>
                                <div>
                                    <div class="fw-bold mb-1" style="color: var(--primary); line-height: 1;"><?= htmlspecialchars($dm['name']) ?></div>
                                    <small class="text-muted dm-price-display" data-extra="<?= $dm['price'] ?>" style="font-size: 0.75rem;"><?= __('select_location_first') ?></small>
                                </div>
                            </div>
                            <i class="fas fa-check-circle check-icon ms-auto"></i>
                        </label>
                    </div>
                    <?php $isFirst = false; endforeach; ?>
                </div>
            </div>
          </div>
          
          <!-- Payment Method -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-credit-card me-2" style="color: var(--accent);"></i><?= __('payment_method') ?></span></div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="payment-option payment-method selected" onclick="selectOption(this, 'payment_method')">
                            <input type="radio" name="payment_method" value="Momo Pay" checked style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-mobile-alt fs-4" style="color: var(--accent);"></i>
                                <div>
                                    <div class="fw-bold mb-1" style="color: var(--primary); line-height: 1;">Momo Pay</div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">*182*8*1*675349*Amount#</small>
                                    <small class="text-muted d-block fw-bold mt-1" style="font-size: 0.75rem; color: var(--accent) !important;">Victor</small>
                                </div>
                            </div>
                            <i class="fas fa-check-circle check-icon ms-auto"></i>
                        </label>
                    </div>
                      <div class="col-md-6">
                        <label class="payment-option payment-method" onclick="selectOption(this, 'payment_method')">
                            <input type="radio" name="payment_method" value="Cash on Delivery" style="display:none;">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <i class="fas fa-money-bill-wave fs-4" style="color: var(--text3);"></i>
                                <div>
                                    <div class="fw-bold mb-1" style="color: var(--primary); line-height: 1;"><?= __('cash_on_delivery') ?></div>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= __('pay_at_door') ?></small>
                                </div>
                            </div>
                            <i class="fas fa-check-circle check-icon ms-auto"></i>
                        </label>
                    </div>
                </div>
            </div>
          </div>

          <!-- Order Notes -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><span class="admin-card-title"><i class="fas fa-clipboard-list me-2" style="color: var(--accent);"></i><?= __('order_notes') ?></span></div>
            <div class="p-4">
                <textarea class="form-input" name="order_notes" rows="3" placeholder="<?= __('order_notes_placeholder') ?>"></textarea>
            </div>
          </div>

      </form>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-5">
        <div class="order-summary-card position-sticky" style="top: 2rem;">
            <h4 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif; color: var(--primary);"><?= __('order_summary') ?></h4>
            <div class="cart-items-summary mb-4" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php if (isset($products_data[$item['product_id']])): 
                        $p = $products_data[$item['product_id']];
                        $actual_price = (!empty($item['custom_price']) && (float)$item['custom_price'] > 0) ? (float)$item['custom_price'] : ((!empty($p['discount_price']) && $p['discount_price'] > 0) ? $p['discount_price'] : $p['price']);
                        $sub = $item['quantity'] * $actual_price;
                        $display_image = $p['image'];
                        if (!empty($item['selected_image'])) {
                            $display_image = $item['selected_image'];
                        } elseif (!empty($item['color']) && isset($color_images[$item['product_id']][$item['color']])) {
                            $display_image = $color_images[$item['product_id']][$item['color']];
                        }
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom pe-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted overflow-hidden" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <?php if(!empty($display_image) && file_exists('uploads/' . $display_image)): ?>
                                    <img src="uploads/<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-box"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:0.9rem; color: var(--text);"><?= htmlspecialchars($p['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.8rem;">
                                        <?= __('qty') ?> <?= $item['quantity'] ?>
                                        <?php if (!empty($item['color'])): ?>
                                            | <?= __('color') ?> <?= htmlspecialchars($item['color']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($item['size'])): ?>
                                            | <?= __('size') ?> <?= htmlspecialchars($item['size']) ?>
                                        <?php endif; ?>
                                    </div>
                            </div>
                        </div>
                        <div class="fw-bold text-accent" style="white-space: nowrap;"><?= number_format($sub, 0) ?> RFW</div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Promo Code Section -->
            <div class="mb-4 pb-4 border-bottom">
                <label class="form-label-custom"><?= __('promo_code') ?></label>
                <div class="d-flex gap-2">
                    <input type="text" id="promoCodeInput" class="form-input mb-0" placeholder="<?= __('enter_coupon_code') ?>" style="text-transform: uppercase;">
                    <button type="button" class="btn btn-dark px-4" onclick="applyPromoCode()"><?= __('apply') ?></button>
                </div>
                <div id="promoMessage" class="mt-2 small"></div>
            </div>
            
            <div class="order-row">
                <span class="text-muted"><?= __('subtotal') ?></span>
                <span class="fw-bold" style="color: var(--text);" id="summarySubtotal" data-subtotal="<?= $total ?>"><?= number_format($total, 0) ?> RFW</span>
            </div>
            <div class="order-row" id="discountRow" style="display: none;">
                <span class="text-muted">Discount <span id="discountLabel"></span></span>
                <span class="fw-bold text-success" id="summaryDiscount">-0 RFW</span>
            </div>
            <div class="order-row">
                <span class="text-muted"><?= __('shipping') ?></span>
                <span class="fw-bold text-primary" id="summaryShipping"><?= __('select_village') ?></span>
            </div>
            <div class="order-row mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                <span style="font-size: 1.1rem; color: var(--text); font-weight: 600;"><?= __('total') ?></span>
                <span class="fs-4" style="color: var(--accent); font-family: 'Playfair Display', serif; font-weight: 700;" id="summaryTotal"><?= number_format($total, 0) ?> RFW</span>
            </div>
            
            <?php if ($store_order_status === 'disable'): ?>
                <button type="button" disabled class="btn btn-secondary w-100 mt-4 py-3 border-0" style="font-weight: 600; cursor: not-allowed;"><?= __('checkout_disabled') ?></button>
            <?php else: ?>
                <button type="submit" form="checkoutForm" class="btn-hero w-100 mt-4"><?= __('place_order') ?></button>
            <?php endif; ?>
        </div>
    </div>
  </div>
</div>

<script>
let baseLocationFee = 0;
let deliveryMethodExtra = <?php echo isset($del_methods[0]['price']) ? $del_methods[0]['price'] : 0; ?>;
let discountAmount = 0;
let discountType = null;
let discountValue = 0;
let discountProductId = null;
const productSubtotals = <?php echo json_encode($productSubtotals); ?>;

function selectOption(element, groupName) {
    document.querySelectorAll('input[name="'+groupName+'"]').forEach(radio => {
        radio.closest('.payment-method').classList.remove('selected');
        radio.closest('.payment-method').querySelector('i:first-child').style.color = 'var(--text3)';
    });
    element.classList.add('selected');
    element.querySelector('i:first-child').style.color = 'var(--accent)';
    element.querySelector('input[type="radio"]').checked = true;
}

function selectDeliveryMethod(element, extraCost) {
    document.querySelectorAll('input[name="delivery_method_id"]').forEach(radio => {
        radio.closest('.payment-method').classList.remove('selected');
        radio.closest('.payment-method').querySelector('i:first-child').style.color = 'var(--text3)';
    });
    element.classList.add('selected');
    element.querySelector('i:first-child').style.color = 'var(--accent)';
    element.querySelector('input[type="radio"]').checked = true;
    
    deliveryMethodExtra = parseFloat(extraCost) || 0;
    calculateTotal();
}

function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('summarySubtotal').getAttribute('data-subtotal'));
    
    // Calculate Discount
    let eligibleSubtotal = subtotal;
    if (discountProductId) {
        eligibleSubtotal = productSubtotals[discountProductId] || 0;
    }
    
    if (eligibleSubtotal === 0 && discountProductId) {
        discountAmount = 0;
    } else {
        if (discountType === 'percentage') {
            discountAmount = eligibleSubtotal * (discountValue / 100);
        } else if (discountType === 'fixed') {
            discountAmount = Math.min(discountValue, eligibleSubtotal);
        } else {
            discountAmount = 0;
        }
    }
    
    if (discountAmount > subtotal) discountAmount = subtotal; // Prevent negative total
    
    if (discountAmount > 0) {
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('summaryDiscount').innerText = '-' + new Intl.NumberFormat().format(discountAmount) + ' RFW';
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
    
    // Delivery Fee Calculation
    const isDropdownSelected = document.getElementById('sel_village') && document.getElementById('sel_village').value !== '';
    let currentShippingCost = 0;
    let isShippingValid = false;
    
    if (isDropdownSelected) {
        currentShippingCost = baseLocationFee + deliveryMethodExtra;
        isShippingValid = true;
        document.getElementById('summaryShipping').innerText = new Intl.NumberFormat().format(currentShippingCost) + ' RFW';
    } else {
        document.getElementById('summaryShipping').innerText = <?= json_encode(__('select_village')) ?>;
    }
    
    const finalTotal = subtotal - discountAmount + (isShippingValid ? currentShippingCost : 0);
    document.getElementById('summaryTotal').innerText = new Intl.NumberFormat().format(finalTotal) + ' RFW';
}

async function applyPromoCode() {
    const code = document.getElementById('promoCodeInput').value.trim();
    const msgEl = document.getElementById('promoMessage');
    
    if (!code) {
        msgEl.innerText = 'Please enter a code.';
        msgEl.className = 'mt-2 small text-danger';
        return;
    }
    
    msgEl.innerText = 'Checking...';
    msgEl.className = 'mt-2 small text-muted';
    
    try {
        const res = await fetch(`api_coupon.php?code=${encodeURIComponent(code)}`);
        const data = await res.json();
        
        if (data.status === 'success') {
            msgEl.innerText = data.message;
            msgEl.className = 'mt-2 small text-success';
            document.getElementById('applied_coupon').value = code;
            
            discountType = data.type;
            discountValue = parseFloat(data.value);
            discountProductId = data.product_id;
            
            document.getElementById('discountLabel').innerText = discountType === 'percentage' ? `(${discountValue}%)` : '';
            calculateTotal();
        } else {
            msgEl.innerText = data.message;
            msgEl.className = 'mt-2 small text-danger';
            document.getElementById('applied_coupon').value = '';
            discountType = null;
            discountValue = 0;
            discountProductId = null;
            calculateTotal();
        }
    } catch(e) {
        console.error(e);
        msgEl.innerText = 'Error checking coupon.';
        msgEl.className = 'mt-2 small text-danger';
    }
}

let fees = {
    province: 0,
    district: 0,
    sector: 0,
    cell: 0,
    village: 0
};

// Run on page load
window.addEventListener('DOMContentLoaded', () => {
    calculateTotal();
    
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Validate customer information
            const nameInp = document.getElementById('shipping_name');
            const emailInp = document.getElementById('guest_email');
            const phoneInp = document.getElementById('shipping_phone');
            
            if (nameInp && !nameInp.value.trim()) {
                e.preventDefault();
                const msg = <?= json_encode(__('please_fill_all_fields')) ?>;
                if (typeof customAlert === 'function') {
                    customAlert(msg, 'error');
                } else {
                    alert(msg);
                }
                nameInp.focus();
                return false;
            }
            if (emailInp && !emailInp.value.trim()) {
                e.preventDefault();
                const msg = <?= json_encode(__('please_fill_all_fields')) ?>;
                if (typeof customAlert === 'function') {
                    customAlert(msg, 'error');
                } else {
                    alert(msg);
                }
                emailInp.focus();
                return false;
            }
            if (phoneInp && !phoneInp.value.trim()) {
                e.preventDefault();
                const msg = <?= json_encode(__('please_fill_all_fields')) ?>;
                if (typeof customAlert === 'function') {
                    customAlert(msg, 'error');
                } else {
                    alert(msg);
                }
                phoneInp.focus();
                return false;
            }
            
            const prov = document.getElementById('sel_province')?.value;
            const dist = document.getElementById('sel_district')?.value;
            const sec = document.getElementById('sel_sector')?.value;
            const cell = document.getElementById('sel_cell')?.value;
            const vil = document.getElementById('sel_village')?.value;
            
            if (!prov || !dist || !sec || !cell || !vil) {
                e.preventDefault();
                const msg = <?= json_encode(__('select_complete_location')) ?>;
                if (typeof customAlert === 'function') {
                    customAlert(msg, 'error');
                } else {
                    alert(msg);
                }
                return false;
            }
            
            // All validations passed -> safely show processing on button
            const submitBtn = document.querySelector('button[form="checkoutForm"]');
        });
    }
});

function resolveBaseLocationFee() {
    baseLocationFee = fees.village || fees.cell || fees.sector || fees.district || fees.province || 0;
    
    // Update delivery method prices
    document.querySelectorAll('.dm-price-display').forEach(el => {
        const extra = parseFloat(el.getAttribute('data-extra')) || 0;
        el.innerText = new Intl.NumberFormat().format(baseLocationFee + extra) + ' RFW';
    });
    
    calculateTotal();
}

async function loadLocations(type, parent_id) {
    const types = ['province', 'district', 'sector', 'cell', 'village'];
    const idx = types.indexOf(type);
    if (idx === -1) return;
    
    const selectLabels = {
        'province': <?= json_encode(__('select_province')) ?>,
        'district': <?= json_encode(__('select_district')) ?>,
        'sector': <?= json_encode(__('select_sector')) ?>,
        'cell': <?= json_encode(__('select_cell')) ?>,
        'village': <?= json_encode(__('select_village')) ?>
    };
    
    // Reset this level and all lower levels
    for (let i = idx; i < types.length; i++) {
        const el = document.getElementById('sel_' + types[i]);
        if (el) {
            el.innerHTML = '<option value="">' + selectLabels[types[i]] + '</option>';
            el.disabled = true;
        }
        fees[types[i]] = 0;
    }
    
    if (!parent_id) {
        document.getElementById('deliveryMethodContainer').style.display = 'none';
        resolveBaseLocationFee();
        return;
    }
    
    const currentEl = document.getElementById('sel_' + type);
    if (currentEl) {
        currentEl.innerHTML = '<option value="">' + selectLabels[type] + ' (Tegereza...)</option>';
    }
    
    try {
        const url = `api_locations.php?type=${type}&parent_id=${parent_id}&lang=<?= $curr_lang ?>`;
        const res = await fetch(url);
        const json = await res.json();
        
        if (json.status === 'success' && currentEl) {
            currentEl.innerHTML = '<option value="">' + selectLabels[type] + '</option>';
            if (json.data && json.data.length > 0) {
                json.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    opt.dataset.fee = item.delivery_fee;
                    currentEl.appendChild(opt);
                });
                currentEl.disabled = false;
            }
        }
    } catch(e) { 
        console.error('Error loading locations', e); 
        if (currentEl) {
            currentEl.innerHTML = '<option value="">' + selectLabels[type] + '</option>';
            currentEl.disabled = false;
        }
    }
    
    resolveBaseLocationFee();
}

function updateLocationFee(type, selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption && selectedOption.dataset.fee) {
        fees[type] = parseFloat(selectedOption.dataset.fee) || 0;
    } else {
        fees[type] = 0;
    }
    
    // Show delivery methods only if user is using dropdowns and has selected a province
    if (document.getElementById('deliveryLocationType').value === 'dropdowns') {
        const provSel = document.getElementById('sel_province');
        if (provSel && provSel.value !== '') {
            document.getElementById('deliveryMethodContainer').style.display = 'block';
        }
    }
    
    resolveBaseLocationFee();
}

function villageSelected(selectElement) {
    updateLocationFee('village', selectElement);
}
</script>

<?php require_once 'includes/footer.php'; ?>
