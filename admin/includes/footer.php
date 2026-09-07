    </div> <!-- .admin-main -->
</div> <!-- .admin-layout -->

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.admin-table').forEach(function(table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            var needCard = !table.closest('.admin-card');
            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive border-0';
            
            if (needCard) {
                var card = document.createElement('div');
                card.className = 'admin-card mb-4';
                table.parentNode.insertBefore(card, table);
                card.appendChild(wrapper);
            } else {
                table.parentNode.insertBefore(wrapper, table);
            }
            wrapper.appendChild(table);
            table.classList.add('mb-0'); // Remove bottom margin when in a card
        }
    });
});
</script>
<script>
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update icons
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        icon.className = newTheme === 'dark' ? 'fas fa-sun darkModeIcon' : 'fas fa-moon darkModeIcon';
    });
}

// Set initial icon state
document.addEventListener('DOMContentLoaded', () => {
    // Check local storage first
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const icons = document.querySelectorAll('.darkModeIcon');
    icons.forEach(icon => {
        icon.className = isDark ? 'fas fa-sun darkModeIcon' : 'fas fa-moon darkModeIcon';
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="../assets/script.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#product_description').length > 0) {
        $('#product_description').summernote({
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['view', ['codeview', 'help']]
            ]
        });
    }
});
</script>
</body>
</html>
