<?php
/**
 * logout.php - Çıkış İşlemi
 * Session sonlandırma ve yönlendirme
 */

require_once __DIR__ . '/config.php';

// Session'ı temizle
$_SESSION = [];
session_destroy();

// Yeni session başlat ve mesaj ekle
session_start();
$_SESSION['flash_success'] = 'Başarıyla çıkış yaptınız.';

header('Location: ' . SITE_URL . '/index.php');
exit;
