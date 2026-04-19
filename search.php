<?php
/**
 * search.php - Arama ve Filtreleme Sayfası
 * Film adı, yıl, tür ve oyuncuya göre arama
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

$page_title = 'Film Ara';
$query = trim($_GET['q'] ?? '');
$year_filter = $_GET['year'] ?? '';
$genre_filter = $_GET['genre'] ?? '';
$results = [];

// Sorgu oluştur
$sql = "SELECT m.*, COUNT(l.id) as like_count FROM movies m LEFT JOIN likes l ON m.id = l.movie_id WHERE 1=1";
$params = [];

if (!empty($query)) {
    $sql .= " AND (m.title LIKE ? OR m.description LIKE ? OR m.director LIKE ?)";
    $search_term = '%' . $query . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $page_title = '"' . $query . '" için arama sonuçları';
}

if (!empty($year_filter) && is_numeric($year_filter)) {
    $sql .= " AND m.year = ?";
    $params[] = $year_filter;
}

if (!empty($genre_filter)) {
    $sql .= " AND m.genre LIKE ?";
    $params[] = '%' . $genre_filter . '%';
}

$sql .= " GROUP BY m.id ORDER BY m.title ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Oyuncuya göre arama (ek sonuçlar)
$actor_results = [];
if (!empty($query)) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*, COUNT(l.id) as like_count 
        FROM movies m 
        INNER JOIN movie_actors ma ON m.id = ma.movie_id 
        INNER JOIN actors a ON ma.actor_id = a.id 
        LEFT JOIN likes l ON m.id = l.movie_id 
        WHERE a.name LIKE ? 
        GROUP BY m.id
    ");
    $stmt->execute(['%' . $query . '%']);
    $actor_results = $stmt->fetchAll();
    
    // Mevcut sonuçlarla birleştir (tekrarları kaldır)
    $existing_ids = array_column($results, 'id');
    foreach ($actor_results as $ar) {
        if (!in_array($ar['id'], $existing_ids)) {
            $results[] = $ar;
        }
    }
}

// Filtreleme için yıllar ve türler
$years = $pdo->query("SELECT DISTINCT year FROM movies ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
$genres_raw = $pdo->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$genres = [];
foreach ($genres_raw as $g) {
    foreach (explode(',', $g) as $genre) {
        $genre = trim($genre);
        if ($genre && !in_array($genre, $genres)) {
            $genres[] = $genre;
        }
    }
}
sort($genres);

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="container">
    <!-- Arama Header -->
    <div class="search-page-header">
        <h1><i class="fas fa-search" style="color: var(--accent-gold)"></i> Film Keşfet</h1>
        <div class="search-page-form">
            <form action="" method="GET" class="search-form">
                <input type="text" name="q" value="<?= e($query) ?>" placeholder="Film adı, yönetmen veya oyuncu ara...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <!-- Filtreler -->
        <div class="search-filters">
            <form action="" method="GET" id="filterForm" style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <input type="hidden" name="q" value="<?= e($query) ?>">
                <select name="year" onchange="this.form.submit()">
                    <option value="">Tüm Yıllar</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $year_filter == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="genre" onchange="this.form.submit()">
                    <option value="">Tüm Türler</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= e($g) ?>" <?= $genre_filter === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Sonuçlar -->
    <section class="section" style="padding-top: 0;">
        <?php if (!empty($query) || !empty($year_filter) || !empty($genre_filter)): ?>
            <div class="search-results-info">
                <strong><?= count($results) ?></strong> sonuç bulundu
                <?php if (!empty($query)): ?> - "<?= e($query) ?>" için<?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($results) && (!empty($query) || !empty($year_filter) || !empty($genre_filter))): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Sonuç bulunamadı</h3>
                <p>Farklı anahtar kelimeler veya filtreler deneyin.</p>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($results as $movie): ?>
                    <?php
                    $like_count = $movie['like_count'] ?? 0;
                    $is_liked = is_logged_in() ? has_user_liked($pdo, $movie['id'], $_SESSION['user_id']) : false;
                    $in_watchlist = is_logged_in() ? is_in_watchlist($pdo, $movie['id'], $_SESSION['user_id']) : false;
                    $trailer_url = get_movie_trailer_url($pdo, $movie['id']);
                    // Prefer cover_url over poster_url if available
                    $poster = (!empty($movie['cover_url']) ? $movie['cover_url'] : ($movie['poster_url'] ?: 'https://via.placeholder.com/300x450/1a1a2e/e2b616?text=' . urlencode($movie['title'])));
                    ?>
                    <div class="movie-card">
                        <div class="movie-card-poster">
                            <img src="<?= e($poster) ?>" alt="<?= e($movie['title']) ?>" loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/300x450/1a1a2e/e2b616?text=Film'">
                            <span class="movie-card-year"><?= e($movie['year']) ?></span>
                            <div class="movie-card-poster-overlay">
                                <a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-play"></i> Detaylar</a>
                                <?php if ($trailer_url): ?>
                                    <a href="<?= e($trailer_url) ?>" class="btn btn-secondary btn-sm trailer-btn" target="_blank" rel="noopener"><i class="fas fa-clapperboard"></i> Fragman</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="movie-card-body">
                            <h3 class="movie-card-title"><a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>"><?= e($movie['title']) ?></a></h3>
                            <p class="movie-card-desc"><?= e(truncate($movie['description'], 80)) ?></p>
                            <div class="movie-card-meta">
                                <?php if ($movie['director']): ?>
                                    <span><i class="fas fa-video"></i> <?= e($movie['director']) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-heart"></i> <?= $like_count ?></span>
                            </div>
                            <div class="movie-card-actions">
                                <button class="btn btn-like btn-sm <?= $is_liked ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
                                    <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i> <span class="like-count"><?= $like_count ?></span>
                                </button>
                                <button class="btn btn-watchlist btn-sm <?= $in_watchlist ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
                                    <i class="<?= $in_watchlist ? 'fas' : 'far' ?> fa-bookmark"></i>
                                </button>
                                <?php if ($trailer_url): ?>
                                    <a href="<?= e($trailer_url) ?>" class="btn btn-secondary btn-sm trailer-btn" target="_blank" rel="noopener"><i class="fas fa-clapperboard"></i></a>
                                <?php endif; ?>
                                <a href="<?= SITE_URL ?>/movie.php?id=<?= $movie['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-info-circle"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
