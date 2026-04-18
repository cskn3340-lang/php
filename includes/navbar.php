<?php
/**
 * includes/navbar.php - Navigasyon barı
 * Responsive menü, arama kutusu, kullanıcı menüsü
 */
?>
<!-- Navigasyon -->
<nav class="navbar" id="mainNavbar">
    <div class="container navbar-content">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/index.php" class="navbar-brand">
            <i class="fas fa-film"></i>
            <span>Türk<strong>Filmleri</strong></span>
        </a>
        
        <!-- Arama Kutusu -->
        <div class="navbar-search">
            <form action="<?= SITE_URL ?>/search.php" method="GET" class="search-form" id="searchForm">
                <input type="text" name="q" id="searchInput" placeholder="Film veya oyuncu ara..." autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <!-- Canlı Arama Sonuçları -->
            <div class="search-results-dropdown" id="searchDropdown"></div>
        </div>
        
        <!-- Navigasyon Linkleri -->
        <div class="navbar-links" id="navbarLinks">
            <a href="<?= SITE_URL ?>/index.php" class="nav-link"><i class="fas fa-home"></i> Ana Sayfa</a>
            <a href="<?= SITE_URL ?>/search.php" class="nav-link"><i class="fas fa-compass"></i> Keşfet</a>
            
            <?php if (is_logged_in()): ?>
                <a href="<?= SITE_URL ?>/profile.php" class="nav-link"><i class="fas fa-user"></i> Profilim</a>
                <?php if (is_admin()): ?>
                    <a href="<?= SITE_URL ?>/admin/index.php" class="nav-link nav-admin"><i class="fas fa-cog"></i> Admin</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/logout.php" class="nav-link nav-logout"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="nav-link"><i class="fas fa-sign-in-alt"></i> Giriş</a>
                <a href="<?= SITE_URL ?>/register.php" class="nav-link btn-nav-register"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
            <?php endif; ?>
        </div>
        
        <!-- Mobil Menü Butonu -->
        <button class="navbar-toggle" id="navbarToggle" aria-label="Menüyü aç/kapat">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<!-- Ana İçerik Başlangıcı -->
<main class="main-content">
    <?= show_flash_messages() ?>
