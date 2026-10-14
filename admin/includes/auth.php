<?php
/**
 * Admin Authentication Guard
 * Ensures only authenticated administrators can view admin dashboard pages.
 */

require_once dirname(dirname(__DIR__)) . '/config/database.php';

require_login();
$adminUser = $_SESSION['admin_user'] ?? ['username' => 'admin', 'name' => 'Administrator'];

// Count unread / new inquiries for sidebar badge
try {
    $pdo = get_db_connection();
    $unreadStmt = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Baru'");
    $newInquiriesCount = (int)$unreadStmt->fetchColumn();
} catch (Exception $e) {
    $newInquiriesCount = 0;
}
