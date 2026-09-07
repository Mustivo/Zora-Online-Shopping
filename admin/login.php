<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - ZORA</title>
<link rel="icon" href="../uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< HEAD
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">
<style>
body, html {
    height: 100%;
    margin: 0;
    font-family: 'DM Sans', sans-serif;
    background-color: var(--bg);
}
.split-layout {
    display: flex;
    min-height: 100vh;
}
.split-left {
    flex: 1.1;
    background: linear-gradient(135deg, rgba(2,56,126,0.92) 0%, rgba(2,56,126,0.96) 100%), url('../uploads/hero.jpg') center/cover no-repeat;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    padding: 3rem;
    position: relative;
}
.split-left::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.05"><circle cx="50" cy="50" r="40" fill="white"/></svg>') repeat;
}
.split-left h1 {
    font-family: 'Playfair Display', serif;
    font-size: 4.5rem;
    font-weight: 900;
    letter-spacing: 3px;
    margin-bottom: 0.2rem;
    z-index: 1;
}
.split-left p {
    font-size: 1.1rem;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    z-index: 1;
    color: #93c5fd;
}
.split-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: var(--bg);
    padding: 2.5rem;
}
.login-form-container {
    width: 100%;
    max-width: 420px;
}
.login-header {
    margin-bottom: 2.2rem;
}
.login-header h2 {
    font-weight: 800;
    font-size: 1.85rem;
    color: var(--text);
    margin-bottom: 0.4rem;
    letter-spacing: -0.5px;
}
.login-header p {
    color: var(--text3);
    font-size: 0.95rem;
    margin: 0;
}
.form-label-custom {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text2);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
}

/* Modern Input Design */
.custom-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    background: var(--bg2, #f8fafc);
    border: 1.5px solid var(--border, #e2e8f0);
    border-radius: 10px;
    transition: all 0.25s ease;
}
.custom-input-box:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.12);
    background: #ffffff;
}
.custom-input-box .input-icon-left {
    position: absolute;
    left: 16px;
    color: #94a3b8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 2;
}
.custom-input-box:focus-within .input-icon-left {
    color: #2563eb;
}
.custom-input-field {
    width: 100%;
    padding: 0.85rem 1rem 0.85rem 2.85rem;
    background: transparent;
    border: none;
    outline: none;
    font-size: 0.95rem;
    color: var(--text, #1e293b);
    font-weight: 500;
    border-radius: 10px;
}
.custom-input-field.has-trailing-btn {
    padding-right: 2.85rem;
}
.custom-input-field::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
.password-toggle-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    padding: 6px 10px;
    color: #94a3b8;
    cursor: pointer;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
    z-index: 2;
}
.password-toggle-btn:hover {
    color: #2563eb;
    background: rgba(37, 99, 235, 0.08);
}

.btn-login {
    background: #2563eb;
=======
<link href="../assets/style.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, var(--primary) 0%, #02387e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    margin: 0;
}
.admin-login-wrapper {
    background: var(--bg);
    border-radius: 16px;
    padding: 3rem 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.4);
    position: relative;
    overflow: hidden;
}
.admin-login-wrapper::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 6px;
    background: var(--accent);
}
.admin-logo-text {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    color: var(--primary);
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 0.2rem;
    letter-spacing: 2px;
}
.admin-subtitle {
    text-align: center;
    color: var(--text3);
    font-size: 0.8rem;
    margin-bottom: 2.5rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 600;
}
.form-input {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.85rem 1rem;
    font-size: 0.95rem;
    width: 100%;
    transition: all 0.3s;
}
.form-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(251,124,0,0.15);
    background: #fff;
}
.btn-login {
    background: var(--accent);
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
    color: #fff;
    border: none;
    width: 100%;
    padding: 0.9rem;
<<<<<<< HEAD
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.92rem;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-top: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
}
.btn-login:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    color: #fff;
}
@media (max-width: 768px) {
    .split-left {
        display: none;
    }
    .split-right {
        padding: 1.5rem;
    }
=======
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-top: 1.5rem;
}
.btn-login:hover {
    background: var(--accent2);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(251,124,0,0.3);
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: var(--text3);
    font-size: 0.85rem;
    text-decoration: none;
    transition: color 0.2s;
    font-weight: 500;
}
.back-link:hover {
    color: var(--accent);
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
}
</style>
</head>
<body>

<<<<<<< HEAD
<div class="split-layout">
    <div class="split-left">
        <h1>ZORA</h1>
        <p>Admin Portal</p>
    </div>
    
    <div class="split-right">
        <div class="login-form-container">
            <div class="login-header text-center">
                <h2>Welcome Back</h2>
                <p>Please enter your credentials to access the dashboard.</p>
            </div>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger" style="font-size: 0.85rem; padding: 12px; border-radius: 8px; border-left: 4px solid var(--danger); background: rgba(224,85,85,0.1); color: var(--danger); border-top: none; border-right: none; border-bottom: none;"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success" style="font-size: 0.85rem; padding: 12px; border-radius: 8px; border-left: 4px solid var(--success); background: rgba(76,175,125,0.1); color: var(--success); border-top: none; border-right: none; border-bottom: none;"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>

            <form action="../core/actions.php" method="POST">
                <input type="hidden" name="action" value="admin_login">
                
                <div style="margin-bottom: 1.35rem;">
                    <label class="form-label-custom">Email Address</label>
                    <div class="custom-input-box">
                        <i class="fas fa-envelope input-icon-left"></i>
                        <input class="custom-input-field" type="email" name="email" placeholder="name@zora.com" required autocomplete="email">
                    </div>
                </div>
                
                <div style="margin-bottom: 1.35rem;">
                    <label class="form-label-custom">Password</label>
                    <div class="custom-input-box">
                        <i class="fas fa-lock input-icon-left"></i>
                        <input class="custom-input-field has-trailing-btn" type="password" name="password" id="adminPasswordInput" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" onclick="toggleAdminPassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt me-2"></i> Login to Dashboard</button>
                <a href="../index.php" class="d-block text-center mt-4 text-decoration-none" style="color: var(--text3); font-size: 0.85rem;"><i class="fas fa-arrow-left me-1"></i> Return to Main Site</a>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAdminPassword() {
    const input = document.getElementById('adminPasswordInput');
    const icon = document.getElementById('togglePasswordIcon');
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
=======
<div class="admin-login-wrapper">
    <div class="admin-logo-text">ZORA</div>
    <div class="admin-subtitle">Admin Portal</div>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger" style="font-size: 0.85rem; padding: 12px; border-radius: 8px; border-left: 4px solid var(--danger); background: rgba(224,85,85,0.1); color: var(--danger); border-top: none; border-right: none; border-bottom: none;"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success" style="font-size: 0.85rem; padding: 12px; border-radius: 8px; border-left: 4px solid var(--success); background: rgba(76,175,125,0.1); color: var(--success); border-top: none; border-right: none; border-bottom: none;"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <form action="../core/actions.php" method="POST">
        <input type="hidden" name="action" value="admin_login">
        
        <div style="margin-bottom: 1.2rem;">
            <label class="form-label-custom" style="font-size: 0.75rem; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Email Address</label>
            <input class="form-input" type="email" name="email" placeholder="admin@zora.com" required>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label class="form-label-custom" style="font-size: 0.75rem; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Password</label>
            <input class="form-input" type="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="btn-login">Login to Dashboard</button>
    </form>
</div>

>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
</body>
</html>
