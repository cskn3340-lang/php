<?php
/**
 * admin/movie_form.php - Film Ekleme/Düzenleme Formu
 */

$admin_page_title = 'Film Formu';
require_once __DIR__ . '/includes/admin_header.php';

$movie = null;
$editing = false;

// Düzenleme modu
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $movie = $stmt->fetch();
    if ($movie) {
        $editing = true;
        $admin_page_title = 'Film Düzenle: ' . $movie['title'];
    }
}

$errors = [];

// Form gönderimi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $year = (int)($_POST['year'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $director = trim($_POST['director'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $duration = (int)($_POST['duration'] ?? 0);
        $poster_url = trim($_POST['poster_url'] ?? '');
        $cover_url = trim($_POST['cover_url'] ?? '');
        
        // Validasyon
        if (empty($title)) $errors[] = 'Film adı gerekli.';
        if ($year < 1900 || $year > 2030) $errors[] = 'Geçersiz yıl.';
        if (empty($description)) $errors[] = 'Açıklama gerekli.';
        if (!empty($poster_url) && !filter_var($poster_url, FILTER_VALIDATE_URL)) $errors[] = 'Geçersiz afiş URL.';
        if (!empty($cover_url) && !filter_var($cover_url, FILTER_VALIDATE_URL)) $errors[] = 'Geçersiz kapak URL.';
        
        if (empty($errors)) {
            if ($editing) {
                $stmt = $pdo->prepare("
                    UPDATE movies SET title=?, year=?, description=?, director=?, genre=?, duration=?, poster_url=?, cover_url=?, updated_at=NOW() 
                    WHERE id=?
                ");
                $stmt->execute([$title, $year, $description, $director, $genre, $duration ?: null, $poster_url ?: null, $cover_url ?: null, $movie['id']]);
                $_SESSION['flash_success'] = 'Film güncellendi!';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO movies (title, year, description, director, genre, duration, poster_url, cover_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $year, $description, $director, $genre, $duration ?: null, $poster_url ?: null, $cover_url ?: null]);
                $_SESSION['flash_success'] = 'Film eklendi!';
            }
            header('Location: ' . SITE_URL . '/admin/movies.php');
            exit;
        }
    }
}

// Form değerleri
$f = $movie ?? [
    'title' => $_POST['title'] ?? '',
    'year' => $_POST['year'] ?? date('Y'),
    'description' => $_POST['description'] ?? '',
    'director' => $_POST['director'] ?? '',
    'genre' => $_POST['genre'] ?? '',
    'duration' => $_POST['duration'] ?? '',
    'poster_url' => $_POST['poster_url'] ?? '',
    'cover_url' => $_POST['cover_url'] ?? ''
];
?>

<div class="admin-page-header">
    <h1><i class="fas fa-<?= $editing ? 'edit' : 'plus' ?>"></i> <?= $editing ? 'Film Düzenle' : 'Yeni Film Ekle' ?></h1>
    <a href="<?= SITE_URL ?>/admin/movies.php" class="admin-btn admin-btn-secondary"><i class="fas fa-arrow-left"></i> Geri</a>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endforeach; ?>

<div class="admin-form-card">
    <form method="POST">
        <?= csrf_field() ?>
        
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Film Adı *</label>
                <input type="text" name="title" value="<?= e($f['title']) ?>" required>
            </div>
            <div class="admin-form-group">
                <label>Yıl *</label>
                <input type="number" name="year" value="<?= e($f['year']) ?>" min="1900" max="2030" required>
            </div>
        </div>
        
        <div class="admin-form-group">
            <label>Açıklama *</label>
            <textarea name="description" required><?= e($f['description']) ?></textarea>
        </div>
        
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Yönetmen</label>
                <input type="text" name="director" value="<?= e($f['director'] ?? '') ?>">
            </div>
            <div class="admin-form-group">
                <label>Tür</label>
                <input type="text" name="genre" value="<?= e($f['genre'] ?? '') ?>" placeholder="Drama, Komedi">
            </div>
            <div class="admin-form-group">
                <label>Süre (dk)</label>
                <input type="number" name="duration" value="<?= e($f['duration'] ?? '') ?>" min="1">
            </div>
        </div>
        
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Afiş URL</label>
                <input type="url" name="poster_url" value="<?= e($f['poster_url'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="admin-form-group">
                <label>Kapak Görseli URL</label>
                <input type="url" name="cover_url" value="<?= e($f['cover_url'] ?? '') ?>" placeholder="https://...">
            </div>
        </div>
        
        <div style="margin-top: 24px;">
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-save"></i> <?= $editing ? 'Güncelle' : 'Kaydet' ?>
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
