<?php
require_once 'includes/header.php';

// Fetch all pages
$pages_res = mysqli_query($conn, "SELECT * FROM pages");
$pages = [];
while ($row = mysqli_fetch_assoc($pages_res)) {
    $pages[$row['slug']] = $row;
}

// Define the 5 manageable content pages
$content_sections = [
    'about' => [
        'title' => 'About Us',
        'icon' => 'fas fa-info-circle',
        'url' => '../about.php',
        'default_title' => 'About Zora Shop',
        'default_subtitle' => 'Our Story & Vision',
        'description' => 'Manage the story, vision, core values, and introductory info for Zora Shop Rwanda.'
    ],
    'how_to_order' => [
        'title' => 'How to Order',
        'icon' => 'fas fa-shopping-bag',
        'url' => '../how_to_order.php',
        'default_title' => 'How to Order',
        'default_subtitle' => 'Simple & Easy Shopping Guide',
        'description' => 'Step-by-step instructions for customers to browse, cart, and checkout products.'
    ],
    'returns' => [
        'title' => 'Returns & Refunds',
        'icon' => 'fas fa-undo-alt',
        'url' => '../returns.php',
        'default_title' => 'Returns & Refunds Policy',
        'default_subtitle' => 'Customer Protection & Guarantees',
        'description' => 'Details on return eligibility, timelines, refund methods, and condition guidelines.'
    ],
    'privacy' => [
        'title' => 'Privacy Policy',
        'icon' => 'fas fa-user-shield',
        'url' => '../privacy.php',
        'default_title' => 'Privacy Policy',
        'default_subtitle' => 'Data Privacy & Security',
        'description' => 'Explain how customer data, phone numbers, and payment details are collected and protected.'
    ],
    'terms' => [
        'title' => 'Terms of Service',
        'icon' => 'fas fa-file-contract',
        'url' => '../terms.php',
        'default_title' => 'Terms of Service',
        'default_subtitle' => 'Rules & Agreement',
        'description' => 'Legal terms governing orders, deliveries, account usage, and store liability.'
    ],
    'order_notices' => [
        'title' => 'Auto-Cancel & Order Notice',
        'icon' => 'fas fa-language',
        'url' => '../order_track.php',
        'default_title' => 'Auto-Cancel & Order Policy Translations',
        'default_subtitle' => 'English & Kinyarwanda Content',
        'description' => 'Translate and customize the auto-cancel countdown title, policy notice message, and store order status for customers.'
    ]
];

$active_tab = isset($_GET['tab']) && isset($content_sections[$_GET['tab']]) ? $_GET['tab'] : (isset($_GET['page']) && isset($content_sections[$_GET['page']]) ? $_GET['page'] : 'about');
?>

<style>
.content-nav-pills .nav-link {
    border-radius: 12px;
    padding: 12px 18px;
    font-weight: 600;
    color: var(--text);
    background: var(--bg2);
    border: 1px solid var(--border);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.content-nav-pills .nav-link:hover {
    background: var(--bg3);
    border-color: var(--primary);
    transform: translateX(4px);
}
.content-nav-pills .nav-link.active {
    background: var(--primary) !important;
    color: #fff !important;
    border-color: var(--primary);
    box-shadow: 0 4px 15px rgba(1, 42, 94, 0.25);
}
.content-nav-pills .nav-link.active i {
    color: #fff !important;
}
.note-editor.note-frame {
    border-color: var(--border) !important;
    border-radius: 8px;
    overflow: hidden;
}
.note-toolbar {
    background: var(--bg3) !important;
    border-bottom: 1px solid var(--border) !important;
}
.note-editor .note-editable {
    background: var(--card) !important;
    color: var(--text) !important;
    min-height: 320px;
}
.note-statusbar {
    background: var(--bg3) !important;
    border-top: 1px solid var(--border) !important;
}
.banner-preview-box {
    width: 100%;
    max-height: 180px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid var(--border);
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="admin-page-title mb-1"><i class="fas fa-file-alt me-2 text-accent"></i> Content Management</h2>
        <p class="text-muted small mb-0">Manage and update custom text, banners, and policies for your customer-facing pages.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Navigation Tabs -->
    <div class="col-lg-3">
        <div class="admin-card p-3">
            <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 1px;">Website Pages</h6>
            <div class="nav flex-column content-nav-pills" id="contentTabs" role="tablist" aria-orientation="vertical">
                <?php foreach ($content_sections as $sKey => $sInfo): ?>
                <a class="nav-link <?= $active_tab === $sKey ? 'active' : '' ?>" id="tab-btn-<?= $sKey ?>" data-bs-toggle="pill" href="#tab-pane-<?= $sKey ?>" role="tab" aria-controls="tab-pane-<?= $sKey ?>" aria-selected="<?= $active_tab === $sKey ? 'true' : 'false' ?>">
                    <i class="<?= $sInfo['icon'] ?> text-accent"></i>
                    <span><?= htmlspecialchars($sInfo['title']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            
            <hr style="border-color: var(--border);" class="my-3">
            <div class="p-2 rounded" style="background: var(--bg1); font-size: 0.8rem; color: var(--text3);">
                <i class="fas fa-lightbulb text-warning me-1"></i>
                <strong>Tip:</strong> You can format text with headings, bullet lists, bold styling, and links. Leave content empty to use the system default layout.
            </div>
        </div>
    </div>

    <!-- Right Column: Content Editor Panes -->
    <div class="col-lg-9">
        <div class="tab-content" id="contentTabContent">
            <?php foreach ($content_sections as $sKey => $sInfo): 
                $pData = $pages[$sKey] ?? [];
                $current_title = !empty($pData['title']) ? $pData['title'] : $sInfo['default_title'];
                $current_subtitle = !empty($pData['subtitle']) ? $pData['subtitle'] : $sInfo['default_subtitle'];
                $current_content = $pData['content'] ?? '';
                $current_banner = $pData['banner_image'] ?? '';
            ?>
            <?php if ($sKey === 'order_notices'): ?>
            <div class="tab-pane fade <?= $active_tab === $sKey ? 'show active' : '' ?>" id="tab-pane-<?= $sKey ?>" role="tabpanel" aria-labelledby="tab-btn-<?= $sKey ?>">
                <div class="admin-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3" style="border-color: var(--border) !important;">
                        <div>
                            <h4 class="mb-1 fw-bold" style="color: var(--primary);">
                                <i class="<?= $sInfo['icon'] ?> me-2 text-accent"></i> <?= htmlspecialchars($sInfo['title']) ?>
                            </h4>
                            <small class="text-muted"><?= htmlspecialchars($sInfo['description']) ?></small>
                        </div>
                        <a href="<?= $sInfo['url'] ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-external-link-alt me-1"></i> View Order Tracking
                        </a>
                    </div>

                    <form action="actions.php" method="POST" class="page-content-form">
                        <input type="hidden" name="action" value="update_order_settings">
                        <input type="hidden" name="redirect" value="content.php?tab=order_notices">

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Auto-Cancel Order Period (Days)</label>
                                <input type="number" name="settings[global_delivery_days]" class="form-control" value="<?= get_setting('global_delivery_days', '0') ?>" min="0" placeholder="0 = Disable auto-cancel">
                                <small class="text-muted d-block mt-1">Number of days before active customer orders are automatically cancelled.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Store Order Status</label>
                                <select name="settings[store_order_status]" class="form-select">
                                    <option value="enable" <?= get_setting('store_order_status') == 'enable' ? 'selected' : '' ?>>Enable (Normal Ordering)</option>
                                    <option value="disable" <?= get_setting('store_order_status') == 'disable' ? 'selected' : '' ?>>Disable Ordering</option>
                                    <option value="schedule" <?= get_setting('store_order_status') == 'schedule' ? 'selected' : '' ?>>Auto Follow Schedule</option>
                                </select>
                                <small class="text-muted d-block mt-1">Global store checkout availability.</small>
                            </div>
                        </div>

                        <!-- Bilingual Content Boxes -->
                        <div class="row g-4 mb-4">
                            <!-- English Column -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border h-100" style="background: var(--bg2); border-color: var(--border) !important;">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border) !important;">
                                        <i class="fas fa-globe text-primary fs-5"></i>
                                        <strong style="color: var(--text);">English Content (EN)</strong>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Auto-Cancel Title / Label</label>
                                        <input type="text" name="settings[auto_cancel_title_en]" class="form-control" value="<?= get_setting('auto_cancel_title_en', 'Auto-Cancel Deadline') ?>" placeholder="Auto-Cancel Deadline">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Auto-Cancel Notice / Policy Message</label>
                                        <textarea name="settings[auto_cancel_message_en]" class="form-control" rows="3" placeholder="Orders not delivered before this deadline will be automatically cancelled."><?= get_setting('auto_cancel_message_en', 'Orders not delivered before this deadline will be automatically cancelled.') ?></textarea>
                                        <small class="text-muted">Shown under the deadline on customer order tracking.</small>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Store Closed / Disabled Message</label>
                                        <input type="text" name="settings[store_order_message]" class="form-control" value="<?= get_setting('store_order_message', 'Ordering is temporarily disabled.') ?>" placeholder="Ordering is temporarily disabled.">
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
                                        <label class="form-label small fw-bold text-muted mb-1">Umutwe w'Igihe Ntarengwa (Kinyarwanda Title)</label>
                                        <input type="text" name="settings[auto_cancel_title_rw]" class="form-control" value="<?= get_setting('auto_cancel_title_rw', 'Igihe Ntarengwa cyo Guhagarika Komande') ?>" placeholder="Igihe Ntarengwa cyo Guhagarika Komande">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Ubutumwa n'Ibisobanuro (Kinyarwanda Notice)</label>
                                        <textarea name="settings[auto_cancel_message_rw]" class="form-control" rows="3" placeholder="Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora."><?= get_setting('auto_cancel_message_rw', "Komande idatanzwe mbere y'iki gihe ntarengwa ihagarikwa mu buryo bwikora.") ?></textarea>
                                        <small class="text-muted">Bigaragara munsi y'igihe ntarengwa ku rupapuro rwo kureba komande.</small>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Ubutumwa bwo guhagarika gutumiza (Kinyarwanda Message)</label>
                                        <input type="text" name="settings[store_order_message_rw]" class="form-control" value="<?= get_setting('store_order_message_rw', 'Gutumiza ibicuruzwa byahagaze by\'agateganyo.') ?>" placeholder="Gutumiza ibicuruzwa byahagaze by'agateganyo.">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border) !important;">
                            <span class="text-muted small">Changes will immediately update active order countdowns and customer displays.</span>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill">
                                <i class="fas fa-save me-1"></i> Save Translation & Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="tab-pane fade <?= $active_tab === $sKey ? 'show active' : '' ?>" id="tab-pane-<?= $sKey ?>" role="tabpanel" aria-labelledby="tab-btn-<?= $sKey ?>">
                <div class="admin-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3" style="border-color: var(--border) !important;">
                        <div>
                            <h4 class="mb-1 fw-bold" style="color: var(--primary);">
                                <i class="<?= $sInfo['icon'] ?> me-2 text-accent"></i> <?= htmlspecialchars($sInfo['title']) ?>
                            </h4>
                            <small class="text-muted"><?= htmlspecialchars($sInfo['description']) ?></small>
                        </div>
                        <a href="<?= $sInfo['url'] ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fas fa-external-link-alt me-1"></i> View Live Page
                        </a>
                    </div>

                    <form action="actions.php" method="POST" enctype="multipart/form-data" class="page-content-form">
                        <input type="hidden" name="action" value="save_page_content">
                        <input type="hidden" name="slug" value="<?= htmlspecialchars($sKey) ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Page Header Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($current_title) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Subtitle / Eyebrow</label>
                                <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($current_subtitle) ?>" placeholder="e.g. Simple & Easy Shopping Guide">
                            </div>
                        </div>

                        <div class="mb-4 p-3 rounded" style="background: var(--bg1); border: 1px solid var(--border);">
                            <label class="form-label fw-bold d-block mb-2">Hero Banner Image <span class="text-muted fw-normal small">(Optional - replaces default background header)</span></label>
                            
                            <?php if (!empty($current_banner) && file_exists("../uploads/" . $current_banner)): ?>
                                <div class="mb-3 position-relative d-inline-block">
                                    <img src="../uploads/<?= htmlspecialchars($current_banner) ?>" alt="Banner" class="banner-preview-box">
                                    <div class="mt-2 form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_banner" value="1" id="remove_banner_<?= $sKey ?>">
                                        <label class="form-check-label text-danger small fw-bold" for="remove_banner_<?= $sKey ?>">
                                            <i class="fas fa-trash-alt me-1"></i> Remove this banner image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Page Body Content</label>
                            <textarea name="content" class="summernote-page" id="editor_<?= $sKey ?>"><?= htmlspecialchars($current_content) ?></textarea>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i> Tip: Use the editor toolbar to add paragraphs, bullet points, headers, or embed links.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border) !important;">
                            <span class="text-muted small">All changes take effect immediately on the store.</span>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Summernote on all page content textareas
    if (typeof $.fn.summernote !== 'undefined') {
        $('.summernote-page').summernote({
            height: 380,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    // Handle form submit button loading states
    document.querySelectorAll('.page-content-form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving Content...';
            }
        });
    });

    // Keep tab in URL hash or parameter
    const pills = document.querySelectorAll('#contentTabs a[data-bs-toggle="pill"]');
    pills.forEach(pill => {
        pill.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('href').replace('#tab-pane-', '');
            if (history.pushState) {
                history.pushState(null, null, '?tab=' + targetId);
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
