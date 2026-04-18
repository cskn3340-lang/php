<?php
/**
 * api/comment.php - Yorum AJAX Endpoint
 * POST action=add: Yorum ekleme
 * POST action=delete: Yorum silme
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

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'add':
        $movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
        $content = trim($_POST['content'] ?? '');
        
        if (!$movie_id) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz film ID.']);
            exit;
        }
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Yorum boş olamaz.']);
            exit;
        }
        
        if (mb_strlen($content) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Yorum 1000 karakterden uzun olamaz.']);
            exit;
        }
        
        // Film var mı kontrol et
        $stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
        $stmt->execute([$movie_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Film bulunamadı.']);
            exit;
        }
        
        // Yorum ekle
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, movie_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $movie_id, $content]);
        
        $comment_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Yorum eklendi!',
            'comment' => [
                'id'       => $comment_id,
                'username' => $_SESSION['username'],
                'content'  => $content,
                'date'     => 'Az önce'
            ]
        ]);
        break;
        
    case 'delete':
        $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
        
        if (!$comment_id) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz yorum ID.']);
            exit;
        }
        
        // Yorum sahibi veya admin mi kontrol et
        $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Yorum bulunamadı.']);
            exit;
        }
        
        if ($comment['user_id'] != $user_id && !is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Bu yorumu silme yetkiniz yok.']);
            exit;
        }
        
        // Yorumu sil
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        
        echo json_encode(['success' => true, 'message' => 'Yorum silindi.']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Geçersiz işlem.']);
}
