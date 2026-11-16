<?php
/**
 * Admin Functions - Clean Version
 * Teddy Bear Shop
 */

// Đảm bảo session được start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== ADMIN UTILITY FUNCTIONS =====

if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: admin-login.php');
            exit();
        }
    }
}

if (!function_exists('getAdminInfo')) {
    function getAdminInfo() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'] ?? 0,
            'username' => $_SESSION['admin_username'] ?? '',
            'name' => $_SESSION['admin_name'] ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
            'role' => $_SESSION['admin_role'] ?? 'admin'
        ];
    }
}

if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details = '') {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $admin_info = getAdminInfo();
            $timestamp = date('Y-m-d H:i:s');
            $log = "[{$timestamp}] Admin {$admin_info['username']}: {$action}";
            if ($details) $log .= " - {$details}";
            error_log($log);
        }
    }
}

if (!function_exists('formatAdminDate')) {
    function formatAdminDate($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
}

if (!function_exists('getAdminStats')) {
    function getAdminStats() {
        global $db;
        $stats = [
            'total_products' => 0,
            'total_orders' => 0,
            'total_users' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0
        ];
        
        try {
            $result = $db->fetch("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
            $stats['total_products'] = $result['count'] ?? 0;
            
            $result = $db->fetch("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result['count'] ?? 0;
            
            $result = $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $stats['total_users'] = $result['count'] ?? 0;
            
            $result = $db->fetch("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
            $stats['total_revenue'] = $result['total'] ?? 0;
            
            $result = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
            $stats['pending_orders'] = $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Admin stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
}

if (!function_exists('deleteImage')) {
    function deleteImage($filename, $directory = '../images/') {
        if ($filename && $filename !== 'default.jpg' && file_exists($directory . $filename)) {
            return unlink($directory . $filename);
        }
        return true;
    }
}

if (!function_exists('getUserIP')) {
    function getUserIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// ===== AUTO CREATE ADMIN IF NOT EXISTS =====
if (!function_exists('createDefaultAdmin')) {
    function createDefaultAdmin() {
        global $db;
        try {
            $admin_exists = $db->fetch("SELECT COUNT(*) as count FROM admins");
            if ($admin_exists['count'] == 0) {
                $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $db->execute(
                    "INSERT INTO admins (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())",
                    ['admin', 'admin@teddyshop.com', $password_hash, 'Super Administrator', 'super_admin']
                );
                return true;
            }
        } catch (Exception $e) {
            error_log("Create default admin error: " . $e->getMessage());
        }
        return false;
    }
}

// Auto create admin if needed
try {
    if (isset($db)) {
        createDefaultAdmin();
    }
} catch (Exception $e) {
    // Silent fail
}
?> 