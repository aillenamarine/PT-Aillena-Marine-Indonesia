<?php
/**
 * Admin Logout Handler
 */

require_once dirname(__DIR__) . '/config/config.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

session_start();
set_flash('info', 'Anda telah berhasil keluar dari sistem CMS.');
header('Location: login.php');
exit;
