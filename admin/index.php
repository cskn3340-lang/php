<?php
/**
 * admin/index.php - Admin Dashboard
 * İstatistikler ve genel bakış
 */

$admin_page_title = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// İstatistikler
$stats = [];
$stats['movies'] = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['comments'] = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$stats['likes'] = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$stats['actors'] = $pdo->query("SELECT COUNT(*) FROM actors")->fetchColumn();
$stats['videos'] = $pdo->query("SELECT COUNT(*) FROM movie_videos")->fetchColumn();

// Son yorumlar
$stmt = $pdo->query("
    SELECT c.*, u.username, m.title as movie_title 
    FROM comments c 
    INNER JOIN users u ON c.user_id = u.id 
    INNER JOIN movies m ON c.movie_id = m.id 
    ORDER BY c.created_at DESC 
    LIMIT 5
");
$recent_comments = $stmt->fetchAll();

// En çok beğenilen filmler
$stmt = $pdo->query("
    SELECT m.title, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY like_count DESC 
    LIMIT 5
");
$top_movies = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
</div>

<!-- İstatistikler -->
<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-icon gold"><i class="fas fa-film"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['movies'] ?></h3>
            <p>Film</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['users'] ?></h3>
            <p>Kullanıcı</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon green"><i class="fas fa-comments"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['comments'] ?></h3>
            <p>Yorum</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon red"><i class="fas fa-heart"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['likes'] ?></h3>
            <p>Beğeni</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon purple"><i class="fas fa-user-tie"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['actors'] ?></h3>
            <p>Oyuncu</p>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon gold"><i class="fas fa-play-circle"></i></div>
        <div class="admin-stat-info">
            <h3><?= $stats['videos'] ?></h3>
            <p>Video</p>
        </div>
    </div>
</div>

<!-- Son Yorumlar & En Çok Beğenilenler -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Son Yorumlar -->
    <div class="admin-table-wrapper">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border);">
            <h3 style="font-size: 1rem;"><i class="fas fa-comments" style="color: var(--admin-accent);"></i> Son Yorumlar</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr><th>Kullanıcı</th><th>Film</th><th>Yorum</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_comments as $c): ?>
                    <tr>
                        <td><strong><?= e($c['username']) ?></strong></td>
                        <td><?= e(truncate($c['movie_title'], 20)) ?></td>
                        <td><?= e(truncate($c['content'], 40)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- En Çok Beğenilenler -->
    <div class="admin-table-wrapper">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--admin-border);">
            <h3 style="font-size: 1rem;"><i class="fas fa-fire" style="color: var(--admin-accent);"></i> En Çok Beğenilenler</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr><th>Film</th><th>Beğeni</th></tr>
            </thead>
            <tbody>
                <?php foreach ($top_movies as $tm): ?>
                    <tr>
                        <td><strong><?= e($tm['title']) ?></strong></td>
                        <td><span class="badge badge-gold"><?= $tm['like_count'] ?> ❤️</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
