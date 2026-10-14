<?php
/**
 * API: Get Public Site Settings
 * Returns contact and operational settings.
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $raw = $stmt->fetchAll();

    $settings = [];
    foreach ($raw as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    json_response([
        'success' => true,
        'data'    => $settings
    ]);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'Gagal memuat pengaturan.'], 500);
}
