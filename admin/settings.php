<?php
require_once 'includes/header.php';
<<<<<<< HEAD

$query = mysqli_query($conn, "SELECT * FROM settings");
$settings = [];
while($row = mysqli_fetch_assoc($query)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        global $settings;
        return htmlspecialchars($settings[$key] ?? $default);
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-cog me-2 text-accent"></i> Global Settings</h2>
</div>

<div class="admin-card p-4">
    <form action="actions.php" method="POST">
        <input type="hidden" name="action" value="update_settings">
        
        <h5 class="mb-4 text-accent border-bottom pb-2" style="border-color: var(--border) !important;">Store Information</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label class="form-label">Store Name</label>
                <input type="text" name="settings[store_name]" class="form-control" value="<?= get_setting('store_name', 'ZORA') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Email</label>
                <input type="email" name="settings[contact_email]" class="form-control" value="<?= get_setting('contact_email', 'support@zora.rw') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Support Phone</label>
                <input type="text" name="settings[support_phone]" class="form-control" value="<?= get_setting('support_phone', '+250 780 000 000') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Store Address</label>
                <textarea name="settings[store_address]" class="form-control" rows="3"><?= get_setting('store_address', "KN 4 Ave, Kigali, Rwanda\nKigali City Tower, 5th Floor") ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Working Hours</label>
                <input type="text" name="settings[support_working_hours]" class="form-control" value="<?= get_setting('support_working_hours', 'Mon-Sat: 8 AM - 8 PM') ?>">
            </div>
        </div>
        
        <h5 class="mb-4 text-accent border-bottom pb-2" style="border-color: var(--border) !important;">
            <i class="fas fa-shield-alt me-2"></i> Product Page Trust & Delivery Badges
        </h5>
        <p class="text-muted small mb-4">
            Customize the 4 trust and delivery perk texts displayed on product detail pages. The icons (Delivery, Shield, Return, Payment) are fixed, but you can change the text for English and Kinyarwanda below.
        </p>

        <div class="row g-4 mb-5">
            <!-- Perk 1: Fast Delivery -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <strong style="color: var(--text);">Badge 1 (Fast Delivery)</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">English Text</label>
                        <input type="text" name="settings[perk_delivery_en]" class="form-control form-control-sm" value="<?= get_setting('perk_delivery_en', '2–4h Fast Delivery (Kigali)') ?>" placeholder="2–4h Fast Delivery (Kigali)">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Kinyarwanda Text</label>
                        <input type="text" name="settings[perk_delivery_rw]" class="form-control form-control-sm" value="<?= get_setting('perk_delivery_rw', 'Kugezwaho mu masaha 2–4 (Kigali)') ?>" placeholder="Kugezwaho mu masaha 2–4 (Kigali)">
                    </div>
                </div>
            </div>

            <!-- Perk 2: Quality Guarantee -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <strong style="color: var(--text);">Badge 2 (Quality & Guarantee)</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">English Text</label>
                        <input type="text" name="settings[perk_quality_en]" class="form-control form-control-sm" value="<?= get_setting('perk_quality_en', '100% Quality Guaranteed') ?>" placeholder="100% Quality Guaranteed">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Kinyarwanda Text</label>
                        <input type="text" name="settings[perk_quality_rw]" class="form-control form-control-sm" value="<?= get_setting('perk_quality_rw', 'Ibicuruzwa Byizewe 100%') ?>" placeholder="Ibicuruzwa Byizewe 100%">
                    </div>
                </div>
            </div>

            <!-- Perk 3: Return Policy -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <strong style="color: var(--text);">Badge 3 (Return Policy)</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">English Text</label>
                        <input type="text" name="settings[perk_return_en]" class="form-control form-control-sm" value="<?= get_setting('perk_return_en', '7-Day Return Policy') ?>" placeholder="7-Day Return Policy">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Kinyarwanda Text</label>
                        <input type="text" name="settings[perk_return_rw]" class="form-control form-control-sm" value="<?= get_setting('perk_return_rw', 'Gusubiza mu minsi 7') ?>" placeholder="Gusubiza mu minsi 7">
                    </div>
                </div>
            </div>

            <!-- Perk 4: Payment Methods -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <strong style="color: var(--text);">Badge 4 (Payment Methods)</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">English Text</label>
                        <input type="text" name="settings[perk_payment_en]" class="form-control form-control-sm" value="<?= get_setting('perk_payment_en', 'MoMo, Card & Pay on Delivery') ?>" placeholder="MoMo, Card & Pay on Delivery">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Kinyarwanda Text</label>
                        <input type="text" name="settings[perk_payment_rw]" class="form-control form-control-sm" value="<?= get_setting('perk_payment_rw', 'MoMo, Karita & Kwishyura uhawe ibintu') ?>" placeholder="MoMo, Karita & Kwishyura uhawe ibintu">
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-4 text-accent border-bottom pb-2" style="border-color: var(--border) !important;">
            <i class="fas fa-language me-2"></i> Auto-Cancel Order & Store Notice Bilingual Content
        </h5>
        <p class="text-muted small mb-4">
            Configure the Auto-Cancel period and customize all customer-facing notice and deadline messages for both English and Kinyarwanda.
        </p>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Auto-Cancel Order (Days)</label>
                <input type="number" name="settings[global_delivery_days]" class="form-control" value="<?= get_setting('global_delivery_days', '0') ?>" min="0" placeholder="0 = Disable auto-cancel">
                <small class="text-muted">Days before unpaid/undelivered active orders are automatically cancelled and restocked.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Store Order Status</label>
                <select name="settings[store_order_status]" class="form-select">
                    <option value="enable" <?= get_setting('store_order_status') == 'enable' ? 'selected' : '' ?>>Enable (Normal Ordering)</option>
                    <option value="disable" <?= get_setting('store_order_status') == 'disable' ? 'selected' : '' ?>>Disable Ordering</option>
                    <option value="schedule" <?= get_setting('store_order_status') == 'schedule' ? 'selected' : '' ?>>Auto Follow Schedule</option>
                </select>
                <small class="text-muted">Global store checkout availability status.</small>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- English Column -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border h-100" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border) !important;">
                        <i class="fas fa-globe text-primary fs-5"></i>
                        <strong style="color: var(--text);">English Content (EN)</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Auto-Cancel Title</label>
                        <input type="text" name="settings[auto_cancel_title_en]" class="form-control form-control-sm" value="<?= get_setting('auto_cancel_title_en', 'Auto-Cancel Deadline') ?>" placeholder="Auto-Cancel Deadline">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Auto-Cancel Notice Message</label>
                        <textarea name="settings[auto_cancel_message_en]" class="form-control form-control-sm" rows="3" placeholder="Orders not delivered before this deadline will be automatically cancelled."><?= get_setting('auto_cancel_message_en', 'Orders not delivered before this deadline will be automatically cancelled.') ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Store Closed Message (EN)</label>
                        <input type="text" name="settings[store_order_message]" class="form-control form-control-sm" value="<?= get_setting('store_order_message', 'Ordering is temporarily disabled.') ?>" placeholder="Ordering is temporarily disabled.">
                    </div>
                </div>
            </div>

            <!-- Kinyarwanda Column -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border h-100" style="background: var(--bg2); border-color: var(--border) !important;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border) !important;">
                        <i class="fas fa-globe-africa text-success fs-5"></i>
                        <strong style="color: var(--text);">Kinyarwanda Content (RW)</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Auto-Cancel Title (Kinyarwanda)</label>
                        <input type="text" name="settings[auto_cancel_title_rw]" class="form-control form-control-sm" value="<?= get_setting('auto_cancel_title_rw', 'Igihe Ntarengwa cyo Guhagarika Komande') ?>" placeholder="Igihe Ntarengwa cyo Guhagarika Komande">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Auto-Cancel Notice Message (Kinyarwanda)</label>
                        <textarea name="settings[auto_cancel_message_rw]" class="form-control form-control-sm" rows="3" placeholder="Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora."><?= get_setting('auto_cancel_message_rw', "Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora.") ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Store Closed Message (Kinyarwanda)</label>
                        <input type="text" name="settings[store_order_message_rw]" class="form-control form-control-sm" value="<?= get_setting('store_order_message_rw', 'Gutumiza ibicuruzwa byahagaze by\'agateganyo.') ?>" placeholder="Gutumiza ibicuruzwa byahagaze by'agateganyo.">
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-4 text-accent border-bottom pb-2" style="border-color: var(--border) !important;">Social Media Links</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <label class="form-label"><i class="fab fa-instagram me-1"></i> Instagram URL</label>
                <input type="url" name="settings[social_instagram]" class="form-control" value="<?= get_setting('social_instagram', 'https://www.instagram.com/zora_shop_rwanda?igsh=amphbDMwNW1vNmdw') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="fab fa-tiktok me-1"></i> TikTok URL</label>
                <input type="url" name="settings[social_tiktok]" class="form-control" value="<?= get_setting('social_tiktok', 'https://www.tiktok.com/@zora_shoprwanda?_r=1&_t=ZS-96MWD6meeLV') ?>">
            </div>
        </div>
        
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-hero px-5"><i class="fas fa-save me-2"></i> Save Settings</button>
        </div>
    </form>
=======
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title">Security & Settings</h2>
</div>

<div class="admin-card p-5 text-center mt-4">
    <i class="fas fa-tools fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
    <h3 class="mt-3">Under Construction</h3>
    <p class="text-muted">The Security & Settings module is currently being built. Check back soon!</p>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
</div>
<?php require_once 'includes/footer.php'; ?>
