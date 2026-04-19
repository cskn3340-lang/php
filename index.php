<?php
/**
 * index.php - Ana Sayfa
 * Öne çıkan film, son eklenen filmler, en çok beğenilenler, kullanıcı listesi
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

$page_title = 'Ana Sayfa';

// Öne çıkan film (en çok beğenilen)
$stmt = $pdo->query("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY like_count DESC 
    LIMIT 1
");
$featured_movie = $stmt->fetch();

// Son eklenen filmler
$stmt = $pdo->query("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY m.created_at DESC 
    LIMIT 10
");
$latest_movies = $stmt->fetchAll();

// En çok beğenilen filmler
$stmt = $pdo->query("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY like_count DESC 
    LIMIT 10
");
$popular_movies = $stmt->fetchAll();

// Kullanıcının izleme listesi
$user_watchlist = [];
if (is_logged_in()) {
    $stmt = $pdo->prepare("
        SELECT m.*, COUNT(l.id) as like_count 
        FROM movies m 
        INNER JOIN watchlist w ON m.id = w.movie_id AND w.user_id = ?
        LEFT JOIN likes l ON m.id = l.movie_id 
        GROUP BY m.id 
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_watchlist = $stmt->fetchAll();
}

// Tüm filmler
$stmt = $pdo->query("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    GROUP BY m.id 
    ORDER BY m.title ASC
");
$all_movies = $stmt->fetchAll();

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<!-- HERO SECTION - Öne Çıkan Film -->
<?php if ($featured_movie): ?>
<section class="hero" id="heroSection">
    <div class="hero-bg" style="background-image: url('<?= e($featured_movie['cover_url'] ?? $featured_movie['poster_url']) ?>')"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <span class="hero-badge"><i class="fas fa-star"></i> Öne Çıkan Film</span>
        <h1><?= e($featured_movie['title']) ?></h1>
        <div class="hero-meta">
            <span><i class="fas fa-calendar"></i> <?= e($featured_movie['year']) ?></span>
            <?php if ($featured_movie['director']): ?>
                <span><i class="fas fa-video"></i> <?= e($featured_movie['director']) ?></span>
            <?php endif; ?>
            <?php if ($featured_movie['duration']): ?>
                <span><i class="fas fa-clock"></i> <?= $featured_movie['duration'] ?> dk</span>
            <?php endif; ?>
            <span><i class="fas fa-heart"></i> <?= $featured_movie['like_count'] ?> Beğeni</span>
        </div>
        <p><?= e(truncate($featured_movie['description'], 200)) ?></p>
        <div class="hero-actions">
            <a href="<?= SITE_URL ?>/movie.php?id=<?= $featured_movie['id'] ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-play"></i> Detayları Gör
            </a>
            <?php if (is_logged_in()): ?>
                <?php $is_liked = has_user_liked($pdo, $featured_movie['id'], $_SESSION['user_id']); ?>
                <button class="btn btn-like btn-lg <?= $is_liked ? 'active' : '' ?>" data-movie-id="<?= $featured_movie['id'] ?>">
                    <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="like-count"><?= $featured_movie['like_count'] ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- SON EKLENEN FİLMLER -->
<section class="section" id="latestMovies">
    <div class="container">
        <div class="section-header animate-on-scroll">
            <h2 class="section-title"><i class="fas fa-clock"></i> Son Eklenen Filmler</h2>
            <a href="<?= SITE_URL ?>/search.php" class="section-link">Tümünü Gör <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="movies-grid animate-on-scroll">
            <?php foreach ($latest_movies as $movie): ?>
                <?php echo render_movie_card($pdo, $movie); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- EN ÇOK BEĞENİLEN FİLMLER -->
<section class="section" id="popularMovies" style="background: var(--bg-secondary);">
    <div class="container">
        <div class="section-header animate-on-scroll">
            <h2 class="section-title"><i class="fas fa-fire"></i> En Çok Beğenilen</h2>
        </div>
        <div class="movies-grid animate-on-scroll">
            <?php foreach ($popular_movies as $movie): ?>
                <?php echo render_movie_card($pdo, $movie); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- KULLANICININ LİSTESİ -->
<?php if (is_logged_in() && !empty($user_watchlist)): ?>
<section class="section" id="myWatchlist">
    <div class="container">
        <div class="section-header animate-on-scroll">
            <h2 class="section-title"><i class="fas fa-bookmark"></i> İzleme Listem</h2>
            <a href="<?= SITE_URL ?>/profile.php" class="section-link">Tümünü Gör <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="movies-grid animate-on-scroll">
            <?php foreach ($user_watchlist as $movie): ?>
                <?php echo render_movie_card($pdo, $movie); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TÜM FİLMLER -->
<section class="section" id="allMovies">
    <div class="container">
        <div class="section-header animate-on-scroll">
            <h2 class="section-title"><i class="fas fa-film"></i> Tüm Filmler</h2>
        </div>
        <div class="movies-grid animate-on-scroll">
            <?php foreach ($all_movies as $movie): ?>
                <?php echo render_movie_card($pdo, $movie); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
/**
 * Film kartı render fonksiyonu
 */
function render_movie_card(PDO $pdo, array $movie): string
{
    $like_count = $movie['like_count'] ?? get_like_count($pdo, $movie['id']);
    $is_liked = is_logged_in() ? has_user_liked($pdo, $movie['id'], $_SESSION['user_id']) : false;
    $in_watchlist = is_logged_in() ? is_in_watchlist($pdo, $movie['id'], $_SESSION['user_id']) : false;
    $trailer_url = get_movie_trailer_url($pdo, $movie['id']);
    // Prefer cover_url over poster_url for cards if cover is set
    $poster = (!empty($movie['cover_url']) ? $movie['cover_url'] : ($movie['poster_url'] ?: 'https://via.placeholder.com/300x450/1a1a2e/e2b616?text=' . urlencode($movie['title'])));
    
    $html = '<div class="movie-card">';
    $html .= '<div class="movie-card-poster">';
    $html .= '<img src="' . e($poster) . '" alt="' . e($movie['title']) . '" loading="lazy" onerror="this.src=\'https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film\'">';
    $html .= '<span class="movie-card-year">' . e($movie['year']) . '</span>';
    $html .= '<div class="movie-card-poster-overlay">';
    $html .= '<a href="' . SITE_URL . '/movie.php?id=' . $movie['id'] . '" class="btn btn-primary btn-sm"><i class="fas fa-play"></i> Detaylar</a>';
    if ($trailer_url) {
        $html .= '<a href="' . e($trailer_url) . '" class="btn btn-secondary btn-sm trailer-btn" target="_blank" rel="noopener"><i class="fas fa-clapperboard"></i> Fragman</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="movie-card-body">';
    $html .= '<h3 class="movie-card-title"><a href="' . SITE_URL . '/movie.php?id=' . $movie['id'] . '">' . e($movie['title']) . '</a></h3>';
    $html .= '<p class="movie-card-desc">' . e(truncate($movie['description'], 80)) . '</p>';
    
    $html .= '<div class="movie-card-meta">';
    if (!empty($movie['director'])) {
        $html .= '<span><i class="fas fa-video"></i> ' . e($movie['director']) . '</span>';
    }
    $html .= '<span><i class="fas fa-heart"></i> ' . $like_count . '</span>';
    $html .= '</div>';
    
    $html .= '<div class="movie-card-actions">';
    $html .= '<button class="btn btn-like btn-sm ' . ($is_liked ? 'active' : '') . '" data-movie-id="' . $movie['id'] . '">';
    $html .= '<i class="' . ($is_liked ? 'fas' : 'far') . ' fa-heart"></i> <span class="like-count">' . $like_count . '</span>';
    $html .= '</button>';
    $html .= '<button class="btn btn-watchlist btn-sm ' . ($in_watchlist ? 'active' : '') . '" data-movie-id="' . $movie['id'] . '">';
    $html .= '<i class="' . ($in_watchlist ? 'fas' : 'far') . ' fa-bookmark"></i>';
    $html .= '</button>';
    if ($trailer_url) {
        $html .= '<a href="' . e($trailer_url) . '" class="btn btn-secondary btn-sm trailer-btn" target="_blank" rel="noopener"><i class="fas fa-clapperboard"></i></a>';
    }
    $html .= '<a href="' . SITE_URL . '/movie.php?id=' . $movie['id'] . '" class="btn btn-secondary btn-sm"><i class="fas fa-info-circle"></i></a>';
    $html .= '</div>';
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

require_once INCLUDES_PATH . '/footer.php';
?>
