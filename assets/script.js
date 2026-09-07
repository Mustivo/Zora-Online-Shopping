function toggleCart() {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

function openAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('open');
        modal.style.display = 'flex';
        const alertBox = document.getElementById('authAlert');
        if (alertBox) alertBox.classList.add('d-none');
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
    }
}

function switchModalTab(tabId, btn) {
    document.querySelectorAll('.modal-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
    const targetTab = document.getElementById(tabId);
    if (targetTab) targetTab.classList.add('active');
    if (btn) btn.classList.add('active');
}

function handleAuth(event, type) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const alertBox = document.getElementById('authAlert');
    
    if (type === 'register') {
        const pass = formData.get('password');
        const confirmPass = formData.get('confirm_password');
        
        if (pass !== confirmPass) {
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger mb-3';
                alertBox.style.borderLeft = '4px solid var(--danger)';
                alertBox.style.background = 'rgba(224,85,85,0.1)';
                alertBox.style.color = 'var(--danger)';
                alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Passwords do not match.';
            } else {
                showToast('Passwords do not match.', true);
            }
            return;
        }
        
        // Strong password: >=8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special character
        const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (!strongRegex.test(pass)) {
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger mb-3';
                alertBox.style.borderLeft = '4px solid var(--danger)';
                alertBox.style.background = 'rgba(224,85,85,0.1)';
                alertBox.style.color = 'var(--danger)';
                alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Password must be at least 8 characters long, and contain at least 1 uppercase letter, 1 number, and 1 special character.';
            } else {
                showToast('Password must be at least 8 characters long, with uppercase, number & symbol.', true);
            }
            return;
        }
    }
    
    formData.append('ajax', '1');
    
    fetch('core/actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (alertBox) {
            alertBox.classList.remove('d-none');
            if (data.status === 'success') {
                alertBox.className = 'alert alert-success mb-3';
                alertBox.style.borderLeft = '4px solid var(--success)';
                alertBox.style.background = 'rgba(76,175,125,0.1)';
                alertBox.style.color = 'var(--success)';
                alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
            } else {
                alertBox.className = 'alert alert-danger mb-3';
                alertBox.style.borderLeft = '4px solid var(--danger)';
                alertBox.style.background = 'rgba(224,85,85,0.1)';
                alertBox.style.color = 'var(--danger)';
                alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + (data.message || 'An error occurred.');
            }
        } else {
            showToast(data.message || (data.status === 'success' ? 'Success' : 'Error'), data.status !== 'success');
        }

        if (data.status === 'success') {
            setTimeout(() => {
                if (data.role === 'admin') {
                    window.location.href = 'admin/index.php';
                } else if (type === 'register') {
                    window.location.href = 'user_panel.php';
                } else {
                    window.location.reload();
                }
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Auth Error:', error);
        if (alertBox) {
            alertBox.classList.remove('d-none');
            alertBox.className = 'alert alert-danger mb-3';
            alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>A network error occurred. Please try again.';
        } else {
            showToast('A network error occurred. Please try again.', true);
        }
    });
}

function showToast(msg, isError = false) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast-msg ${isError ? 'error' : ''}`;
    toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>${msg}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 4000);
}

// Check URL params for messages (from actions.php redirects)
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const error = urlParams.get('error');
    if (msg) showToast(msg);
    if (error) showToast(error, true);
    initThemeIcons();
});

function toggleWishlist(productId, element) {
    fetch('core/actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle_wishlist&product_id=' + encodeURIComponent(productId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error' && data.message === 'unauthorized') {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            }
        } else if (data.status === 'success') {
            if (data.action === 'added') {
                element.classList.add('active');
                showToast('Added to favorites!');
            } else {
                element.classList.remove('active');
                showToast('Removed from favorites.');
            }
        }
    })
    .catch(err => console.error(err));
}

function toggleChatPopup() {
    const chatPopup = document.getElementById('chatPopupWidget');
    const chatBtn = document.querySelector('.floating-chat-btn');
    if (chatPopup) {
        chatPopup.classList.toggle('open');
        if (chatBtn) {
            if (chatPopup.classList.contains('open')) {
                chatBtn.style.transform = 'scale(0)';
                chatBtn.style.opacity = '0';
                chatBtn.style.pointerEvents = 'none';
            } else {
                chatBtn.style.transform = 'scale(1)';
                chatBtn.style.opacity = '1';
                chatBtn.style.pointerEvents = 'auto';
            }
        }
    }
}

function toggleDarkMode() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme') || (localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        if (newTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

function initThemeIcons() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || localStorage.getItem('theme') === 'dark';
    if (isDark) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

function togglePasswordVisibility(buttonOrId) {
    let btn, input;
    if (typeof buttonOrId === 'string') {
        input = document.getElementById(buttonOrId);
        btn = input ? input.parentElement.querySelector('.password-toggle-btn') : null;
    } else {
        btn = buttonOrId;
        const wrap = btn.closest('.password-toggle-wrap') || btn.parentElement;
        input = wrap ? wrap.querySelector('input[type="password"], input[type="text"]') : null;
    }
    
    if (input) {
        const icon = btn ? btn.querySelector('i') : null;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }
}

