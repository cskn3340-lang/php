<?php
/**
 * includes/footer.php - Ortak HTML alt kısmı
 * Footer alanı ve JavaScript dosyaları
 */
?>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h3><i class="fas fa-film"></i> Türk<strong>Filmleri</strong></h3>
                <p>Türk sinemasının en iyi filmlerini keşfedin, fragmanları izleyin, listenizi oluşturun ve yorumlarınızı paylaşın.</p>
            </div>
            <div class="footer-section">
                <h4>Hızlı Erişim</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Ana Sayfa</a></li>
                    <li><a href="<?= SITE_URL ?>/search.php">Film Keşfet</a></li>
                    <?php if (!is_logged_in()): ?>
                        <li><a href="<?= SITE_URL ?>/login.php">Giriş Yap</a></li>
                        <li><a href="<?= SITE_URL ?>/register.php">Kayıt Ol</a></li>
                    <?php else: ?>
                        <li><a href="<?= SITE_URL ?>/profile.php">Profilim</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Kategoriler</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/search.php?genre=Drama">Drama</a></li>
                    <li><a href="<?= SITE_URL ?>/search.php?genre=Komedi">Komedi</a></li>
                    <li><a href="<?= SITE_URL ?>/search.php?genre=Savaş">Savaş</a></li>
                    <li><a href="<?= SITE_URL ?>/search.php?genre=Suç">Suç</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Bize Ulaşın</h4>
                <div class="footer-social">
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Türk Filmleri Platformu. Tüm hakları saklıdır.</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
    // Global ayarlar
    const SITE_URL = '<?= SITE_URL ?>';
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    const IS_LOGGED_IN = <?= is_logged_in() ? 'true' : 'false' ?>;
</script>
</body>
</html>
