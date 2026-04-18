<?php
/**
 * api/watchlist.php - İzleme Listesi AJAX Endpoint
 * POST: Listeye ekleme/çıkarma (toggle)
 */

require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Güvenlik doğrulaması başarısız.']);
    exit;
}

$movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
if (!$movie_id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz film ID.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Film var mı kontrol et
$stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Film bulunamadı.']);
    exit;
}

// Watchlist durumunu kontrol et
$stmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$user_id, $movie_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Listeden çıkar
    $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$user_id, $movie_id]);
    $in_watchlist = false;
    $message = 'Film listeden çıkarıldı.';
} else {
    // Listeye ekle
    $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $movie_id]);
    $in_watchlist = true;
    $message = 'Film listenize eklendi!';
}

echo json_encode([
    'success'      => true,
    'in_watchlist'  => $in_watchlist,
    'message'       => $message
]);
