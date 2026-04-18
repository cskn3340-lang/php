<?php
/**
 * admin/comments.php - Yorum Moderasyonu
 * Tüm yorumları listele, uygunsuz yorumları sil
 */

$admin_page_title = 'Yorumlar';
require_once __DIR__ . '/includes/admin_header.php';

// Yorum silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['flash_success'] = 'Yorum silindi.';
        header('Location: ' . SITE_URL . '/admin/comments.php');
        exit;
    }
}

// Tüm yorumlar
$stmt = $pdo->query("
    SELECT c.*, u.username, m.title as movie_title 
    FROM comments c 
    INNER JOIN users u ON c.user_id = u.id 
    INNER JOIN movies m ON c.movie_id = m.id 
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1><i class="fas fa-comments"></i> Yorum Moderasyonu</h1>
    <span style="color: var(--admin-text-muted);"><?= count($comments) ?> yorum</span>
</div>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı</th>
                <th>Film</th>
                <th>Yorum</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><strong><?= e($c['username']) ?></strong></td>
                    <td>
                        <a href="<?= SITE_URL ?>/movie.php?id=<?= $c['movie_id'] ?>" target="_blank">
                            <?= e(truncate($c['movie_title'], 20)) ?>
                        </a>
                    </td>
                    <td><?= e(truncate($c['content'], 80)) ?></td>
                    <td style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= time_ago($c['created_at']) ?></td>
                    <td>
                        <a href="<?= SITE_URL ?>/admin/comments.php?delete=<?= $c['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
                           class="admin-btn admin-btn-danger admin-btn-sm delete-confirm">
                            <i class="fas fa-trash"></i> Sil
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
