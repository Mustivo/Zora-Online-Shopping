<?php
require_once 'includes/header.php';

$active_tab = $_POST['active_tab'] ?? ($_GET['tab'] ?? 'locations-content');

// Handle POST request to update delivery fee for hierarchy locations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_fee') {
    $loc_id = (int)$_POST['location_id'];
    $new_fee = (float)$_POST['delivery_fee'];
    $active_tab = 'locations-content';
    
    $update_sql = "UPDATE rwanda_locations SET delivery_fee = $new_fee WHERE id = $loc_id";
    if (mysqli_query($conn, $update_sql)) {
        $msg = "Location delivery fee updated successfully.";
    } else {
        $error = "Error updating fee: " . mysqli_error($conn);
    }
}

// Handle bulk update for Rwanda locations / hierarchy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_update_locations') {
    $bulk_loc_fee = (float)$_POST['bulk_location_fee'];
    $loc_scope = clean_input($conn, $_POST['location_scope'] ?? 'all');
    $active_tab = 'locations-content';
    
    if ($loc_scope === 'all') {
        $bulk_loc_sql = "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee";
        if (mysqli_query($conn, $bulk_loc_sql)) {
            $affected = mysqli_affected_rows($conn);
            $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all Rwanda locations ($affected locations updated).";
        } else {
            $error = "Error updating location fees: " . mysqli_error($conn);
        }
    } elseif ($loc_scope === 'kigali_all') {
        mysqli_query($conn, "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee WHERE id = 1 OR parent_id = 1 OR parent_id IN (415, 998, 1378) OR parent_id IN (SELECT id FROM (SELECT id FROM rwanda_locations WHERE parent_id IN (415, 998, 1378)) t1) OR parent_id IN (SELECT id FROM (SELECT id FROM rwanda_locations WHERE parent_id IN (SELECT id FROM rwanda_locations WHERE parent_id IN (415, 998, 1378))) t2)");
        $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all Kigali locations.";
    } elseif ($loc_scope === 'provinces') {
        $bulk_loc_sql = "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee WHERE type = 'province'";
        if (mysqli_query($conn, $bulk_loc_sql)) {
            $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all 5 Provinces.";
        } else {
            $error = "Error updating location fees: " . mysqli_error($conn);
        }
    } elseif ($loc_scope === 'districts') {
        $bulk_loc_sql = "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee WHERE type = 'district'";
        if (mysqli_query($conn, $bulk_loc_sql)) {
            $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all 30 Districts.";
        } else {
            $error = "Error updating location fees: " . mysqli_error($conn);
        }
    } elseif ($loc_scope === 'sectors') {
        $bulk_loc_sql = "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee WHERE type = 'sector'";
        if (mysqli_query($conn, $bulk_loc_sql)) {
            $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all 416 Sectors.";
        } else {
            $error = "Error updating location fees: " . mysqli_error($conn);
        }
    } elseif ($loc_scope === 'villages') {
        $bulk_loc_sql = "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee WHERE type = 'village'";
        if (mysqli_query($conn, $bulk_loc_sql)) {
            $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all 14,842 Villages.";
        } else {
            $error = "Error updating location fees: " . mysqli_error($conn);
        }
    } else {
        mysqli_query($conn, "UPDATE rwanda_locations SET delivery_fee = $bulk_loc_fee");
        $msg = "Set delivery fee of " . number_format($bulk_loc_fee, 0) . " RFW for all locations.";
    }
}

// Fetch Provinces for overview table
$provinces_res = mysqli_query($conn, "SELECT id, name, delivery_fee FROM rwanda_locations WHERE type = 'province' ORDER BY id ASC");
$provinces_list = [];
if ($provinces_res) {
    while ($p = mysqli_fetch_assoc($provinces_res)) {
        $provinces_list[] = $p;
    }
}
?>

<div class="admin-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="admin-page-title"><i class="fas fa-shipping-fast me-2 text-accent"></i> Delivery Rates & Locations</h2>
        <p class="text-muted small mb-0">Manage delivery fees for all Rwanda provinces, districts, sectors, cells, and villages.</p>
    </div>
</div>

<?php if(isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if(isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<!-- NAVIGATION TABS -->
<ul class="nav nav-pills mb-4" id="shippingTabs" role="tablist" style="background: var(--bg2); padding: 5px; border-radius: 12px; display: inline-flex;">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'locations-content' ? 'active' : '' ?> fw-bold px-4" id="locations-tab" data-bs-toggle="tab" data-bs-target="#locations-content" type="button" role="tab" style="border-radius: 8px;">
            <i class="fas fa-sitemap me-2"></i> Locations & Hierarchy
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'delivery-methods-content' ? 'active' : '' ?> fw-bold px-4" id="delivery-methods-tab" data-bs-toggle="tab" data-bs-target="#delivery-methods-content" type="button" role="tab" style="border-radius: 8px;">
            <i class="fas fa-truck me-2"></i> Delivery Methods
        </button>
    </li>
</ul>

<div class="tab-content" id="shippingTabsContent">
    <!-- ==================== TAB 1: PROVINCES & DISTRICTS HIERARCHY ==================== -->
    <div class="tab-pane fade <?= $active_tab === 'locations-content' ? 'show active' : '' ?>" id="locations-content" role="tabpanel">
        <div class="row g-4">
            <div class="col-md-7">
                <div class="admin-card p-4 h-100">
                    <h5 class="mb-2 fw-bold"><i class="fas fa-globe-africa text-accent me-2"></i> Location Delivery Fees</h5>
                    <p class="text-muted small mb-3">Set delivery fees by Province, District, Sector, Cell, or Village across Rwanda.</p>

                    <!-- BULK PRICE UPDATE FOR LOCATIONS -->
                    <div class="p-3 mb-4 rounded-3" style="background: rgba(251, 124, 0, 0.05); border: 1.5px solid rgba(251, 124, 0, 0.25);">
                        <form method="POST" action="" class="row g-2 align-items-center" onsubmit="return customConfirm(event, 'Apply this delivery fee to all selected locations?')">
                            <input type="hidden" name="action" value="bulk_update_locations">
                            <input type="hidden" name="active_tab" value="locations-content">
                            <div class="col-md-4">
                                <span class="fw-bold small text-accent"><i class="fas fa-layer-group me-1"></i> Set Same Fee for Locations:</span>
                            </div>
                            <div class="col-md-3">
                                <select name="location_scope" class="form-select form-select-sm fw-medium">
                                    <option value="all">All Rwanda Locations (All)</option>
                                    <option value="kigali_all">All Kigali Locations</option>
                                    <option value="provinces">All 5 Provinces</option>
                                    <option value="districts">All 30 Districts</option>
                                    <option value="sectors">All 416 Sectors</option>
                                    <option value="villages">All 14,842 Villages</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="bulk_location_fee" class="form-control fw-bold" placeholder="e.g. 1500" step="0.01" min="0" required>
                                    <span class="input-group-text">RFW</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold"><i class="fas fa-check-double me-1"></i> Apply</button>
                            </div>
                        </form>
                    </div>

                    <!-- PROVINCES QUICK OVERVIEW TABLE -->
                    <div class="mb-4">
                        <label class="form-label text-primary fw-bold small text-uppercase"><i class="fas fa-map me-1"></i> Provinces Overview & Quick Fee</label>
                        <div class="table-responsive rounded border" style="border-color: var(--border) !important;">
                            <table class="table admin-table align-middle mb-0">
                                <thead style="background: var(--bg2);">
                                    <tr>
                                        <th>Province</th>
                                        <th style="width: 240px;">Current Fee (RFW)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $prov_names = [
                                        'KIGALI' => 'Kigali City (Umujyi wa Kigali)',
                                        'SOUTH' => 'Southern Province (Amajyepfo)',
                                        'WEST' => 'Western Province (Iburengerazuba)',
                                        'NORTH' => 'Northern Province (Amajyaruguru)',
                                        'EAST' => 'Eastern Province (Iburasirazuba)'
                                    ];
                                    foreach($provinces_list as $prov): 
                                        $p_label = $prov_names[strtoupper(trim($prov['name']))] ?? $prov['name'];
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold"><?= htmlspecialchars($p_label) ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-flex align-items-center gap-2 m-0">
                                                    <input type="hidden" name="action" value="update_fee">
                                                    <input type="hidden" name="location_id" value="<?= $prov['id'] ?>">
                                                    <input type="hidden" name="active_tab" value="locations-content">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="delivery_fee" class="form-control text-end fw-bold" value="<?= (float)$prov['delivery_fee'] ?>" step="0.01" min="0" required>
                                                        <span class="input-group-text">RFW</span>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-success text-nowrap"><i class="fas fa-save"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <hr class="my-4" style="border-color: var(--border);">

                    <!-- LOCATION DRILLDOWN SELECTORS -->
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-sitemap me-1"></i> Drilldown Hierarchy Explorer</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-primary fw-bold small">Province</label>
                            <select class="form-select" id="sel_province" onchange="loadLocations('district', this.value); setSelectedLoc(this.value, 'province');">
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-primary fw-bold small">District</label>
                            <select class="form-select" id="sel_district" onchange="loadLocations('sector', this.value); setSelectedLoc(this.value, 'district');" disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-primary fw-bold small">Sector</label>
                            <select class="form-select" id="sel_sector" onchange="loadLocations('cell', this.value); setSelectedLoc(this.value, 'sector');" disabled>
                                <option value="">Select Sector</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-primary fw-bold small">Cell</label>
                            <select class="form-select" id="sel_cell" onchange="loadLocations('village', this.value); setSelectedLoc(this.value, 'cell');" disabled>
                                <option value="">Select Cell</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-primary fw-bold small">Village</label>
                            <select class="form-select" id="sel_village" onchange="setSelectedLoc(this.value, 'village');" disabled>
                                <option value="">Select Village</option>
                            </select>
                        </div>
                    </div>

                    <form method="POST" action="" class="border p-3 rounded mb-4" style="background: var(--bg1); display: none; border-color: var(--accent) !important;" id="dropdownFeeForm">
                        <input type="hidden" name="action" value="update_fee">
                        <input type="hidden" name="location_id" id="dropdownLocId" value="">
                        <input type="hidden" name="active_tab" value="locations-content">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Location</label>
                                <input type="text" class="form-control text-capitalize" id="dropdownLocName" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Fee (RFW)</label>
                                <input type="number" name="delivery_fee" id="dropdownLocFee" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-1"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="admin-card p-4 h-100">
                    <h5 class="mb-2 fw-bold"><i class="fas fa-search me-2 text-primary"></i> Quick Location Search</h5>
                    <p class="text-muted small mb-3">Lookup and edit any specific location rate across Rwanda.</p>
                    <div class="border p-3 rounded mb-3" style="background: var(--bg1);">
                        <form id="searchForm" onsubmit="event.preventDefault(); executeSearch();" class="d-flex align-items-end gap-2">
                            <div class="flex-grow-1">
                                <input type="text" id="globalDestSearch" class="form-control" placeholder="Search (e.g. Musanze, Rubavu, Huye)..." autocomplete="off" required>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive" id="searchTableContainer" style="display: none;">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Destination</th>
                                    <th>Type</th>
                                    <th>Fee (RFW)</th>
                                </tr>
                            </thead>
                            <tbody id="searchTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: DELIVERY METHODS ==================== -->
    <div class="tab-pane fade <?= $active_tab === 'delivery-methods-content' ? 'show active' : '' ?>" id="delivery-methods-content" role="tabpanel">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1 fw-bold">Delivery Methods</h5>
                    <p class="text-muted small mb-0">Additional delivery options (e.g. Express, Doorstep, Store Pickup).</p>
                </div>
                <a href="delivery_methods.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog me-1"></i> Configure Methods
                </a>
            </div>
            
            <div class="p-3 rounded-3" style="background: rgba(251,124,0,0.08); border: 1px dashed var(--accent);">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fas fa-info-circle text-accent"></i>
                    <span class="text-accent fw-bold small">How Total Delivery Is Calculated</span>
                </div>
                <p class="text-muted mb-0 small">Base location fee + selected delivery method extra fee = Final Delivery Fee.</p>
            </div>
        </div>
    </div>
</div>

<style>
#shippingTabs .nav-link.active {
    background-color: var(--accent);
    color: white !important;
}
</style>

<script>
// Initial Load & Tab Persistence
document.addEventListener('DOMContentLoaded', () => {
    loadLocations('province', null);

    // Tab persistence
    const savedTab = sessionStorage.getItem('activeShippingTab');
    if (savedTab) {
        const btn = document.querySelector(`button[data-bs-target="${savedTab}"]`);
        if (btn) {
            const tabInstance = bootstrap.Tab.getOrCreateInstance(btn);
            tabInstance.show();
        }
    }

    document.querySelectorAll('#shippingTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                sessionStorage.setItem('activeShippingTab', target);
            }
        });
    });
});

// Hierarchy Locations Functions
async function loadLocations(type, parent_id) {
    const types = ['province', 'district', 'sector', 'cell', 'village'];
    let startIndex = types.indexOf(type);
    
    if(startIndex === -1) return;
    
    for (let i = startIndex; i < types.length; i++) {
        const sel = document.getElementById('sel_' + types[i]);
        if(sel) {
            sel.innerHTML = `<option value="">Select ${types[i].charAt(0).toUpperCase() + types[i].slice(1)}</option>`;
            sel.disabled = true;
        }
    }
    
    if (!parent_id && type !== 'province') {
        document.getElementById('dropdownFeeForm').style.display = 'none';
        return;
    }
    
    try {
        const url = `../api_locations.php?type=${type}` + (parent_id ? `&parent_id=${parent_id}` : '');
        const res = await fetch(url);
        const json = await res.json();
        const sel = document.getElementById('sel_' + type);
        
        if (json.status === 'success' && json.data.length > 0) {
            json.data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.dataset.fee = item.delivery_fee;
                opt.dataset.name = item.name;
                opt.textContent = item.name + (item.delivery_fee > 0 ? ` (${new Intl.NumberFormat().format(item.delivery_fee)} RFW)` : '');
                sel.appendChild(opt);
            });
            sel.disabled = false;
        }
    } catch(e) { console.error(e); }
}

function setSelectedLoc(loc_id, type) {
    const sel = document.getElementById('sel_' + type);
    if (!loc_id || sel.selectedIndex <= 0) {
        document.getElementById('dropdownFeeForm').style.display = 'none';
        return;
    }
    
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('dropdownLocId').value = loc_id;
    document.getElementById('dropdownLocName').value = opt.dataset.name + ' (' + type + ')';
    document.getElementById('dropdownLocFee').value = opt.dataset.fee || 0;
    
    document.getElementById('dropdownFeeForm').style.display = 'block';
}

async function executeSearch() {
    const query = document.getElementById('globalDestSearch').value;
    const container = document.getElementById('searchTableContainer');
    const tbody = document.getElementById('searchTableBody');
    
    if (query.trim().length < 2) {
        container.style.display = 'none';
        return;
    }
    
    try {
        const url = `../api_locations.php?search=` + encodeURIComponent(query);
        const res = await fetch(url);
        const json = await res.json();
        
        tbody.innerHTML = '';
        if (json.status === 'success' && json.data.length > 0) {
            json.data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${item.name}</strong></td>
                    <td><span class="badge bg-secondary text-capitalize">${item.type}</span></td>
                    <td>
                        <form method="POST" action="" class="d-flex align-items-center m-0 gap-2">
                            <input type="hidden" name="action" value="update_fee">
                            <input type="hidden" name="location_id" value="${item.id}">
                            <input type="hidden" name="active_tab" value="locations-content">
                            <input type="number" name="delivery_fee" class="form-control form-control-sm" style="width: 100px;" value="${item.delivery_fee}" step="0.01" min="0" required>
                            <button type="submit" class="btn btn-sm btn-success text-nowrap"><i class="fas fa-save"></i> Update</button>
                        </form>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            container.style.display = 'block';
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="3" class="text-center text-muted py-3">No destinations found.</td>';
            tbody.appendChild(tr);
            container.style.display = 'block';
        }
    } catch(e) { console.error(e); }
}
</script>

<?php require_once 'includes/footer.php'; ?>
