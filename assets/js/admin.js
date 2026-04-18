/**
 * admin.js - Admin Panel JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (mobil)
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    
    sidebarToggle?.addEventListener('click', () => {
        adminSidebar?.classList.toggle('active');
    });
    
    // Silme onayı
    document.querySelectorAll('.delete-confirm').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Bu öğeyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                e.preventDefault();
            }
        });
    });
});
