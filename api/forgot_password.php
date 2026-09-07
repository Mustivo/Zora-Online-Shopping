<?php
$page_title = "Forgot Password - Zora Online Shopping";
require_once 'includes/header.php';
?>

<div class="container py-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="background: var(--card); color: var(--text);">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="uploads/logo.png" alt="Zora" style="height: 50px; object-fit: contain;">
                    </div>
                    <h4 class="card-title text-center mb-4"><?= __('forgot_password_title') ?></h4>
                    <p class="text-muted text-center mb-4"><?= __('forgot_password_desc') ?></p>
                    
                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>

                    <form action="core/actions.php" method="POST">
                        <input type="hidden" name="action" value="forgot_password">
                        <div class="mb-3">
                            <label class="form-label"><?= __('email_address') ?></label>
                            <input type="email" name="email" class="form-control" style="background: var(--bg1); color: var(--text); border-color: var(--border);" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?= __('send_reset_link') ?></button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="javascript:void(0)" onclick="openAuthModal()" style="color: var(--accent); text-decoration: none;"><?= __('back_to_login') ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
