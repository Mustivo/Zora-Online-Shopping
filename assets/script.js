function toggleCart() {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

function openAuthModal() {
    document.getElementById('authModal').classList.add('open');
}

function closeAuthModal() {
    document.getElementById('authModal').classList.remove('open');
}

function switchModalTab(tabId, btn) {
    document.querySelectorAll('.modal-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

function showToast(msg, isError = false) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast-msg ${isError ? 'error' : ''}`;
    toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>${msg}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Check URL params for messages (from actions.php redirects)
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const error = urlParams.get('error');
    if (msg) showToast(msg);
    if (error) showToast(error, true);
};

function toggleWishlist(productId, element) {
    fetch('core/actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle_wishlist&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error' && data.message === 'unauthorized') {
            openAuthModal();
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
