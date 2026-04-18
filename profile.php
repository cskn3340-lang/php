<?php
/**
 * profile.php - Kullanıcı Profili
 * Profil bilgileri, beğenilen filmler, izleme listesi, yorumlar
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

require_login();

$page_title = 'Profilim';
$user = get_current_user_data($pdo);

// Beğenilen filmler
$stmt = $pdo->prepare("
    SELECT m.*, COUNT(l2.id) as like_count 
    FROM movies m 
    INNER JOIN likes l ON m.id = l.movie_id AND l.user_id = ?
    LEFT JOIN likes l2 ON m.id = l2.movie_id
    GROUP BY m.id 
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$liked_movies = $stmt->fetchAll();

// İzleme listesi
$stmt = $pdo->prepare("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    INNER JOIN watchlist w ON m.id = w.movie_id AND w.user_id = ?
    LEFT JOIN likes l ON m.id = l.movie_id
    GROUP BY m.id 
    ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$watchlist_movies = $stmt->fetchAll();

// Kullanıcının yorumları
$stmt = $pdo->prepare("
    SELECT c.*, m.title as movie_title, m.id as movie_id 
    FROM comments c 
    INNER JOIN movies m ON c.movie_id = m.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$user_comments = $stmt->fetchAll();

// İstatistikler
$stats = [
    'likes' => count($liked_movies),
    'watchlist' => count($watchlist_movies),
    'comments' => count($user_comments)
];

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <section class="section">
        <!-- Profil Header -->
        <div class="profile-header animate-on-scroll">
            <div class="profile-avatar">
                <?= mb_strtoupper(mb_substr($user['username'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h1><?= e($user['username']) ?></h1>
                <p><i class="fas fa-envelope"></i> <?= e($user['email']) ?> &nbsp;•&nbsp; <i class="fas fa-calendar"></i> <?= format_date($user['created_at']) ?> tarihinde katıldı</p>
            </div>
            <div class="profile-stats">
                <div class="profile-stat">
                    <div class="profile-stat-number"><?= $stats['likes'] ?></div>
                    <div class="profile-stat-label">Beğeni</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-number"><?= $stats['watchlist'] ?></div>
                    <div class="profile-stat-label">Listem</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-number"><?= $stats['comments'] ?></div>
                    <div class="profile-stat-label">Yorum</div>
                </div>
            </div>
        </div>

        <!-- Profil Sekmeleri -->
        <div class="profile-tabs">
            <button class="profile-tab active" data-tab="likes"><i class="fas fa-heart"></i> Beğenilerim</button>
            <button class="profile-tab" data-tab="watchlist"><i class="fas fa-bookmark"></i> İzleme Listem</button>
            <button class="profile-tab" data-tab="comments"><i class="fas fa-comments"></i> Yorumlarım</button>
        </div>

        <!-- Beğenilen Filmler -->
        <div class="profile-tab-content active" id="profile-likes">
            <?php if (empty($liked_movies)): ?>
                <div class="no-results">
                    <i class="far fa-heart"></i>
                    <h3>Henüz film beğenmediniz</h3>
                    <p>Film kartlarındaki kalp ikonuna tıklayarak beğenmeye başlayın.</p>
                    <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary" style="margin-top: 16px;">Filmleri Keşfet</a>
                </div>
            <?php else: ?>
                <div class="movies-grid">
                    <?php foreach ($liked_movies as $movie): ?>
                        <div class="movie-card">
                            <div class="movie-card-poster">
                                <img src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film') ?>" 
                                     alt="<?= e($movie['title']) ?>" loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film'">
                                <span class="movie-card-year"><?= e($movie['year']) ?></span>
                                <div class="movie-card-poster-overlay">
                                    <a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-play"></i> Detaylar</a>
                                </div>
                            </div>
                            <div class="movie-card-body">
                                <h3 class="movie-card-title"><a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>"><?= e($movie['title']) ?></a></h3>
                                <div class="movie-card-meta">
                                    <span><i class="fas fa-heart" style="color: var(--accent-red)"></i> <?= $movie['like_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- İzleme Listesi -->
        <div class="profile-tab-content" id="profile-watchlist">
            <?php if (empty($watchlist_movies)): ?>
                <div class="no-results">
                    <i class="far fa-bookmark"></i>
                    <h3>İzleme listeniz boş</h3>
                    <p>Filmleri listenize ekleyerek daha sonra izlemek üzere kaydedin.</p>
                    <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary" style="margin-top: 16px;">Filmleri Keşfet</a>
                </div>
            <?php else: ?>
                <div class="movies-grid">
                    <?php foreach ($watchlist_movies as $movie): ?>
                        <div class="movie-card">
                            <div class="movie-card-poster">
                                <img src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film') ?>" 
                                     alt="<?= e($movie['title']) ?>" loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film'">
                                <span class="movie-card-year"><?= e($movie['year']) ?></span>
                                <div class="movie-card-poster-overlay">
                                    <a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-play"></i> Detaylar</a>
                                </div>
                            </div>
                            <div class="movie-card-body">
                                <h3 class="movie-card-title"><a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>"><?= e($movie['title']) ?></a></h3>
                                <div class="movie-card-actions">
                                    <button class="btn btn-watchlist btn-sm active" data-movie-id="<?= $movie['id'] ?>">
                                        <i class="fas fa-bookmark"></i> <span class="watchlist-text">Listede</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Yorumlarım -->
        <div class="profile-tab-content" id="profile-comments">
            <?php if (empty($user_comments)): ?>
                <div class="no-results">
                    <i class="far fa-comment-dots"></i>
                    <h3>Henüz yorum yapmadınız</h3>
                    <p>Film detay sayfalarından yorum yapabilirsiniz.</p>
                </div>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($user_comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                            <div class="comment-avatar"><?= mb_strtoupper(mb_substr($user['username'], 0, 1)) ?></div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-username">
                                        <a href="<?= SITE_URL ?>/movie.php?id=<?= $comment['movie_id'] ?>"><?= e($comment['movie_title']) ?></a>
                                    </span>
                                    <span class="comment-date"><?= time_ago($comment['created_at']) ?></span>
                                </div>
                                <p class="comment-text"><?= e($comment['content']) ?></p>
                                <div class="comment-actions">
                                    <button class="comment-delete-btn" onclick="deleteComment(<?= $comment['id'] ?>)">
                                        <i class="fas fa-trash"></i> Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
