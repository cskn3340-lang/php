<?php
/**
 * includes/functions.php - Yardımcı Fonksiyonlar
 * YouTube URL dönüştürücü, güvenlik fonksiyonları,
 * oturum kontrolleri ve genel yardımcı araçlar
 */

/**
 * YouTube URL'sini embed formatına çevirir
 * watch?v=, youtu.be/ ve mevcut embed formatlarını destekler
 * 
 * @param string $url YouTube video URL'si
 * @return string|false Embed URL veya hatalıysa false
 */
function youtube_url_to_embed(string $url): string|false
{
    $url = trim($url);
    
    // URL doğrulama
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    $video_id = '';
    
    // Format 1: https://www.youtube.com/watch?v=VIDEO_ID
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Format 2: https://youtu.be/VIDEO_ID
    elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Format 3: Zaten embed formatında
    elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Format 4: youtube.com/v/VIDEO_ID
    elseif (preg_match('/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (empty($video_id)) {
        return false;
    }
    
    return "https://www.youtube.com/embed/{$video_id}";
}

/**
 * YouTube video ID'sini URL'den çıkarır
 */
function get_youtube_video_id(string $url): string|false
{
    $url = trim($url);
    
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        return $matches[1];
    }
    
    return false;
}

/**
 * YouTube thumbnail URL'si oluşturur
 */
function get_youtube_thumbnail(string $url): string
{
    $video_id = get_youtube_video_id($url);
    if ($video_id) {
        return "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
    }
    return '';
}

/**
 * Kullanıcı için otomatik avatar URL'si üretir
 */
function get_user_avatar_url(string $username, string $role = 'user'): string
{
    $seed = rawurlencode(trim($username) . '-' . $role);
    return "https://api.dicebear.com/7.x/bottts-neutral/svg?seed={$seed}&backgroundColor=e2b616";
}

/**
 * Bir filmin ilk fragman URL'sini getirir
 */
function get_movie_trailer_url(PDO $pdo, int $movie_id): string|false
{
    $stmt = $pdo->prepare("SELECT youtube_url FROM movie_videos WHERE movie_id = ? AND video_type = 'trailer' ORDER BY created_at ASC LIMIT 1");
    $stmt->execute([$movie_id]);
    $youtube_url = $stmt->fetchColumn();

    if (!$youtube_url) {
        return false;
    }

    return youtube_url_to_embed($youtube_url);
}

/**
 * URL doğrulama
 */
function validate_url(string $url): bool
{
    return filter_var(trim($url), FILTER_VALIDATE_URL) !== false;
}

/**
 * XSS koruması - HTML özel karakterlerini dönüştürür
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF token kontrolü
 */
function verify_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF token form alanı oluşturur
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

/**
 * Kullanıcı giriş yapmış mı kontrol eder
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kullanıcı admin mi kontrol eder
 */
function is_admin(): bool
{
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Giriş gerektiren sayfalarda yönlendirme
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Bu sayfayı görüntülemek için giriş yapmalısınız.';
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Admin gerektiren sayfalarda yönlendirme
 */
function require_admin(): void
{
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Bu sayfaya erişim yetkiniz yok.';
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

/**
 * Kullanıcı bilgilerini getirir
 */
function get_current_user_data(PDO $pdo): ?array
{
    if (!is_logged_in()) return null;
    
    $stmt = $pdo->prepare("SELECT id, username, email, role, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Film beğeni sayısını getirir
 */
function get_like_count(PDO $pdo, int $movie_id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE movie_id = ?");
    $stmt->execute([$movie_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Kullanıcı bu filmi beğenmiş mi
 */
function has_user_liked(PDO $pdo, int $movie_id, int $user_id): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movie_id, $user_id]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Kullanıcı bu filmi listesine eklemiş mi
 */
function is_in_watchlist(PDO $pdo, int $movie_id, int $user_id): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movie_id, $user_id]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Tarih formatlama (Türkçe)
 */
function format_date(string $date): string
{
    $months = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "{$day} {$month} {$year}";
}

/**
 * Zaman farkı hesapla (ör. "2 saat önce")
 */
function time_ago(string $datetime): string
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' yıl önce';
    if ($diff->m > 0) return $diff->m . ' ay önce';
    if ($diff->d > 0) return $diff->d . ' gün önce';
    if ($diff->h > 0) return $diff->h . ' saat önce';
    if ($diff->i > 0) return $diff->i . ' dakika önce';
    return 'Az önce';
}

/**
 * Flash mesaj göster
 */
function show_flash_messages(): string
{
    $html = '';
    
    if (isset($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success">' . e($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    
    if (isset($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-error">' . e($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    
    return $html;
}

/**
 * Metin kısaltma
 */
function truncate(string $text, int $length = 150): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Video türü label
 */
function video_type_label(string $type): string
{
    $labels = [
        'trailer'  => 'Fragman',
        'sahne'    => 'Sahneler',
        'roportaj' => 'Röportaj',
        'ekstra'   => 'Ek Videolar'
    ];
    return $labels[$type] ?? $type;
}
