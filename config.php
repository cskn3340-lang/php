<?php
/**
 * config.php - Site yapÄ±landÄ±rma dosyasÄ±
 * VeritabanÄ± baÄŸlantÄ± bilgileri ve genel site ayarlarÄ±
 */

// Hata raporlama (geliÅŸtirme ortamÄ±)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum baÅŸlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// VERÄ°TABANI AYARLARI
// =====================================================
define('DB_FILE', __DIR__ . '/database.sqlite');

// =====================================================
// SÄ°TE AYARLARI
// =====================================================
define('SITE_NAME', 'TÃ¼rk Filmleri');
define('SITE_DESCRIPTION', 'TÃ¼rk SinemasÄ±nÄ±n En Ä°yi Filmleri');
// Dinamik SITE_URL tanÄ±mÄ± (Render.com ve yerel Ã§alÄ±ÅŸma iÃ§in)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8082';
define('SITE_URL', $protocol . '://' . $host);

// =====================================================
// DOSYA YOLLARI
// =====================================================
define('BASE_PATH', __DIR__);
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');

// =====================================================
// YÃœKLEME AYARLARI
// =====================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// =====================================================
// SAYFALAMA
// =====================================================
define('MOVIES_PER_PAGE', 12);
define('COMMENTS_PER_PAGE', 10);

// =====================================================
// CSRF TOKEN YÃ–NETÄ°MÄ°
// =====================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
