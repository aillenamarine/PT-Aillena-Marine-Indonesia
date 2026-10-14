<?php
/**
 * PT Aillena Marine Indonesia - CMS Configuration
 * Global constants, security helpers, and session management.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('GALLERY_UPLOADS_PATH', UPLOADS_PATH . '/gallery');
define('BACKUP_PATH', UPLOADS_PATH . '/backups');

// Application Details
define('APP_NAME', 'PT Aillena Marine Indonesia');
define('APP_TAGLINE', 'Admin Panel & Content Management System');
define('APP_VERSION', '2.0.0');

// Create required upload directories if they don't exist
$dirsToCreate = [DATA_PATH, UPLOADS_PATH, GALLERY_UPLOADS_PATH, BACKUP_PATH];
foreach ($dirsToCreate as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Protect data directory with .htaccess if not already present
$dataHtaccess = DATA_PATH . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    @file_put_contents($dataHtaccess, "# Deny direct web access to database file\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
}

/**
 * Generate CSRF Token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Clean & Sanitize Input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Set Flash Message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Get and Clear Flash Message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * JSON Response Helper
 */
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require Login Guard
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Silakan masuk terlebih dahulu untuk mengakses dashboard.');
        header('Location: login.php');
        exit;
    }
}
