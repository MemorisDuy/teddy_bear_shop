<?php
/**
 * Admin Index Page - Redirect to appropriate location
 */

require_once '../config/config.php';
require_once '../includes/admin_functions.php';

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

if (isAdmin()) {
    header("Location: dashboard.php");
} else {
    header("Location: admin-login.php");
}

exit();
?>