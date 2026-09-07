<?php
require_once 'includes/header.php';

$query = mysqli_query($conn, "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 50");

// Mark all as read when page is visited
mysqli_query($conn, "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-bell me-2 text-accent"></i> Notifications & Activity Log</h2>
    <form action="actions.php" method="POST" onsubmit="return customConfirm(event, 'Clear all notifications?')">
        <input type="hidden" name="action" value="clear_notifications">
        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i> Clear All</button>
    </form>
</div>

<div class="admin-card overflow-hidden">
    <?php if(mysqli_num_rows($query) == 0): ?>
    <div class="text-center text-muted py-5">
        <i class="far fa-bell-slash fa-3x mb-3 d-block text-muted" style="opacity: 0.5;"></i>
        No recent notifications or activities.
    </div>
    <?php else: ?>
    <ul class="list-group list-group-flush">
        <?php while($row = mysqli_fetch_assoc($query)): 
            $icon = 'fas fa-info-circle text-info';
            $bg_color = 'rgba(23, 162, 184, 0.1)';
            if (strpos(strtolower($row['message']), 'order') !== false) {
                $icon = 'fas fa-shopping-cart text-primary';
                $bg_color = 'rgba(1, 42, 94, 0.1)';
            }
            if (strpos(strtolower($row['message']), 'user') !== false) {
                $icon = 'fas fa-user text-success';
                $bg_color = 'rgba(76, 175, 125, 0.1)';
            }
            if (strpos(strtolower($row['message']), 'stock') !== false) {
                $icon = 'fas fa-exclamation-triangle text-danger';
                $bg_color = 'rgba(220, 53, 69, 0.1)';
            }
        ?>
        <li class="list-group-item bg-transparent p-4 border-bottom" style="border-color: var(--border) !important;">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: <?= $bg_color ?>;">
                    <i class="<?= $icon ?> fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-1 fw-bold" style="color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($row['message']) ?></p>
                    <small class="text-muted"><i class="far fa-clock me-1"></i> <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></small>
                </div>
                <?php if($row['link'] && $row['link'] !== '#'): ?>
                <a href="<?= htmlspecialchars($row['link']) ?>" class="admin-action-btn"><i class="fas fa-eye text-primary"></i></a>
                <?php endif; ?>
            </div>
        </li>
        <?php endwhile; ?>
    </ul>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
