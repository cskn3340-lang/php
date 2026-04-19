<?php
/**
 * setup_sqlite.php - MySQL to SQLite migration script
 */

require_once __DIR__ . '/config.php';

if (file_exists(DB_FILE)) {
    unlink(DB_FILE);
}

try {
    $pdo = new PDO("sqlite:" . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/database.sql');

    // Remove MySQL specific header
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $sql);
    $sql = preg_replace('/USE `.*?`;/is', '', $sql);

    // Convert INT AUTO_INCREMENT to INTEGER PRIMARY KEY AUTOINCREMENT
    $sql = preg_replace('/INT AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);

    // Keep the movies.year column name intact while converting its type for SQLite
    $sql = preg_replace('/(`year`)\s+YEAR\b/i', '$1 INTEGER', $sql);

    // Remove ENGINE, CHARSET, COLLATE
    $sql = preg_replace('/ENGINE=InnoDB/i', '', $sql);
    $sql = preg_replace('/DEFAULT CHARSET=[^ ;]*/i', '', $sql);
    $sql = preg_replace('/COLLATE=[^ ;]*/i', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove comments

    // Convert ENUM to TEXT
    $sql = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $sql);

    // Convert common MySQL functions used in the app
    $sql = preg_replace('/\bNOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
    $sql = preg_replace('/\bRAND\(\)/i', 'RANDOM()', $sql);

    // Remove ON UPDATE CURRENT_TIMESTAMP and COMMENTs
    $sql = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $sql);
    $sql = preg_replace('/COMMENT\s+\'[^\']*\'/i', '', $sql);

    // Remove UNIQUE KEY syntax and replace with UNIQUE
    $sql = preg_replace('/UNIQUE KEY `[^`]+` \(([^)]+)\)/i', 'UNIQUE ($1)', $sql);

    // Split and execute queries
    $queries = explode(';', $sql);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }

    echo "Veritabanı başarıyla oluşturuldu ve veriler yüklendi.\n";
} catch (Exception $e) {
    die("Hata: " . $e->getMessage() . "\n");
}
