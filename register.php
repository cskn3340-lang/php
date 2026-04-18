<?php
/**
 * register.php - Kayıt Sayfası
 * Yeni kullanıcı kaydı, validasyon, şifre hashleme
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Kayıt Ol';
$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validasyon
        if (empty($username) || mb_strlen($username) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalı.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }
        if (mb_strlen($password) < 6) {
            $errors[] = 'Şifre en az 6 karakter olmalı.';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Şifreler eşleşmiyor.';
        }
        
        // Kullanıcı adı ve e-posta benzersizliği
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
            }
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
            }
        }
        
        // Kayıt işlemi
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Otomatik giriş
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = 'user';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            $_SESSION['flash_success'] = 'Kayıt başarılı! Hoş geldiniz, ' . $username . '!';
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    }
}

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h1><i class="fas fa-user-plus" style="color: var(--accent-gold)"></i> Kayıt Ol</h1>
        <p class="auth-subtitle">Ücretsiz hesap oluşturun ve Türk sinemasını keşfedin.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Kullanıcı Adı</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" 
                       placeholder="Kullanıcı adınızı seçin" required autofocus minlength="3">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-posta Adresi</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" 
                       placeholder="ornek@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Şifre</label>
                <input type="password" id="password" name="password" placeholder="En az 6 karakter" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="password_confirm"><i class="fas fa-lock"></i> Şifre Tekrar</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Şifrenizi tekrarlayın" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Kayıt Ol
            </button>
        </form>
        
        <div class="auth-footer">
            Zaten hesabınız var mı? <a href="<?= SITE_URL ?>/login.php">Giriş Yap</a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
