<?php
/**
 * includes/db.php - PDO Veritabanı Bağlantısı
 * Tüm veritabanı işlemlerinde bu dosya kullanılır.
 * PDO prepared statements ile güvenli sorgulama sağlanır.
 */

require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Üretim ortamında detaylı hata gösterme
    error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h1>⚠️ Veritabanı Bağlantı Hatası</h1>
        <p>Lütfen veritabanı ayarlarınızı kontrol edin.</p>
        <p style="color:#999;font-size:0.9em;">config.php dosyasındaki DB_HOST, DB_NAME, DB_USER ve DB_PASS değerlerini kontrol edin.</p>
    </div>');
}
