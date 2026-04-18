<?php
/**
 * admin/login.php - Admin Giriş Sayfası
 */

require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

if (is_admin()) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            header('Location: ' . SITE_URL . '/admin/index.php');
            exit;
        } else {
            $errors[] = 'Geçersiz admin bilgileri.';
        }
    } else {
        $errors[] = 'Tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <style>
        .admin-login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-bg);
        }
        .admin-login-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 40px;
            width: 400px;
            max-width: 90%;
        }
        .admin-login-card h1 {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 8px;
        }
        .admin-login-card h1 i { color: var(--admin-accent); }
        .admin-login-subtitle {
            text-align: center;
            color: var(--admin-text-muted);
            margin-bottom: 24px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="admin-login-page">
    <div class="admin-login-card">
        <h1><i class="fas fa-shield-alt"></i> Admin Giriş</h1>
        <p class="admin-login-subtitle">Yönetim paneline erişmek için giriş yapın.</p>
        
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
        <?php endforeach; ?>
        
        <form method="POST">
            <div class="admin-form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" placeholder="Admin kullanıcı adı" required autofocus>
            </div>
            <div class="admin-form-group">
                <label>Şifre</label>
                <input type="password" name="password" placeholder="Admin şifresi" required>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; padding: 12px; justify-content: center;">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>
        <div style="text-align: center; margin-top: 16px;">
            <a href="<?= SITE_URL ?>/index.php" style="font-size: 0.85rem; color: var(--admin-text-muted);">
                <i class="fas fa-arrow-left"></i> Siteye Dön
            </a>
        </div>
    </div>
</div>
</body>
</html>
