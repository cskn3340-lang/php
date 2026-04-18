<?php
/**
 * admin/actor_form.php - Oyuncu Ekleme/Düzenleme
 */

$admin_page_title = 'Oyuncu Formu';
require_once __DIR__ . '/includes/admin_header.php';

$actor = null;
$editing = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM actors WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $actor = $stmt->fetch();
    if ($actor) $editing = true;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $photo_url = trim($_POST['photo_url'] ?? '');
        
        if (empty($name)) $errors[] = 'Oyuncu adı gerekli.';
        if (!empty($photo_url) && !filter_var($photo_url, FILTER_VALIDATE_URL)) $errors[] = 'Geçersiz fotoğraf URL.';
        
        if (empty($errors)) {
            if ($editing) {
                $stmt = $pdo->prepare("UPDATE actors SET name=?, bio=?, photo_url=? WHERE id=?");
                $stmt->execute([$name, $bio ?: null, $photo_url ?: null, $actor['id']]);
                $_SESSION['flash_success'] = 'Oyuncu güncellendi!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO actors (name, bio, photo_url) VALUES (?, ?, ?)");
                $stmt->execute([$name, $bio ?: null, $photo_url ?: null]);
                $_SESSION['flash_success'] = 'Oyuncu eklendi!';
            }
            header('Location: ' . SITE_URL . '/admin/actors.php');
            exit;
        }
    }
}

$f = $actor ?? ['name' => $_POST['name'] ?? '', 'bio' => $_POST['bio'] ?? '', 'photo_url' => $_POST['photo_url'] ?? ''];
?>

<div class="admin-page-header">
    <h1><i class="fas fa-<?= $editing ? 'edit' : 'plus' ?>"></i> <?= $editing ? 'Oyuncu Düzenle' : 'Yeni Oyuncu Ekle' ?></h1>
    <a href="<?= SITE_URL ?>/admin/actors.php" class="admin-btn admin-btn-secondary"><i class="fas fa-arrow-left"></i> Geri</a>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
<?php endforeach; ?>

<div class="admin-form-card">
    <form method="POST">
        <?= csrf_field() ?>
        
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label>Oyuncu Adı *</label>
                <input type="text" name="name" value="<?= e($f['name']) ?>" required>
            </div>
            <div class="admin-form-group">
                <label>Fotoğraf URL</label>
                <input type="url" name="photo_url" value="<?= e($f['photo_url'] ?? '') ?>" placeholder="https://...">
            </div>
        </div>
        
        <div class="admin-form-group">
            <label>Biyografi</label>
            <textarea name="bio"><?= e($f['bio'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-save"></i> <?= $editing ? 'Güncelle' : 'Kaydet' ?>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
