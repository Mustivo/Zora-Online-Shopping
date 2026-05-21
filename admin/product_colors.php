<?php
require_once 'includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_query = mysqli_query($conn, "SELECT name, image FROM products WHERE id = $product_id");

if (mysqli_num_rows($product_query) == 0) {
    echo "<h2>Product not found.</h2>";
    require_once 'includes/footer.php';
    exit;
}

$product = mysqli_fetch_assoc($product_query);

$images_query = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY created_at DESC");
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
                <input type="hidden" name="action" value="add_product_color">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                
                <div class="mb-3">
                    <label class="form-label">Color Name</label>
                    <input type="text" name="color_name" class="form-control" placeholder="e.g. Red, Midnight Blue" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Upload Image</button>
            </form>
        </div>
    </div>
    
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
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <p class="text-muted">No additional color images added yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
