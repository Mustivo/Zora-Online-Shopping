<?php
$html = file_get_contents('c:/xampp/htdocs/Commerce/ecommerce.html');
preg_match('/<style>(.*?)<\/style>/s', $html, $matches);
if (isset($matches[1])) {
    $css = $matches[1];
    
    // Replace dark theme variables with light theme variables
    $css = preg_replace('/--bg: #0a0a0f;/', '--bg: #ffffff;', $css);
    $css = preg_replace('/--bg2: #111118;/', '--bg2: #f8f9fa;', $css);
    $css = preg_replace('/--bg3: #1a1a24;/', '--bg3: #f1f3f5;', $css);
    $css = preg_replace('/--card: #16161f;/', '--card: #ffffff;', $css);
    $css = preg_replace('/--border: #2a2a38;/', '--border: #e9ecef;', $css);
    $css = preg_replace('/--text: #f0ede8;/', '--text: #1a1a1a;', $css);
    $css = preg_replace('/--text2: #9a9aaa;/', '--text2: #495057;', $css);
    $css = preg_replace('/--text3: #6a6a7a;/', '--text3: #868e96;', $css);
    $css = preg_replace('/--accent2: #e8c97a;/', '--accent2: #b4933b;', $css);
    $css = preg_replace('/color: #000;/', 'color: #fff;', $css); // Buttons with accent background should have white text if needed, or keep black text if accent is gold. Gold and white or gold and black? Gold and black is fine, keep it. Wait, the above preg_replace doesn't affect color: #000. Let's leave it.

    $mobileNav = '
/* ===== MOBILE BOTTOM NAV (PWA STYLE) ===== */
.mobile-bottom-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--bg);
    border-top: 1px solid var(--border);
    z-index: 1000;
    padding: 0.5rem 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
}
.mobile-nav-item {
    flex: 1;
    text-align: center;
    color: var(--text3);
    text-decoration: none;
    font-size: 0.7rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.mobile-nav-item i {
    font-size: 1.2rem;
}
.mobile-nav-item.active, .mobile-nav-item:hover {
    color: var(--accent);
}
@media (max-width: 768px) {
    .mobile-bottom-nav {
        display: flex;
    }
    body {
        padding-bottom: 60px; /* Space for bottom nav */
    }
    .navbar-main .d-flex.align-items-center.gap-2 {
        /* Hide some elements on mobile header if needed */
    }
}
';
    $css .= $mobileNav;
    
    file_put_contents('c:/xampp/htdocs/Commerce/style.css', $css);
    echo "CSS extracted and modified.\n";
} else {
    echo "Style tag not found.\n";
}
?>
