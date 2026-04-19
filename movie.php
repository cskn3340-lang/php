<?php
/**
 * movie.php - Film Detay Sayfası
 * Büyük kapak, bilgiler, oyuncular, sekmeli videolar, galeri, yorumlar, benzer filmler
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// Film ID kontrolü
$movie_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$movie_id) {
    $_SESSION['flash_error'] = 'Geçersiz film ID.';
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Film bilgilerini getir
$stmt = $pdo->prepare("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    WHERE m.id = ? 
    GROUP BY m.id
");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    $_SESSION['flash_error'] = 'Film bulunamadı.';
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = $movie['title'];
$page_description = truncate($movie['description'], 160);

// Oyuncular
$stmt = $pdo->prepare("
    SELECT a.*, ma.role_name 
    FROM actors a 
    INNER JOIN movie_actors ma ON a.id = ma.actor_id 
    WHERE ma.movie_id = ?
");
$stmt->execute([$movie_id]);
$actors = $stmt->fetchAll();

// Videolar (türe göre grupla)
$stmt = $pdo->prepare("SELECT * FROM movie_videos WHERE movie_id = ? ORDER BY video_type, created_at");
$stmt->execute([$movie_id]);
$all_videos = $stmt->fetchAll();

$videos_by_type = [];
foreach ($all_videos as $video) {
    $videos_by_type[$video['video_type']][] = $video;
}

// Galeri görselleri
$stmt = $pdo->prepare("SELECT * FROM movie_images WHERE movie_id = ? ORDER BY created_at");
$stmt->execute([$movie_id]);
$gallery_images = $stmt->fetchAll();

// Yorumlar
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    INNER JOIN users u ON c.user_id = u.id 
    WHERE c.movie_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$movie_id]);
$comments = $stmt->fetchAll();

// Benzer filmler (aynı türden, bu film hariç)
$stmt = $pdo->prepare("
    SELECT m.*, COUNT(l.id) as like_count 
    FROM movies m 
    LEFT JOIN likes l ON m.id = l.movie_id 
    WHERE m.id != ? AND m.genre LIKE ?
    GROUP BY m.id 
    ORDER BY RANDOM() 
    LIMIT 6
");
$stmt->execute([$movie_id, '%' . ($movie['genre'] ? explode(',', $movie['genre'])[0] : 'Drama') . '%']);
$similar_movies = $stmt->fetchAll();

// Kullanıcı durumları
$is_liked = is_logged_in() ? has_user_liked($pdo, $movie_id, $_SESSION['user_id']) : false;
$in_watchlist = is_logged_in() ? is_in_watchlist($pdo, $movie_id, $_SESSION['user_id']) : false;

// İlk video embed URL
// Fragman önceliği: önce trailer, yoksa ilk video
$trailer_videos = $videos_by_type['trailer'] ?? [];
$featured_video = $trailer_videos[0] ?? ($all_videos[0] ?? null);
$featured_embed_url = $featured_video ? youtube_url_to_embed($featured_video['youtube_url']) : null;

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<!-- FİLM HERO -->
<section class="movie-hero" id="movieHero">
    <div class="movie-hero-bg" style="background-image: url('<?= e($movie['cover_url'] ?? $movie['poster_url']) ?>')"></div>
    <div class="movie-hero-overlay"></div>
    <div class="container movie-hero-content">
        <div class="movie-detail-poster">
            <img src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/240x360/1a1a2e/e2b616?text=Film') ?>" 
                 alt="<?= e($movie['title']) ?>"
                 onerror="this.src='https://via.placeholder.com/240x360/1a1a2e/e2b616?text=Film'">
        </div>
        <div class="movie-detail-info">
            <h1><?= e($movie['title']) ?></h1>
            <div class="movie-detail-meta">
                <span><i class="fas fa-calendar"></i> <?= e($movie['year']) ?></span>
                <?php if ($movie['director']): ?>
                    <span><i class="fas fa-video"></i> <?= e($movie['director']) ?></span>
                <?php endif; ?>
                <?php if ($movie['duration']): ?>
                    <span><i class="fas fa-clock"></i> <?= $movie['duration'] ?> dakika</span>
                <?php endif; ?>
                <?php if ($movie['genre']): ?>
                    <span><i class="fas fa-tag"></i> <?= e($movie['genre']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-heart" style="color: var(--accent-red)"></i> <span id="heroLikeCount"><?= $movie['like_count'] ?></span> Beğeni</span>
            </div>
            <p class="movie-detail-description"><?= e($movie['description']) ?></p>
            <div class="movie-detail-actions">
                <?php if ($featured_embed_url): ?>
                    <a href="#videoSection" class="btn btn-primary btn-lg"><i class="fas fa-play"></i> Fragmanı İzle</a>
                <?php endif; ?>
                <button class="btn btn-like btn-lg <?= $is_liked ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
                    <i class="<?= $is_liked ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="like-count"><?= $movie['like_count'] ?></span> Beğeni
                </button>
                <button class="btn btn-watchlist btn-lg <?= $in_watchlist ? 'active' : '' ?>" data-movie-id="<?= $movie['id'] ?>">
                    <i class="<?= $in_watchlist ? 'fas' : 'far' ?> fa-bookmark"></i>
                    <span class="watchlist-text"><?= $in_watchlist ? 'Listede' : 'Listeme Ekle' ?></span>
                </button>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- OYUNCULAR -->
    <?php if (!empty($actors)): ?>
    <section class="section" id="actorsSection">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-users"></i> Oyuncular</h2>
        </div>
        <div class="actors-grid">
            <?php foreach ($actors as $actor): ?>
                <div class="actor-card">
                    <div class="actor-card-photo">
                        <img src="<?= e($actor['photo_url'] ?: 'https://via.placeholder.com/100x100/1a1a2e/e2b616?text=' . urlencode(mb_substr($actor['name'], 0, 1))) ?>" 
                             alt="<?= e($actor['name']) ?>"
                             onerror="this.src='https://via.placeholder.com/100x100/1a1a2e/e2b616?text=A'">
                    </div>
                    <div class="actor-card-name"><?= e($actor['name']) ?></div>
                    <?php if ($actor['role_name']): ?>
                        <div class="actor-card-role"><?= e($actor['role_name']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- VİDEO BÖLÜMÜ -->
    <?php if (!empty($all_videos)): ?>
    <section class="section" id="videoSection">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-play-circle"></i> Videolar</h2>
        </div>
        <div class="video-section">
            <!-- Video Sekmeleri -->
            <div class="video-tabs">
                <?php 
                $tab_types = ['trailer' => 'Fragman', 'sahne' => 'Sahneler', 'roportaj' => 'Röportaj', 'ekstra' => 'Ek Videolar'];
                $first_tab = true;
                foreach ($tab_types as $type => $label): 
                    if (isset($videos_by_type[$type])): 
                ?>
                    <button class="video-tab <?= $first_tab ? 'active' : '' ?>" data-tab="<?= $type ?>">
                        <i class="fas fa-<?= $type === 'trailer' ? 'film' : ($type === 'sahne' ? 'clapperboard' : ($type === 'roportaj' ? 'microphone' : 'plus-circle')) ?>"></i>
                        <?= $label ?> (<?= count($videos_by_type[$type]) ?>)
                    </button>
                <?php 
                        $first_tab = false;
                    endif; 
                endforeach; 
                ?>
            </div>
            
            <!-- Video İçerikleri -->
            <?php 
            $first_content = true;
            foreach ($tab_types as $type => $label): 
                if (isset($videos_by_type[$type])): 
            ?>
                <div class="video-tab-content <?= $first_content ? 'active' : '' ?>" id="tab-<?= $type ?>">
                    <!-- Ana Video Oynatıcı -->
                    <?php 
                    $current_video = $videos_by_type[$type][0];
                    $embed_url = youtube_url_to_embed($current_video['youtube_url']);
                    ?>
                    <?php if ($embed_url): ?>
                        <div class="video-player-wrapper">
                            <iframe id="mainVideoPlayer" 
                                    src="<?= e($embed_url) ?>?enablejsapi=1" 
                                    title="<?= e($current_video['title']) ?>"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen
                                    loading="lazy"></iframe>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Video Listesi -->
                    <?php if (count($videos_by_type[$type]) > 1): ?>
                        <div class="video-list">
                            <?php foreach ($videos_by_type[$type] as $video): ?>
                                <?php $vid_embed = youtube_url_to_embed($video['youtube_url']); ?>
                                <?php if ($vid_embed): ?>
                                    <div class="video-item" data-embed-url="<?= e($vid_embed) ?>">
                                        <div class="video-item-thumbnail">
                                            <img src="<?= e(get_youtube_thumbnail($video['youtube_url'])) ?>" 
                                                 alt="<?= e($video['title']) ?>"
                                                 onerror="this.src='https://via.placeholder.com/320x180/1a1a2e/e2b616?text=Video'">
                                            <div class="play-icon"><i class="fas fa-play-circle"></i></div>
                                        </div>
                                        <div class="video-item-info">
                                            <div class="video-item-title"><?= e($video['title']) ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php 
                $first_content = false;
                endif; 
            endforeach; 
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- GALERİ -->
    <?php if (!empty($gallery_images)): ?>
    <section class="section" id="gallerySection">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-images"></i> Galeri</h2>
        </div>
        <div class="gallery-grid">
            <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item">
                    <img src="<?= e($image['image_url']) ?>" 
                         alt="<?= e($image['caption'] ?? $movie['title']) ?>"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- YORUMLAR -->
    <section class="section" id="commentsSection">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-comments"></i> Yorumlar 
                <span style="font-size: 0.6em; color: var(--text-muted);">(<span id="commentCount"><?= count($comments) ?></span>)</span>
            </h2>
        </div>
        
        <div class="comments-section">
            <!-- Yorum Formu -->
            <?php if (is_logged_in()): ?>
                <form class="comment-form" id="commentForm">
                    <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                    <textarea name="content" placeholder="Filmi hakkında ne düşünüyorsunuz? Yorumunuzu yazın..." maxlength="1000"></textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Yorum Gönder
                    </button>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 20px; margin-bottom: 24px; background: var(--bg-card); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <p style="color: var(--text-muted); margin-bottom: 12px;">Yorum yapmak için giriş yapmalısınız.</p>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
                </div>
            <?php endif; ?>
            
            <!-- Yorum Listesi -->
            <div class="comments-list" id="commentsList">
                <?php if (empty($comments)): ?>
                    <div class="no-comments" style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <i class="far fa-comment-dots" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        <p>Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                            <div class="comment-avatar"><?= mb_strtoupper(mb_substr($comment['username'], 0, 1)) ?></div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-username"><?= e($comment['username']) ?></span>
                                    <span class="comment-date"><?= time_ago($comment['created_at']) ?></span>
                                </div>
                                <p class="comment-text"><?= e($comment['content']) ?></p>
                                <?php if (is_logged_in() && ($comment['user_id'] == $_SESSION['user_id'] || is_admin())): ?>
                                    <div class="comment-actions">
                                        <button class="comment-delete-btn" onclick="deleteComment(<?= $comment['id'] ?>)">
                                            <i class="fas fa-trash"></i> Sil
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- BENZER FİLMLER -->
    <?php if (!empty($similar_movies)): ?>
    <section class="section" id="similarMovies">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-th-large"></i> Benzer Filmler</h2>
        </div>
        <div class="similar-movies-scroll">
            <?php foreach ($similar_movies as $sim_movie): ?>
                <div class="movie-card">
                    <div class="movie-card-poster">
                        <img src="<?= e($sim_movie['poster_url'] ?: 'https://via.placeholder.com/200x300/1a1a2e/e2b616?text=Film') ?>" 
                             alt="<?= e($sim_movie['title']) ?>" loading="lazy"
                             onerror="this.src='https://via.placeholder.com/200x300/1a1a2e/e2b616?text=Film'">
                        <span class="movie-card-year"><?= e($sim_movie['year']) ?></span>
                        <div class="movie-card-poster-overlay">
                            <a href="<?= SITE_URL ?>/movie.php?id=<?= $sim_movie['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> Detaylar
                            </a>
                        </div>
                    </div>
                    <div class="movie-card-body">
                        <h3 class="movie-card-title">
                            <a href="<?= SITE_URL ?>/movie.php?id=<?= $sim_movie['id'] ?>"><?= e($sim_movie['title']) ?></a>
                        </h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="Galeri Görseli">
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
