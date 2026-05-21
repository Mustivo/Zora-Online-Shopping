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
    color: #fff;
    border: none;
    width: 100%;
    padding: 0.9rem;
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
}
</style>
</head>
<body>

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

</body>
</html>
