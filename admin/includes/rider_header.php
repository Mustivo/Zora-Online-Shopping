<?php
require_once '../core/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Dashboard - ZORA</title>
<link rel="icon" href="../uploads/icon.png">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">
<style>
    body { background-color: var(--bg2); color: var(--text); padding-bottom: 80px; }
    .rider-nav { position: fixed; bottom: 0; left: 0; right: 0; background: var(--card); border-top: 1px solid var(--border); display: flex; justify-content: space-around; padding: 10px 0; z-index: 1000; }
    .rider-nav a { text-align: center; color: var(--text3); text-decoration: none; font-size: 0.8rem; }
    .rider-nav a i { display: block; font-size: 1.5rem; margin-bottom: 2px; }
    .rider-nav a.active { color: var(--primary); }
</style>
</head>
<body>
<div class="p-3 bg-primary text-white d-flex justify-content-between align-items-center shadow-sm" style="position: sticky; top:0; z-index: 1000;">
    <h5 class="m-0 fw-bold"><i class="fas fa-motorcycle me-2"></i> Zora Rider</h5>
    <a href="../core/actions.php?action=admin_logout" class="text-white text-decoration-none fw-medium"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
</div>
<div class="container-fluid px-4 mt-4">
