<?php
require_once 'includes/header.php';
date_default_timezone_set('Africa/Kigali');

$cats_query = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = mysqli_fetch_assoc($cats_query)) {
    $categories[] = $c;
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
.tagify__input { color: var(--text); }
.tagify__tag > div::before { box-shadow: 0 0 0 var(--tag-inset-shadow-size) var(--tag-bg) inset; }

/* Drag and Drop Zone */
.drop-zone {
    border: 2px dashed var(--accent);
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    background: var(--bg2);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.drop-zone.dragover {
    background: rgba(251, 124, 0, 0.1);
    border-color: #fb7c00;
}
.drop-zone-prompt {
    font-size: 1.1rem;
    color: var(--text);
    margin-top: 15px;
}
.drop-zone-icon {
    font-size: 3rem;
    color: var(--accent);
}

/* Image Preview Carousel */
.preview-container {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    display: none;
    background: var(--bg1);
    border-radius: 10px;
    overflow: hidden;
}
.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.carousel-controls {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 10px;
    pointer-events: none;
}
.carousel-btn {
    background: rgba(0,0,0,0.6);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    pointer-events: auto;
    transition: 0.2s;
}
.carousel-btn:hover { background: var(--accent); }
.preview-counter {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

/* Variant Builder */
.variant-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    position: relative;
}
.btn-remove-variant {
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-plus-circle me-2 text-accent"></i> Add New Product</h2>
    <a href="products.php" class="btn btn-secondary">Cancel</a>
</div>

<form action="actions.php" method="POST" enctype="multipart/form-data" id="addProductForm">
    <input type="hidden" name="action" value="add_product_advanced">
    
    <div class="row g-4">
        <!-- Left Column: Images -->
        <div class="col-lg-5">
            <div class="admin-card p-4 h-100">
                <h4 class="mb-3">Product Images</h4>
                <p class="text-muted small mb-3">Drag & drop 2 or more images. The first image will be set as the main product photo, and other photos will be added to the product gallery.</p>
                
                <div class="drop-zone" id="dropZone" style="cursor: pointer; min-height: 220px; border: 2px dashed var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; position: relative; background: var(--bg1); padding: 15px;">
                    <div id="dropPrompt" class="text-center">
                        <i class="fas fa-cloud-upload-alt drop-zone-icon" style="font-size: 3rem; color: var(--accent); margin-bottom: 10px;"></i>
                        <div class="drop-zone-prompt fw-bold">Drag & Drop 2 or more images, or click to browse</div>
                        <small class="text-muted d-block mt-2">Supports JPG, PNG, WEBP (Select multiple)</small>
                    </div>
                    
                    <div class="preview-grid" id="previewGrid" style="display: none; width: 100%; display: none; flex-wrap: wrap; gap: 10px; justify-content: center;">
                        <!-- Dynamic Multi-Image Thumbnails inserted by JS -->
                    </div>
                    
                    <input type="file" name="images[]" id="fileInput" accept="image/*" multiple style="display: none;" required>
                </div>
                
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearImagesBtn" style="display:none;"><i class="fas fa-trash me-1"></i> Clear All Images</button>
                </div>
                
                <div class="mt-4">
                    <label class="form-label text-muted small text-uppercase fw-bold">Or Provide Image URL</label>
                    <input type="url" name="image_url" id="imageUrlInput" class="form-control" placeholder="https://example.com/image.jpg">
                </div>
            </div>
        </div>
        
        <!-- Right Column: Details -->
        <div class="col-lg-7">
            <div class="admin-card p-4">
                <h4 class="mb-4">Basic Information</h4>
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Nike Air Max" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Price (RFW)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Price (RFW) <span class="text-muted">(Optional)</span></label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Expiry Time <span class="text-muted">(Optional)</span></label>
                        <input type="datetime-local" name="discount_expiry" class="form-control" min="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Stock</label>
                        <input type="number" name="stock" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="product_description" class="form-control" placeholder="Detailed product description..." rows="5"></textarea>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <label class="form-label">Search Tags</label>
                        <input name="tags" class="form-control" id="product_tags" placeholder="e.g. electronics, modern, fast">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Variants Builder -->
    <div class="admin-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Product Variants (Colors & Sizes)</h4>
                <p class="text-muted small mb-0">Add different colors or specific characteristics. The product name remains the same, but customers can choose these options.</p>
            </div>
            <button type="button" class="btn btn-outline-primary" id="addVariantBtn"><i class="fas fa-plus"></i> Add Variant</button>
        </div>
        
        <div id="variantsContainer">
            <!-- Dynamic variants will go here -->
        </div>
    </div>
    
    <div class="mt-4 mb-5 text-end">
        <button type="submit" class="btn btn-success btn-lg px-5"><i class="fas fa-save me-2"></i> Save Product</button>
    </div>
</form>

<script>
// Smart Tagify Configuration with ChatGPT & Multi-platform copy-paste support
const productTagsInput = document.getElementById('product_tags');
if (productTagsInput) {
    new Tagify(productTagsInput, {
        delimiters: ",|\\n|\\r|\\t|;|•|•|- |#",
        trim: true,
        duplicates: false,
        transformTag: function(tagData) {
            let val = tagData.value || '';
            // Strip bullet points, numbered lists (1., 2.), markdown dashes, hashtags, and wrapping quotes
            val = val.replace(/^[\s\d\.\-\*•#–—]+/, '');
            val = val.replace(/^["'`]|["'`]$/g, '');
            tagData.value = val.trim();
        }
    });
}

// --- Drag & Drop Image Logic (Multiple Files) ---
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const previewGrid = document.getElementById('previewGrid');
const dropPrompt = document.getElementById('dropPrompt');
const clearImagesBtn = document.getElementById('clearImagesBtn');
const imageUrlInput = document.getElementById('imageUrlInput');

dropZone.addEventListener('click', (e) => {
    if (!e.target.closest('.remove-single-img-btn')) {
        fileInput.click();
    }
});

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--accent)';
    dropZone.style.backgroundColor = 'var(--bg2)';
});
dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = 'var(--border)';
    dropZone.style.backgroundColor = 'var(--bg1)';
});
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--border)';
    dropZone.style.backgroundColor = 'var(--bg1)';
    if (e.dataTransfer.files.length) {
        addFilesToInput(e.dataTransfer.files);
    }
});
fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
        addFilesToInput(fileInput.files, true);
    }
});

function addFilesToInput(newFiles, isChange = false) {
    const dt = new DataTransfer();
    
    // Keep existing files if not a direct file input change
    if (!isChange) {
        Array.from(fileInput.files).forEach(f => dt.items.add(f));
    }
    
    Array.from(newFiles).forEach(f => {
        if (f.type.startsWith('image/')) {
            dt.items.add(f);
        }
    });
    
    fileInput.files = dt.files;
    renderMultiPreviews();
}

function renderMultiPreviews() {
    previewGrid.innerHTML = '';
    const files = Array.from(fileInput.files);
    
    if (files.length > 0) {
        dropPrompt.style.display = 'none';
        previewGrid.style.display = 'flex';
        clearImagesBtn.style.display = 'inline-block';
        fileInput.removeAttribute('required');
        
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const item = document.createElement('div');
                item.className = 'position-relative';
                item.style.width = '100px';
                item.style.height = '100px';
                item.style.borderRadius = '10px';
                item.style.overflow = 'hidden';
                item.style.border = index === 0 ? '2px solid var(--accent)' : '1px solid var(--border)';
                item.style.boxShadow = '0 2px 6px rgba(0,0,0,0.1)';
                
                const isPrimary = index === 0;
                item.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                    <span class="badge ${isPrimary ? 'bg-primary' : 'bg-secondary'} position-absolute bottom-0 start-50 translate-middle-x mb-1" style="font-size: 0.65rem; border-radius: 4px; padding: 2px 5px;">
                        ${isPrimary ? 'Primary' : 'Photo ' + (index + 1)}
                    </span>
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-single-img-btn" style="width: 22px; height: 22px; padding: 0; border-radius: 50%; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; z-index: 10;" onclick="event.stopPropagation(); removeSingleImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewGrid.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    } else {
        if (!imageUrlInput.value.trim()) {
            dropPrompt.style.display = 'block';
            previewGrid.style.display = 'none';
            clearImagesBtn.style.display = 'none';
            fileInput.setAttribute('required', 'required');
        }
    }
}

window.removeSingleImage = function(indexToRemove) {
    const dt = new DataTransfer();
    Array.from(fileInput.files).forEach((file, idx) => {
        if (idx !== indexToRemove) {
            dt.items.add(file);
        }
    });
    fileInput.files = dt.files;
    renderMultiPreviews();
};

clearImagesBtn.addEventListener('click', () => {
    fileInput.value = '';
    const dt = new DataTransfer();
    fileInput.files = dt.files;
    imageUrlInput.value = '';
    previewGrid.innerHTML = '';
    previewGrid.style.display = 'none';
    dropPrompt.style.display = 'block';
    clearImagesBtn.style.display = 'none';
    fileInput.setAttribute('required', 'required');
});

imageUrlInput.addEventListener('input', (e) => {
    const url = e.target.value.trim();
    if (url !== '') {
        fileInput.removeAttribute('required');
        dropPrompt.style.display = 'none';
        previewGrid.style.display = 'flex';
        previewGrid.innerHTML = `
            <div class="position-relative" style="width: 110px; height: 110px; border-radius: 10px; overflow: hidden; border: 2px solid var(--accent);">
                <img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">
                <span class="badge bg-primary position-absolute bottom-0 start-50 translate-middle-x mb-1" style="font-size: 0.65rem;">Primary URL</span>
            </div>
        `;
        clearImagesBtn.style.display = 'inline-block';
    } else if (fileInput.files.length === 0) {
        fileInput.setAttribute('required', 'required');
        previewGrid.style.display = 'none';
        dropPrompt.style.display = 'block';
        clearImagesBtn.style.display = 'none';
    }
});

// --- Dynamic Variants Logic ---
const variantsContainer = document.getElementById('variantsContainer');
const addVariantBtn = document.getElementById('addVariantBtn');
let variantIndex = 0; // Use an index for array grouping

function calculateTotalStock() {
    const stockInputs = document.querySelectorAll('input[name^="variant_size_stocks"]');
    const baseStockInput = document.querySelector('input[name="stock"]');
    
    if (stockInputs.length > 0) {
        let total = 0;
        stockInputs.forEach(inp => {
            total += parseInt(inp.value) || 0;
        });
        baseStockInput.value = total;
        baseStockInput.setAttribute('readonly', 'readonly');
        baseStockInput.title = "Automatically calculated from variants";
        baseStockInput.style.backgroundColor = 'var(--bg1)';
    } else {
        baseStockInput.removeAttribute('readonly');
        baseStockInput.title = "";
        baseStockInput.style.backgroundColor = '';
    }
}

variantsContainer.addEventListener('input', (e) => {
    if (e.target.matches('input[name^="variant_size_stocks"]')) {
        calculateTotalStock();
    }
});

addVariantBtn.addEventListener('click', () => {
    const currentIndex = variantIndex++;
    const div = document.createElement('div');
    div.className = 'variant-card';
    div.innerHTML = `
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant"><i class="fas fa-times"></i></button>
        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-7">
                <label class="form-label fw-bold small text-uppercase mb-1" style="letter-spacing: 0.5px; color: var(--primary);">
                    <i class="fas fa-palette me-1 text-accent"></i> Variant Name / Color <span class="text-danger">*</span>
                </label>
                <input type="text" name="variant_color[${currentIndex}]" class="form-control fw-medium" placeholder="e.g. Red, Midnight Blue" required>
            </div>
            <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold small text-uppercase mb-0" style="letter-spacing: 0.5px; color: var(--primary);">
                        <i class="fas fa-tag me-1 text-accent"></i> Variant Price
                    </label>
                    <span class="badge bg-secondary-subtle text-muted border px-2 py-1" style="font-size: 0.68rem; font-weight: 600;">Optional</span>
                </div>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="variant_price[${currentIndex}]" class="form-control fw-semibold" placeholder="Base price">
                    <span class="input-group-text bg-light fw-bold text-muted small">RFW</span>
                </div>
            </div>
        </div>
        <div class="col-md-12">
                <label class="form-label">Variant Images <span class="text-muted">(Optional - Drag & Drop multiple images)</span></label>
                <div class="variant-drop-zone position-relative p-3 text-center rounded" style="border: 2px dashed var(--border); background: var(--bg1); cursor: pointer; min-height: 120px; transition: all 0.3s ease;">
                    <div class="variant-drop-prompt">
                        <i class="fas fa-images fs-3 text-muted mb-2"></i>
                        <p class="mb-0 text-muted small">Click to browse or drag and drop images here (Optional)</p>
                    </div>
                    <div class="variant-preview-container d-flex gap-2 flex-wrap mt-2 justify-content-center" style="display: none !important;"></div>
                    <input type="file" name="variant_images[${currentIndex}][]" class="variant-file-input" accept="image/*" multiple style="display: none;">
                </div>
            </div>
        </div>
        
        <div class="size-stock-container p-3 rounded" style="background: var(--bg1); border: 1px solid var(--border);">
            <div class="d-flex justify-content-between mb-2">
                <label class="form-label mb-0 fw-bold">Sizes & Inventory for this Variant</label>
                <button type="button" class="btn btn-sm btn-outline-primary btn-add-size"><i class="fas fa-plus"></i> Add Size</button>
            </div>
            <div class="size-rows">
                <div class="row g-2 align-items-center mb-2 size-row">
                    <div class="col-5">
                        <input type="text" name="variant_size_names[${currentIndex}][]" class="form-control form-control-sm" placeholder="Size (Optional)">
                    </div>
                    <div class="col-5">
                        <input type="number" name="variant_size_stocks[${currentIndex}][]" class="form-control form-control-sm" placeholder="Stock Qty" required min="0">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-size" disabled><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    `;
    variantsContainer.appendChild(div);
    calculateTotalStock();
    
    // Setup Drag & Drop and Previews for Variant Images
    const dropZoneEl = div.querySelector('.variant-drop-zone');
    const fileInp = div.querySelector('.variant-file-input');
    const promptEl = div.querySelector('.variant-drop-prompt');
    const previewContainerEl = div.querySelector('.variant-preview-container');

    dropZoneEl.addEventListener('click', (e) => {
        // Prevent click if clicking on a preview image or its remove button
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
            // Append files to input
            const dt = new DataTransfer();
            // Add existing files if any
            for(let i=0; i<fileInp.files.length; i++) {
                dt.items.add(fileInp.files[i]);
            }
            // Add new files
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
    
    // Remove variant
    div.querySelector('.btn-remove-variant').addEventListener('click', () => {
        div.remove();
        calculateTotalStock();
    });
    
    // Add size row
    const sizeRowsContainer = div.querySelector('.size-rows');
    div.querySelector('.btn-add-size').addEventListener('click', () => {
        const sizeDiv = document.createElement('div');
        sizeDiv.className = 'row g-2 align-items-center mb-2 size-row';
        sizeDiv.innerHTML = `
            <div class="col-5">
                <input type="text" name="variant_size_names[${currentIndex}][]" class="form-control form-control-sm" placeholder="Size (e.g. S, M, 42)" required>
            </div>
            <div class="col-5">
                <input type="number" name="variant_size_stocks[${currentIndex}][]" class="form-control form-control-sm" placeholder="Stock Qty" required min="0">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-size"><i class="fas fa-trash"></i></button>
            </div>
        `;
        sizeRowsContainer.appendChild(sizeDiv);
        calculateTotalStock();
        
        // Remove size row
        sizeDiv.querySelector('.btn-remove-size').addEventListener('click', () => {
            sizeDiv.remove();
            calculateTotalStock();
        });
        
        // Enable all trash buttons if there's more than 1
        const rows = sizeRowsContainer.querySelectorAll('.size-row');
        rows.forEach(r => r.querySelector('.btn-remove-size').removeAttribute('disabled'));
    });
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

// Global helper to remove a specific file from a variant's FileList
window.removeVariantFile = function(fileIndex, fileInput, container, prompt) {
    const dt = new DataTransfer();
    Array.from(fileInput.files).forEach((file, index) => {
        if (index !== fileIndex) dt.items.add(file);
    });
    fileInput.files = dt.files;
    updateVariantPreviews(fileInput, container, prompt);
};

// Validate that variant color names are not duplicated and show upload spinner
const addProdForm = document.getElementById('addProductForm');
if (addProdForm) {
    addProdForm.addEventListener('submit', function(e) {
        const colorInputs = document.querySelectorAll('input[name^="variant_color"]');
        const seenColors = new Set();
        
        for (let inp of colorInputs) {
            const val = inp.value.trim().toLowerCase();
            if (val) {
                if (seenColors.has(val)) {
                    e.preventDefault();
                    if (typeof showVariantExistsAlert === 'function') {
                        showVariantExistsAlert(inp.value.trim());
                    } else {
                        alert('Variant "' + inp.value.trim() + '" is duplicated. Please use unique variation names.');
                    }
                    inp.focus();
                    return false;
                }
                seenColors.add(val);
            }
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Optimizing & Saving Product...';
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
