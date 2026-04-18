<?php
/**
 * admin/videos.php - Video Yönetimi
 * YouTube video ekleme, listeleme, silme
 */

$admin_page_title = 'Videolar';
require_once __DIR__ . '/includes/admin_header.php';

// Video silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM movie_videos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['flash_success'] = 'Video silindi.';
        header('Location: ' . SITE_URL . '/admin/videos.php');
        exit;
    }
}

// Video ekleme
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız.';
    } else {
        $movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $youtube_url = trim($_POST['youtube_url'] ?? '');
        $video_type = $_POST['video_type'] ?? 'trailer';
        
        if (!$movie_id) $errors[] = 'Film seçimi gerekli.';
        if (empty($title)) $errors[] = 'Video başlığı gerekli.';
        if (empty($youtube_url)) $errors[] = 'YouTube URL gerekli.';
        if (!filter_var($youtube_url, FILTER_VALIDATE_URL)) $errors[] = 'Geçersiz URL formatı.';
        if (!youtube_url_to_embed($youtube_url)) $errors[] = 'Geçerli bir YouTube linki girin.';
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO movie_videos (movie_id, title, youtube_url, video_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$movie_id, $title, $youtube_url, $video_type]);
            $_SESSION['flash_success'] = 'Video eklendi!';
            header('Location: ' . SITE_URL . '/admin/videos.php');
            exit;
        }
    }
}

// Filmler listesi (dropdown için)
$movies = $pdo->query("SELECT id, title FROM movies ORDER BY title")->fetchAll();

// Tüm videolar
$stmt = $pdo->query("
    SELECT v.*, m.title as movie_title 
    FROM movie_videos v 
    INNER JOIN movies m ON v.movie_id = m.id 
    ORDER BY v.created_at DESC
");
$videos = $stmt->fetchAll();

$type_labels = ['trailer' => 'Fragman', 'sahne' => 'Sahne', 'roportaj' => 'Röportaj', 'ekstra' => 'Ekstra'];
?>

<div class="admin-page-header">
    <h1><i class="fas fa-play-circle"></i> Video Yönetimi</h1>
</div>

<!-- Video Ekleme Formu -->
<div class="admin-form-card" style="margin-bottom: 24px;">
    <h3 style="margin-bottom: 16px;"><i class="fas fa-plus" style="color: var(--admin-accent);"></i> Yeni Video Ekle</h3>
    
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endforeach; ?>
    
    <form method="POST">
        <?= csrf_field() ?>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Film *</label>
                <select name="movie_id" required>
                    <option value="">Film Seçin</option>
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label>Video Türü *</label>
                <select name="video_type" required>
                    <option value="trailer">Fragman</option>
                    <option value="sahne">Sahne</option>
                    <option value="roportaj">Röportaj</option>
                    <option value="ekstra">Ekstra</option>
                </select>
            </div>
        </div>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Video Başlığı *</label>
                <input type="text" name="title" placeholder="ör: Resmi Fragman" required>
            </div>
            <div class="admin-form-group">
                <label>YouTube URL *</label>
                <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
            </div>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Video Ekle</button>
    </form>
</div>

<!-- Video Listesi -->
<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Film</th><th>Başlık</th><th>Tür</th><th>İşlemler</th></tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $v): ?>
                <tr>
                    <td>#<?= $v['id'] ?></td>
                    <td><?= e($v['movie_title']) ?></td>
                    <td><?= e($v['title']) ?></td>
                    <td><span class="badge badge-gold"><?= $type_labels[$v['video_type']] ?? $v['video_type'] ?></span></td>
                    <td>
                        <div class="admin-actions">
                            <a href="<?= e(youtube_url_to_embed($v['youtube_url']) ?: '#') ?>" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/videos.php?delete=<?= $v['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
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
