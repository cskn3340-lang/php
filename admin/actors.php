<?php
/**
 * admin/actors.php - Oyuncu Yönetimi
 */

$admin_page_title = 'Oyuncular';
require_once __DIR__ . '/includes/admin_header.php';

// Oyuncu silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM actors WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['flash_success'] = 'Oyuncu silindi.';
        header('Location: ' . SITE_URL . '/admin/actors.php');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT a.*, (SELECT COUNT(*) FROM movie_actors WHERE actor_id = a.id) as movie_count 
    FROM actors a ORDER BY a.name ASC
");
$actors = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1><i class="fas fa-users"></i> Oyuncu Yönetimi</h1>
    <a href="<?= SITE_URL ?>/admin/actor_form.php" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Yeni Oyuncu Ekle
    </a>
</div>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Fotoğraf</th><th>Ad</th><th>Film Sayısı</th><th>İşlemler</th></tr>
        </thead>
        <tbody>
            <?php foreach ($actors as $actor): ?>
                <tr>
                    <td>#<?= $actor['id'] ?></td>
                    <td>
                        <img src="<?= e($actor['photo_url'] ?: 'https://via.placeholder.com/50x50/1a1a2e/e2b616?text=A') ?>" 
                             alt="" style="width:50px;height:50px;border-radius:50%;object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/50x50/1a1a2e/e2b616?text=A'">
                    </td>
                    <td><strong><?= e($actor['name']) ?></strong></td>
                    <td><span class="badge badge-blue"><?= $actor['movie_count'] ?></span></td>
                    <td>
                        <div class="admin-actions">
                            <a href="<?= SITE_URL ?>/admin/actor_form.php?id=<?= $actor['id'] ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/actors.php?delete=<?= $actor['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
                               class="admin-btn admin-btn-danger admin-btn-sm delete-confirm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
