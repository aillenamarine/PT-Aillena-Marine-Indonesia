<?php
/**
 * API: Get Gallery Items
 * Returns list of verified gallery items formatted in JSON.
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = get_db_connection();

    $category = sanitize($_GET['category'] ?? 'all');

    if ($category !== 'all' && !empty($category)) {
        $stmt = $pdo->prepare("
            SELECT id, title, description, category, image_url, location, tags, display_order, created_at 
            FROM gallery 
            WHERE category = :category 
            ORDER BY display_order ASC, created_at DESC
        ");
        $stmt->execute([':category' => $category]);
    } else {
        $stmt = $pdo->query("
            SELECT id, title, description, category, image_url, location, tags, display_order, created_at 
            FROM gallery 
            ORDER BY display_order ASC, created_at DESC
        ");
    }

    $items = $stmt->fetchAll();

    json_response([
        'success' => true,
        'count'   => count($items),
        'data'    => $items
    ]);
} catch (Exception $e) {
    error_log('Error getting gallery: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Gagal memuat galeri.'], 500);
}
