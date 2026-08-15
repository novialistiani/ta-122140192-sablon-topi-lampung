function initPasswordToggle() {
    var togglePassword = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
        // Remove existing listener to prevent duplicates
        togglePassword.replaceWith(togglePassword.cloneNode(true));
        togglePassword = document.getElementById('togglePassword');
        
        togglePassword.addEventListener('click', function () {
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
}

function initAlertAutoHide() {
    // Auto-hide alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', function () {
    initPasswordToggle();
    initAlertAutoHide();
});

// Re-initialize after Livewire navigation (for SPA-like behavior)
document.addEventListener('livewire:navigated', function () {
    initPasswordToggle();
    initAlertAutoHide();
});