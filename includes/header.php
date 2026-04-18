<?php
/**
 * includes/header.php - Ortak HTML başlığı
 * Tüm sayfalarda kullanılan meta taglar, CSS bağlantıları
 */

// $page_title değişkeni sayfadan gelir
$page_title = isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME . ' - ' . SITE_DESCRIPTION;
$page_description = isset($page_description) ? $page_description : 'Türk sinemasının en iyi filmlerini keşfedin. Fragmanları izleyin, film listenizi oluşturun.';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($page_description) ?>">
    <meta name="author" content="Türk Filmleri Platformu">
    <title><?= e($page_title) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Ana CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
