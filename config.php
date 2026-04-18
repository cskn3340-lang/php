<?php
/**
 * config.php - Site yapılandırma dosyası
 * Veritabanı bağlantı bilgileri ve genel site ayarları
 */

// Hata raporlama (geliştirme ortamı)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// VERİTABANI AYARLARI
// Ortam değişkenleri varsa onları kullan (Render.com)
// Yoksa yerel geliştirme varsayılanlarını kullan
// =====================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'turk_filmleri');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// SİTE AYARLARI
// =====================================================
define('SITE_NAME', 'Türk Filmleri');
define('SITE_DESCRIPTION', 'Türk Sinemasının En İyi Filmleri');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/elif-repo');

// =====================================================
// DOSYA YOLLARI
// =====================================================
define('BASE_PATH', __DIR__);
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');

// =====================================================
// YÜKLEME AYARLARI
// =====================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// =====================================================
// SAYFALAMA
// =====================================================
define('MOVIES_PER_PAGE', 12);
define('COMMENTS_PER_PAGE', 10);

// =====================================================
// CSRF TOKEN YÖNETİMİ
// =====================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
