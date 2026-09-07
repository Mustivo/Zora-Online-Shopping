<?php
$page_title = "Reset Password - Zora Online Shopping";
require_once 'includes/header.php';

$token = isset($_GET['token']) ? clean_input($conn, $_GET['token']) : '';

// Verify token
if (empty($token)) {
    echo "<div class='container py-5 text-center' style='min-height:60vh;'><h3>Invalid request.</h3></div>";
    require_once 'includes/footer.php';
    exit;
}

$query = mysqli_query($conn, "SELECT id FROM users WHERE reset_token = '$token' AND reset_expires > NOW()");
if (mysqli_num_rows($query) === 0) {
    echo "<div class='container py-5 text-center' style='min-height:60vh;'><h3>Invalid or expired reset token.</h3><a href='forgot_password.php' class='btn btn-primary mt-3'>Request New Link</a></div>";
    require_once 'includes/footer.php';
    exit;
}
?>

<div class="container py-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="background: var(--card); color: var(--text);">
                <div class="card-body p-4">
                    <h4 class="card-title text-center mb-4"><?= __('reset_password_title') ?></h4>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>

                    <form action="core/actions.php" method="POST">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label"><?= __('new_password') ?></label>
                            <div class="password-toggle-wrap">
                                <input type="password" name="password" class="form-control" style="background: var(--bg1); color: var(--text); border-color: var(--border);" placeholder="<?= __('new_password_placeholder') ?>" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('confirm_password') ?></label>
                            <div class="password-toggle-wrap">
                                <input type="password" name="confirm_password" class="form-control" style="background: var(--bg1); color: var(--text); border-color: var(--border);" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?= __('reset_password_title') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
