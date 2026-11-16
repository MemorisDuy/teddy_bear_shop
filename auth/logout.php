<?php
/**
 * Logout Handler - Đăng xuất
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie
if (isset($_COOKIE['teddy_remember'])) {
    setcookie('teddy_remember', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to home page with logout message
header('Location: ../index.php?logout=success');
exit();
?>