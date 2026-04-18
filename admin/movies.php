<?php
/**
 * admin/movies.php - Film Yönetimi Listesi
 * Tüm filmleri listele, sil
 */

$admin_page_title = 'Filmler';
require_once __DIR__ . '/includes/admin_header.php';

// Film silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['flash_success'] = 'Film silindi.';
        header('Location: ' . SITE_URL . '/admin/movies.php');
        exit;
    }
}

// Filmleri getir
$stmt = $pdo->query("
    SELECT m.*, COUNT(l.id) as like_count, 
    (SELECT COUNT(*) FROM comments WHERE movie_id = m.id) as comment_count
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY m.id DESC
");
$movies = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1><i class="fas fa-film"></i> Film Yönetimi</h1>
    <a href="<?= SITE_URL ?>/admin/movie_form.php" class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Yeni Film Ekle
    </a>
</div>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Afiş</th>
                <th>Film Adı</th>
                <th>Yıl</th>
                <th>Yönetmen</th>
                <th>Beğeni</th>
                <th>Yorum</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td>#<?= $movie['id'] ?></td>
                    <td>
                        <img src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/50x70/1a1a2e/e2b616?text=F') ?>" 
                             alt="" onerror="this.src='https://via.placeholder.com/50x70/1a1a2e/e2b616?text=F'">
                    </td>
                    <td><strong><?= e($movie['title']) ?></strong></td>
                    <td><?= e($movie['year']) ?></td>
                    <td><?= e($movie['director'] ?? '-') ?></td>
                    <td><span class="badge badge-gold"><?= $movie['like_count'] ?></span></td>
                    <td><span class="badge badge-blue"><?= $movie['comment_count'] ?></span></td>
                    <td>
                        <div class="admin-actions">
                            <a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>" class="admin-btn admin-btn-secondary admin-btn-sm" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/movie_form.php?id=<?= $movie['id'] ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/movies.php?delete=<?= $movie['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
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
