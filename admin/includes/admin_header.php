<?php
/**
 * admin/includes/admin_header.php - Admin Panel Header
 */
require_once __DIR__ . '/../../config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// Admin sayfasında login sayfası hariç admin yetkisi kontrol et
$current_file = basename($_SERVER['SCRIPT_FILENAME']);
if ($current_file !== 'login.php') {
    require_admin();
}

$admin_page_title = isset($admin_page_title) ? $admin_page_title . ' | Admin - ' . SITE_NAME : 'Admin Panel - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($admin_page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="<?= SITE_URL ?>/admin/index.php" class="admin-logo">
            <i class="fas fa-film"></i>
            <span>Admin Panel</span>
        </a>
    </div>
    <nav class="admin-nav">
        <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-link <?= $current_file === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/movies.php" class="admin-nav-link <?= ($current_file === 'movies.php' || $current_file === 'movie_form.php') ? 'active' : '' ?>">
            <i class="fas fa-film"></i> Filmler
        </a>
        <a href="<?= SITE_URL ?>/admin/actors.php" class="admin-nav-link <?= ($current_file === 'actors.php' || $current_file === 'actor_form.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Oyuncular
        </a>
        <a href="<?= SITE_URL ?>/admin/videos.php" class="admin-nav-link <?= $current_file === 'videos.php' ? 'active' : '' ?>">
            <i class="fas fa-play-circle"></i> Videolar
        </a>
        <a href="<?= SITE_URL ?>/admin/images.php" class="admin-nav-link <?= $current_file === 'images.php' ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Görseller
        </a>
        <a href="<?= SITE_URL ?>/admin/comments.php" class="admin-nav-link <?= $current_file === 'comments.php' ? 'active' : '' ?>">
            <i class="fas fa-comments"></i> Yorumlar
        </a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 12px 16px;">
        <a href="<?= SITE_URL ?>/index.php" class="admin-nav-link">
            <i class="fas fa-external-link-alt"></i> Siteyi Gör
        </a>
        <a href="<?= SITE_URL ?>/logout.php" class="admin-nav-link" style="color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i> Çıkış
        </a>
    </nav>
</aside>

<!-- Admin Main Content -->
<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="admin-topbar-right">
            <span class="admin-user"><i class="fas fa-user-shield"></i> <?= e($_SESSION['username'] ?? 'Admin') ?></span>
        </div>
    </header>
    <div class="admin-content">
        <?= show_flash_messages() ?>
