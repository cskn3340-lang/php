<?php
/**
 * api/search_api.php - Canlı Arama AJAX Endpoint
 * GET: Film arama sonuçlarını JSON olarak döner
 */

require_once __DIR__ . '/../config.php';
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$search_term = '%' . $query . '%';

// Film ara
$stmt = $pdo->prepare("
    SELECT id, title, year, poster_url, director, genre 
    FROM movies 
    WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?
    ORDER BY title ASC 
    LIMIT 8
");
$stmt->execute([$search_term, $search_term, $search_term]);
$results = $stmt->fetchAll();

// Oyuncu adına göre de ara
if (count($results) < 8) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.id, m.title, m.year, m.poster_url, m.director, m.genre 
        FROM movies m 
        INNER JOIN movie_actors ma ON m.id = ma.movie_id 
        INNER JOIN actors a ON ma.actor_id = a.id 
        WHERE a.name LIKE ? 
        LIMIT ?
    ");
    $remaining = 8 - count($results);
    $stmt->execute([$search_term, $remaining]);
    $actor_results = $stmt->fetchAll();
    
    $existing_ids = array_column($results, 'id');
    foreach ($actor_results as $ar) {
        if (!in_array($ar['id'], $existing_ids)) {
            $results[] = $ar;
        }
    }
}

echo json_encode(['results' => $results]);
