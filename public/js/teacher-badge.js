// teacher-badge.js
// Actualiza el badge de solicitudes pendientes en el menú del profesor
(function() {
    function updateRequestsBadge() {
        const requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
        const pending = requests.filter(r => r.status === 'pending').length;
        const badge = document.getElementById('requestsBadge');
        if (badge) {
            badge.textContent = pending;
            badge.style.display = pending > 0 ? 'inline-block' : 'none';
        }
    }

    updateRequestsBadge();
    window.addEventListener('storage', updateRequestsBadge);
    setInterval(updateRequestsBadge, 10000);
})();