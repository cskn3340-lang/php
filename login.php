<?php
/**
 * login.php - Giriş Sayfası
 * Kullanıcı girişi, session yönetimi
 */

require_once __DIR__ . '/config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$page_title = 'Giriş Yap';
$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username)) {
            $errors[] = 'Kullanıcı adı gereklidir.';
        }
        if (empty($password)) {
            $errors[] = 'Şifre gereklidir.';
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // CSRF token yenile
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                $_SESSION['flash_success'] = 'Hoş geldiniz, ' . $user['username'] . '!';
                header('Location: ' . SITE_URL . '/index.php');
                exit;
            } else {
                $errors[] = 'Kullanıcı adı veya şifre hatalı.';
            }
        }
    }
}

require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/navbar.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h1><i class="fas fa-sign-in-alt" style="color: var(--accent-gold)"></i> Giriş Yap</h1>
        <p class="auth-subtitle">Hesabınıza giriş yaparak filmleri beğenin, listenize ekleyin ve yorum yazın.</p>

        <div class="auth-mini-profiles" aria-hidden="true">
            <div class="auth-mini-profile">
                <img src="<?= e(get_user_avatar_url('Sinemacı')) ?>" alt="" loading="lazy">
                <span>Sinemacı</span>
            </div>
            <div class="auth-mini-profile">
                <img src="<?= e(get_user_avatar_url('Filmsever')) ?>" alt="" loading="lazy">
                <span>Filmsever</span>
            </div>
            <div class="auth-mini-profile">
                <img src="<?= e(get_user_avatar_url('Fragman')) ?>" alt="" loading="lazy">
                <span>Fragman</span>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= e(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Kullanıcı Adı veya E-posta</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" 
                       placeholder="Kullanıcı adınızı girin" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Şifre</label>
                <input type="password" id="password" name="password" placeholder="Şifrenizi girin" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>
        
        <div class="auth-footer">
            Hesabınız yok mu? <a href="<?= SITE_URL ?>/register.php">Kayıt Ol</a>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
