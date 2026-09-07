<?php
require_once 'includes/header.php';
<<<<<<< HEAD
date_default_timezone_set('Africa/Kigali');

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
$categories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM categories"), MYSQLI_ASSOC);
=======

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_query = mysqli_query($conn, "SELECT name, image FROM products WHERE id = $product_id");
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444

if (mysqli_num_rows($product_query) == 0) {
    echo "<h2>Product not found.</h2>";
    require_once 'includes/footer.php';
    exit;
}

$product = mysqli_fetch_assoc($product_query);

$images_query = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY created_at DESC");
<<<<<<< HEAD

// Fetch existing inventory for this product
$inv_query = mysqli_query($conn, "SELECT color_name, size_name, stock, price FROM product_inventory WHERE product_id = $product_id");
$inventory_data = [];
$color_prices = [];
if ($inv_query) {
    while($row = mysqli_fetch_assoc($inv_query)) {
        $inventory_data[$row['color_name']][] = [
            'size' => $row['size_name'],
            'stock' => $row['stock'],
            'price' => $row['price']
        ];
        if (!empty($row['price']) && (float)$row['price'] > 0 && !isset($color_prices[$row['color_name']])) {
            $color_prices[$row['color_name']] = (float)$row['price'];
        }
    }
}

$has_variants = !empty($inventory_data);
$total_variant_stock = 0;
foreach ($inventory_data as $cName => $sizes) {
    foreach ($sizes as $s) {
        $total_variant_stock += $s['stock'];
    }
}
?>
<!-- Include Tagify -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<style>
/* Custom Tagify Styling */
.tagify {
    --tags-border-color: var(--border);
    --tags-hover-border-color: var(--accent);
    --tags-focus-border-color: var(--accent);
    --tag-bg: var(--bg3);
    --tag-hover: var(--bg1);
    --tag-text-color: var(--text);
    --tag-text-color--edit: var(--text);
    --tag-pad: 0.3rem 0.5rem;
    --tag-inset-shadow-size: 1.1em;
    --tag-invalid-color: #ff3e1d;
    --tag-invalid-bg: rgba(255, 62, 29, 0.5);
    background: var(--bg2);
    border-radius: 6px;
    padding: 0;
}
.tagify__input {
    color: var(--text);
}
.tagify__tag > div::before {
    box-shadow: 0 0 0 var(--tag-inset-shadow-size) var(--tag-bg) inset;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="admin-page-title"><i class="fas fa-edit me-2 text-accent"></i> Update Product: <?= htmlspecialchars($product['name']) ?></h2>
    <a href="products.php" class="btn btn-secondary">Back to Products</a>
</div>

<div class="admin-card p-4 mb-4">
    <h5 class="mb-3">Base Details</h5>
    <form action="actions.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_product">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Discount Price</label>
                <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= $product['discount_price'] ? htmlspecialchars($product['discount_price']) : '' ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Discount Expiry</label>
                <input type="datetime-local" name="discount_expiry" class="form-control" value="<?= isset($product['discount_expiry']) && $product['discount_expiry'] ? date('Y-m-d\TH:i', strtotime($product['discount_expiry'])) : '' ?>" min="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Base Stock <?= $has_variants ? '<small class="text-muted" style="font-size:0.7rem">(Auto-calc)</small>' : '' ?></label>
                <input type="number" name="stock" class="form-control" value="<?= $has_variants ? $total_variant_stock : htmlspecialchars($product['stock']) ?>" <?= $has_variants ? 'readonly' : 'required' ?>>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Update Base Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label">Search Tags</label>
                <?php
                $tags_val = '';
                if (!empty($product['tags'])) {
                    $decoded = str_replace('&quot;', '"', $product['tags']);
                    $parsed = json_decode($decoded, true);
                    if (is_array($parsed)) {
                        $tags_val = implode(', ', array_column($parsed, 'value'));
                    } else {
                        $tags_val = $product['tags'];
                    }
                }
                ?>
                <input name="tags" class="form-control" id="edit_tags" value="<?= htmlspecialchars($tags_val) ?>" placeholder="e.g. smartphone, 5G, electronics">
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" id="product_description" class="form-control" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product Details</button>
            </div>
        </div>
    </form>
</div>

<!-- Product Gallery Images Section -->
<div class="admin-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1"><i class="fas fa-images me-2 text-primary"></i> Product Gallery Images</h5>
            <small class="text-muted">General photos shown in the product gallery carousel for all shoppers.</small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('addGalleryFormContainer').style.display = document.getElementById('addGalleryFormContainer').style.display === 'none' ? 'block' : 'none';">
            <i class="fas fa-plus"></i> Add Gallery Images
        </button>
    </div>

    <!-- Upload Gallery Images Form (Collapsible) -->
    <div id="addGalleryFormContainer" style="display: none;" class="p-3 mb-3 rounded" style="background: var(--bg1); border: 1px solid var(--border);">
        <form action="actions.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_gallery_image">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <div class="row g-2 align-items-center">
                <div class="col-md-9">
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload me-1"></i> Upload</button>
                </div>
            </div>
        </form>
    </div>

    <?php 
    $gallery_images = [];
    $colors_map = [];
    if(mysqli_num_rows($images_query) > 0) {
        // Reset query pointer
        mysqli_data_seek($images_query, 0);
        while($img = mysqli_fetch_assoc($images_query)) {
            $cName = trim($img['color_name'] ?? '');
            if ($cName === '' || in_array(strtolower($cName), ['default', 'image', 'gallery', 'photo', 'none', 'null', 'default image'])) {
                if (!empty($img['image_path'])) {
                    $gallery_images[] = $img;
                }
            } else {
                if (!empty($img['image_path'])) {
                    $colors_map[$cName]['images'][] = $img;
                }
                if (!empty($img['price']) && (float)$img['price'] > 0 && !isset($color_prices[$cName])) {
                    $color_prices[$cName] = (float)$img['price'];
                }
            }
        }
    }
    
    // Ensure all existing color variations from inventory are represented even if they have no photos
    foreach ($inventory_data as $cName => $cSizes) {
        if (!isset($colors_map[$cName])) {
            $colors_map[$cName] = ['images' => []];
        }
    }
    ?>

    <?php if(!empty($gallery_images)): ?>
        <div class="d-flex flex-wrap gap-3 mt-2">
            <?php foreach($gallery_images as $gImg): ?>
            <div class="position-relative" style="width: 110px; height: 110px; border-radius: 8px; border: 2px solid var(--border); background: var(--bg3);">
                <img src="../uploads/<?= htmlspecialchars($gImg['image_path']) ?>" class="w-100 h-100" style="object-fit:cover; border-radius: 6px;">
                <form action="actions.php" method="POST" onsubmit="return customConfirm(event, 'Delete this gallery image?')" class="position-absolute top-0 end-0">
                    <input type="hidden" name="action" value="delete_product_image">
                    <input type="hidden" name="image_id" value="<?= $gImg['id'] ?>">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                    <button type="submit" class="btn btn-danger btn-sm p-0 shadow" style="width: 24px; height: 24px; border-radius: 50%; transform: translate(40%, -40%);"><i class="fas fa-times" style="font-size: 0.7rem;"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted small mb-0">No general gallery images uploaded. (Main product photo is set in Base Details above).</p>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Add New Color Variation -->
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h5 class="mb-3"><i class="fas fa-palette me-2 text-accent"></i> Add Color Variation</h5>
            <form action="actions.php" method="POST" enctype="multipart/form-data" id="addColorVariationForm">
=======
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Colors for: <?= htmlspecialchars($product['name']) ?></h2>
    <a href="products.php" class="btn btn-secondary">Back to Products</a>
</div>

<div class="row g-4">
    <!-- Add New Color/Image -->
    <div class="col-md-4">
        <div class="admin-card p-4">
            <h5 class="mb-3">Add Color Variation</h5>
            <form action="actions.php" method="POST" enctype="multipart/form-data">
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                <input type="hidden" name="action" value="add_product_color">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                
                <div class="mb-3">
<<<<<<< HEAD
                    <label class="form-label fw-bold small text-uppercase mb-1" style="letter-spacing: 0.5px; color: var(--primary);">
                        <i class="fas fa-palette me-1 text-accent"></i> Color Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="color_name" id="new_color_name" class="form-control fw-medium" placeholder="e.g. Red, Midnight Blue, Space Gray" required>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold small text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--primary);">
                            <i class="fas fa-tag me-1 text-accent"></i> Variant Price
                        </label>
                        <span class="badge bg-secondary-subtle text-muted border px-2 py-1" style="font-size: 0.68rem; font-weight: 600;">Optional</span>
                    </div>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="variation_price" class="form-control fw-semibold" placeholder="Inherits base price (<?= number_format($product['price'], 0) ?> RFW)">
                        <span class="input-group-text bg-light fw-bold text-muted small">RFW</span>
                    </div>
                </div>
                
                <div class="mb-3 p-3 rounded" style="background: var(--bg1); border: 1px solid var(--border);">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="form-label mb-0 fw-bold">Sizes & Inventory</label>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-size"><i class="fas fa-plus"></i> Add Size</button>
                    </div>
                    <div class="size-rows" id="colorSizeRows">
                        <div class="row g-2 align-items-center mb-2 size-row">
                            <div class="col-5">
                                <input type="text" name="size_names[]" class="form-control form-control-sm" placeholder="Size (e.g. XL)">
                            </div>
                            <div class="col-5">
                                <input type="number" name="size_stocks[]" class="form-control form-control-sm" placeholder="Stock" required min="0">
                            </div>
                            <div class="col-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-size" disabled><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Variant Images <span class="text-muted">(Optional - Multiple)</span></label>
                    <div class="variant-drop-zone position-relative p-3 text-center rounded" id="colorDropZone" style="border: 2px dashed var(--border); background: var(--bg1); cursor: pointer; min-height: 120px; transition: all 0.3s ease;">
                        <div class="variant-drop-prompt" id="colorDropPrompt">
                            <i class="fas fa-images fs-3 text-muted mb-2"></i>
                            <p class="mb-0 text-muted small">Click to browse or drag and drop images (Optional)</p>
                        </div>
                        <div class="variant-preview-container d-flex gap-2 flex-wrap mt-2 justify-content-center" id="colorPreviewContainer" style="display: none !important;"></div>
                        <input type="file" name="images[]" id="colorFileInput" class="variant-file-input" accept="image/*" multiple style="display: none;">
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus-circle me-1"></i> Save Variation</button>
=======
                    <label class="form-label">Color Name</label>
                    <input type="text" name="color_name" class="form-control" placeholder="e.g. Red, Midnight Blue" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Upload Image</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            </form>
        </div>
    </div>
    
<<<<<<< HEAD
    <!-- Existing Color Variations -->
    <div class="col-md-8">
        <div class="admin-card p-4">
            <h5 class="mb-3"><i class="fas fa-layer-group me-2 text-accent"></i> Existing Color Variations</h5>
            <?php if(!empty($colors_map)): ?>
            <div class="row g-3">
                <?php foreach($colors_map as $cName => $cData): ?>
                <div class="col-12">
                    <div class="card p-3 position-relative" style="background: var(--bg2); border: 1px solid var(--border) !important; border-radius: 10px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <h5 class="mb-0 fw-bold" style="color: var(--primary);"><i class="fas fa-tag me-1 text-accent"></i> <?= htmlspecialchars($cName) ?></h5>
                                <?php if(isset($color_prices[$cName]) && $color_prices[$cName] > 0): ?>
                                    <span class="badge bg-primary" style="font-size: 0.8rem;"><i class="fas fa-dollar-sign me-1"></i> <?= number_format($color_prices[$cName], 0) ?> RFW</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateStockModal_<?= md5($cName) ?>"><i class="fas fa-edit me-1"></i> Update Stock</button>
                                <form action="actions.php" method="POST" onsubmit="return customConfirm(event, 'Are you sure you want to delete this variation and its images?')">
                                    <input type="hidden" name="action" value="delete_product_variation">
                                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                    <input type="hidden" name="color_name" value="<?= htmlspecialchars($cName) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                        
                        <?php 
                        $inv = $inventory_data[$cName] ?? [];
                        if (!empty($inv)): 
                        ?>
                        <div class="mb-3">
                            <strong class="text-muted small text-uppercase">Sizes & Stock:</strong><br>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach($inv as $s): ?>
                                <div class="border rounded px-2 py-1 small" style="background: var(--bg3); border-color: var(--border) !important;">
                                    <strong><?= htmlspecialchars($s['size'] ?: 'Standard') ?>:</strong> 
                                    <span class="badge bg-success ms-1"><?= (int)$s['stock'] ?> in stock</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        $valid_variant_images = array_filter($cData['images'] ?? [], function($img) {
                            return !empty($img['image_path']);
                        });
                        if (!empty($valid_variant_images)): 
                        ?>
                        <hr class="my-2" style="border-color: var(--border);">
                        <strong class="text-muted small text-uppercase mb-2 d-block">Variant Photos:</strong>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach($valid_variant_images as $img): ?>
                            <div class="position-relative" style="width: 90px; height: 90px; border-radius: 8px; border: 2px solid var(--border); background: var(--bg3);">
                                <img src="../uploads/<?= htmlspecialchars($img['image_path']) ?>" class="w-100 h-100" style="object-fit:cover; border-radius: 6px;">
                                <form action="actions.php" method="POST" onsubmit="return customConfirm(event, 'Delete this image?')" class="position-absolute top-0 end-0">
                                    <input type="hidden" name="action" value="delete_product_image">
                                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                    <button type="submit" class="btn btn-danger btn-sm p-0 shadow" style="width: 22px; height: 22px; border-radius: 50%; transform: translate(30%, -30%);"><i class="fas fa-times" style="font-size: 0.65rem;"></i></button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Update Stock Modal for <?= htmlspecialchars($cName) ?> -->
                <div class="modal fade" id="updateStockModal_<?= md5($cName) ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color: var(--card); color: var(--text);">
                            <form action="actions.php" method="POST">
                                <input type="hidden" name="action" value="update_color_inventory">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <input type="hidden" name="color_name" value="<?= htmlspecialchars($cName) ?>">
                                <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                                    <h5 class="modal-title fw-bold">Update Stock: <?= htmlspecialchars($cName) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3 p-3 rounded" style="background: var(--bg1); border: 1px solid var(--border);">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--primary);">
                                                <i class="fas fa-tag me-1 text-accent"></i> Custom Variation Price
                                            </label>
                                            <span class="badge bg-secondary-subtle text-muted border px-2 py-1" style="font-size: 0.68rem; font-weight: 600;">Optional</span>
                                        </div>
                                        <div class="input-group mt-2">
                                            <input type="number" step="0.01" min="0" name="variation_price" class="form-control fw-semibold" value="<?= isset($color_prices[$cName]) && $color_prices[$cName] > 0 ? htmlspecialchars($color_prices[$cName]) : '' ?>" placeholder="Base price (<?= number_format($product['price'], 0) ?> RFW)">
                                            <span class="input-group-text bg-light fw-bold text-muted small">RFW</span>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave empty if this color sells for the default product price.</small>
                                    </div>
                                    <label class="form-label fw-semibold mb-2">Sizes & Stock Levels</label>
                                    <div class="size-rows-<?= md5($cName) ?>">
                                        <?php if (!empty($inv)): foreach($inv as $s): ?>
                                        <div class="row g-2 align-items-center mb-2 size-row">
                                            <div class="col-5">
                                                <input type="text" name="size_names[]" class="form-control form-control-sm" value="<?= htmlspecialchars($s['size']) ?>" placeholder="Size (e.g. M, L, XL)">
                                            </div>
                                            <div class="col-5">
                                                <input type="number" name="size_stocks[]" class="form-control form-control-sm" value="<?= (int)$s['stock'] ?>" placeholder="Stock" required min="0">
                                            </div>
                                            <div class="col-2 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.size-row').remove()"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <div class="row g-2 align-items-center mb-2 size-row">
                                            <div class="col-5">
                                                <input type="text" name="size_names[]" class="form-control form-control-sm" placeholder="Size (e.g. Standard)">
                                            </div>
                                            <div class="col-5">
                                                <input type="number" name="size_stocks[]" class="form-control form-control-sm" placeholder="Stock" required min="0">
                                            </div>
                                            <div class="col-2 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.size-row').remove()"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addSizeRow('size-rows-<?= md5($cName) ?>')"><i class="fas fa-plus me-1"></i> Add Another Size</button>
                                </div>
                                <div class="modal-footer border-top" style="border-color: var(--border) !important;">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-save me-1"></i> Save Changes</button>
                                </div>
=======
    <!-- Existing Colors -->
    <div class="col-md-8">
        <div class="admin-card p-4">
            <h5 class="mb-3">Existing Colors</h5>
            <?php if(mysqli_num_rows($images_query) > 0): ?>
            <div class="row g-3">
                <?php while($img = mysqli_fetch_assoc($images_query)): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card bg-dark text-white border-0 position-relative">
                        <img src="../uploads/<?= htmlspecialchars($img['image_path']) ?>" class="card-img" style="height:200px; object-fit:cover; opacity: 0.8;">
                        <div class="card-img-overlay d-flex flex-column justify-content-between">
                            <h5 class="card-title text-center text-white p-1" style="background: rgba(0,0,0,0.6); border-radius: 4px;">
                                <?= htmlspecialchars($img['color_name']) ?>
                            </h5>
                            <form action="actions.php" method="POST" onsubmit="return confirm('Delete this color image?')">
                                <input type="hidden" name="action" value="delete_product_color">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-trash"></i> Delete</button>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
                            </form>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p class="text-muted mb-0">No color variations created yet. Use the form on the left to add a color variation.</p>
=======
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <p class="text-muted">No additional color images added yet.</p>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
            <?php endif; ?>
        </div>
    </div>
</div>

<<<<<<< HEAD
<script>
    // Setup Drag & Drop and Previews for Variant Images
    const dropZoneEl = document.getElementById('colorDropZone');
    const fileInp = document.getElementById('colorFileInput');
    const promptEl = document.getElementById('colorDropPrompt');
    const previewContainerEl = document.getElementById('colorPreviewContainer');

    dropZoneEl.addEventListener('click', (e) => {
        if (e.target.closest('.variant-preview-item')) return;
        fileInp.click();
    });

    dropZoneEl.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZoneEl.style.borderColor = 'var(--accent)';
        dropZoneEl.style.backgroundColor = 'rgba(251, 124, 0, 0.1)';
    });

    dropZoneEl.addEventListener('dragleave', () => {
        dropZoneEl.style.borderColor = 'var(--border)';
        dropZoneEl.style.backgroundColor = 'var(--bg1)';
    });

    dropZoneEl.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZoneEl.style.borderColor = 'var(--border)';
        dropZoneEl.style.backgroundColor = 'var(--bg1)';
        
        if (e.dataTransfer.files.length) {
            const dt = new DataTransfer();
            for(let i=0; i<fileInp.files.length; i++) {
                dt.items.add(fileInp.files[i]);
            }
            for(let i=0; i<e.dataTransfer.files.length; i++) {
                if(e.dataTransfer.files[i].type.startsWith('image/')) {
                    dt.items.add(e.dataTransfer.files[i]);
                }
            }
            fileInp.files = dt.files;
            updateVariantPreviews(fileInp, previewContainerEl, promptEl);
        }
    });

    fileInp.addEventListener('change', () => {
        updateVariantPreviews(fileInp, previewContainerEl, promptEl);
    });

    function updateVariantPreviews(fileInput, container, prompt) {
        container.innerHTML = '';
        if (fileInput.files.length > 0) {
            prompt.style.display = 'none';
            container.style.setProperty('display', 'flex', 'important');
            fileInput.removeAttribute('required');
            
            Array.from(fileInput.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const item = document.createElement('div');
                    item.className = 'variant-preview-item position-relative';
                    item.style.width = '80px';
                    item.style.height = '80px';
                    
                    item.innerHTML = `
                        <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -5px; right: -5px; padding: 2px 6px; border-radius: 50%; font-size: 0.7rem; z-index: 10;" onclick="event.stopPropagation(); removeVariantFile(${index}, fileInput, container, prompt)">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    container.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        } else {
            prompt.style.display = 'block';
            container.style.setProperty('display', 'none', 'important');
        }
    }

    window.removeVariantFile = function(fileIndex, fileInput, container, prompt) {
        const dt = new DataTransfer();
        Array.from(fileInput.files).forEach((file, index) => {
            if (index !== fileIndex) dt.items.add(file);
        });
        fileInput.files = dt.files;
        updateVariantPreviews(fileInput, container, prompt);
    };

    document.querySelector('.btn-add-size').addEventListener('click', () => {
        const sizeRows = document.getElementById('colorSizeRows');
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center mb-2 size-row';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" name="size_names[]" class="form-control form-control-sm" placeholder="Size (Optional)">
            </div>
            <div class="col-5">
                <input type="number" name="size_stocks[]" class="form-control form-control-sm" placeholder="Stock" required min="0">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-size"><i class="fas fa-trash"></i></button>
            </div>
        `;
        sizeRows.appendChild(row);
        
        row.querySelector('.btn-remove-size').addEventListener('click', () => {
            row.remove();
            checkDeleteButtons();
        });
        
        checkDeleteButtons();
    });
    
    function checkDeleteButtons() {
        const buttons = document.querySelectorAll('#colorSizeRows .btn-remove-size');
        buttons.forEach(btn => {
            if (buttons.length > 1) {
                btn.removeAttribute('disabled');
            } else {
                btn.setAttribute('disabled', 'disabled');
            }
        });
    }

    window.addSizeRow = function(containerClass) {
        const sizeRows = document.querySelector('.' + containerClass);
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-center mb-2 size-row';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" name="size_names[]" class="form-control form-control-sm" placeholder="Size" required>
            </div>
            <div class="col-5">
                <input type="number" name="size_stocks[]" class="form-control form-control-sm" placeholder="Stock" required min="0">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.size-row').remove()"><i class="fas fa-trash"></i></button>
            </div>
        `;
        sizeRows.appendChild(row);
    };

    const editTagsInput = document.getElementById('edit_tags');
    if(editTagsInput) {
        new Tagify(editTagsInput, {
            delimiters: ",|\\n|\\r|\\t|;|•|•|- |#",
            trim: true,
            duplicates: false,
            transformTag: function(tagData) {
                let val = tagData.value || '';
                val = val.replace(/^[\s\d\.\-\*•#–—]+/, '');
                val = val.replace(/^["'`]|["'`]$/g, '');
                tagData.value = val.trim();
            }
        });
    }

    // Client-side duplicate variation name check & upload spinner
    const existingColorNames = <?= json_encode(array_values(array_unique(array_map('strtolower', array_map('trim', array_keys($colors_map)))))) ?>;
    const addColorForm = document.getElementById('addColorVariationForm');
    if (addColorForm) {
        addColorForm.addEventListener('submit', function(e) {
            const input = document.getElementById('new_color_name');
            if (input) {
                const val = input.value.trim().toLowerCase();
                if (existingColorNames.includes(val)) {
                    e.preventDefault();
                    if (typeof showVariantExistsAlert === 'function') {
                        showVariantExistsAlert(input.value.trim());
                    } else {
                        alert('A variation named "' + input.value.trim() + '" already exists for this product.');
                    }
                    input.focus();
                    return false;
                }
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving Variation...';
            }
        });
    }
</script>

=======
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
