<?php
/**
 * admin/images.php - Görsel Yönetimi
 * Film galerisi görselleri ekleme, listeleme, silme
 */

$admin_page_title = 'Görseller';
require_once __DIR__ . '/includes/admin_header.php';

// Görsel silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verify_csrf_token($_GET['token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM movie_images WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['flash_success'] = 'Görsel silindi.';
        header('Location: ' . SITE_URL . '/admin/images.php');
        exit;
    }
}

// Görsel ekleme
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız.';
    } else {
        $movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $image_url = trim($_POST['image_url'] ?? '');
        $caption = trim($_POST['caption'] ?? '');
        
        if (!$movie_id) $errors[] = 'Film seçimi gerekli.';
        if (empty($image_url)) $errors[] = 'Görsel URL gerekli.';
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) $errors[] = 'Geçersiz URL formatı.';
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO movie_images (movie_id, image_url, caption) VALUES (?, ?, ?)");
            $stmt->execute([$movie_id, $image_url, $caption ?: null]);
            $_SESSION['flash_success'] = 'Görsel eklendi!';
            header('Location: ' . SITE_URL . '/admin/images.php');
            exit;
        }
    }
}

$movies = $pdo->query("SELECT id, title FROM movies ORDER BY title")->fetchAll();

$stmt = $pdo->query("
    SELECT i.*, m.title as movie_title 
    FROM movie_images i 
    INNER JOIN movies m ON i.movie_id = m.id 
    ORDER BY i.created_at DESC
");
$images = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1><i class="fas fa-images"></i> Görsel Yönetimi</h1>
</div>

<!-- Görsel Ekleme Formu -->
<div class="admin-form-card" style="margin-bottom: 24px;">
    <h3 style="margin-bottom: 16px;"><i class="fas fa-plus" style="color: var(--admin-accent);"></i> Yeni Görsel Ekle</h3>
    
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
                <label>Açıklama</label>
                <input type="text" name="caption" placeholder="Görsel açıklaması">
            </div>
        </div>
        <div class="admin-form-group">
            <label>Görsel URL *</label>
            <input type="url" name="image_url" placeholder="https://..." required>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Görsel Ekle</button>
    </form>
</div>

<!-- Görsel Listesi -->
<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Önizleme</th><th>Film</th><th>Açıklama</th><th>İşlemler</th></tr>
        </thead>
        <tbody>
            <?php foreach ($images as $img): ?>
                <tr>
                    <td>#<?= $img['id'] ?></td>
                    <td><img src="<?= e($img['image_url']) ?>" alt="" style="width:80px;height:50px;object-fit:cover;border-radius:4px;" 
                             onerror="this.src='https://via.placeholder.com/80x50/1a1a2e/e2b616?text=IMG'"></td>
                    <td><?= e($img['movie_title']) ?></td>
                    <td><?= e($img['caption'] ?? '-') ?></td>
                    <td>
                        <a href="<?= SITE_URL ?>/admin/images.php?delete=<?= $img['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
                           class="admin-btn admin-btn-danger admin-btn-sm delete-confirm">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
