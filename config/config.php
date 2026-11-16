<?php
/**
 * Complete Configuration - Teddy Bear Shop
 * NO DUPLICATE FUNCTIONS - FIXED IMAGE PATHS
 */

// Define environment constants FIRST
define('APP_ENV', 'development');
define('APP_DEBUG', true);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings constants
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);
define('USERS_PER_PAGE', 20);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('SHIPPING_FEE', 30000);
define('DEFAULT_SHIPPING_FEE', 30000);
define('FREE_SHIPPING_MIN', 500000);

// Basic constants
define('SITE_NAME', 'Teddy Bear Paradise 🐻');
define('SITE_URL', 'http://localhost/teddy_bear_shop');
define('ADMIN_EMAIL', 'admin@teddyshop.com');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'teddy_bear_shop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Third-party API Keys
define('PIXABAY_API_KEY', '53623569-98092d3fb35e9181d25556024');
define('GEMINI_API_KEY', 'AIzaSyCe-hID7G0JiK4zgpzFRFuDU1LWUP8Ai0g');

// Paths - FIXED FOR CURRENT IMAGE STRUCTURE
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('CSS_PATH', SITE_URL . '/css/');
define('JS_PATH', SITE_URL . '/js/');
define('IMG_PATH', SITE_URL . '/images/');

// Load Database class
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/cart_functions.php';

// Initialize database connection
try {
    $db = new Database();
    $GLOBALS['db'] = $db;
} catch (Exception $e) {
    if (APP_DEBUG) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Đang có lỗi kỹ thuật. Vui lòng thử lại sau!");
    }
}

// ===== ALL FUNCTIONS - FIXED DUPLICATES =====

// Basic utility functions
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map('sanitizeInput', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        if (strpos($url, 'http') !== 0) {
            $url = SITE_URL . '/' . ltrim($url, '/');
        }
        header("Location: $url");
        exit();
    }
}

// Validation functions
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        return preg_match('/^[0-9]{10,11}$/', str_replace([' ', '-', '.'], '', $phone));
    }
}

// Time and utility functions
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'vừa xong';
        if ($time < 3600) return floor($time/60) . ' phút trước';
        if ($time < 86400) return floor($time/3600) . ' giờ trước';
        if ($time < 2592000) return floor($time/86400) . ' ngày trước';
        if ($time < 31104000) return floor($time/2592000) . ' tháng trước';
        return floor($time/31104000) . ' năm trước';
    }
}

// Product functions
if (!function_exists('getProducts')) {
    function getProducts($limit = 12, $category_id = null, $is_featured = false) {
        global $db;
        
        $sql = "SELECT * FROM products WHERE status = 'active'";
        $params = [];
        
        if ($category_id) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
        }
        
        if ($is_featured) {
            $sql .= " AND is_featured = 1";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $db->fetchAll($sql, $params);
    }
}

if (!function_exists('getFeaturedProducts')) {
    function getFeaturedProducts($limit = 8) {
        return getProducts($limit, null, true);
    }
}

if (!function_exists('getProductById')) {
    function getProductById($productId) {
        global $db;
        try {
            return $db->fetch(
                "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = ?", 
                [$productId]
            );
        } catch (Exception $e) {
            error_log("Get product by ID error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getProductBySlug')) {
    function getProductBySlug($slug) {
        global $db;
        try {
            return $db->fetch(
                "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.slug = ? AND p.status = 'active'", 
                [$slug]
            );
        } catch (Exception $e) {
            error_log("Get product by slug error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('updateProductViews')) {
    function updateProductViews($productId) {
        global $db;
        return $db->execute("UPDATE products SET views = views + 1 WHERE id = ?", [$productId]);
    }
}

// Category functions
if (!function_exists('getCategories')) {
    function getCategories() {
        global $db;
        return $db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    }
}

if (!function_exists('getCategoryBySlug')) {
    function getCategoryBySlug($slug) {
        global $db;
        return $db->fetch("SELECT * FROM categories WHERE slug = ? AND status = 'active'", [$slug]);
    }
}

// Contact functions
if (!function_exists('saveContactMessage')) {
    function saveContactMessage($data) {
        global $db;
        try {
            return $db->execute(
                "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
                [$data['name'], $data['email'], $data['phone'], $data['subject'], $data['message']]
            );
        } catch (Exception $e) {
            error_log("Save contact message error: " . $e->getMessage());
            return false;
        }
    }
}

// Flash message functions
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('getFlashMessage')) {
    function getFlashMessage($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}

if (!function_exists('flashMessage')) {
    function flashMessage($type, $message) {
        setFlashMessage($type, $message);
    }
}

// Authentication functions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

// User login function
if (!function_exists('loginUser')) {
    function loginUser($email, $password) {
        global $db;
        
        try {
            $user = $db->fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                
                // Update last login
                try {
                    $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                } catch (Exception $e) {
                    // Column might not exist, continue
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
}

// Get user by email
if (!function_exists('getUserByEmail')) {
    function getUserByEmail($email) {
        global $db;
        
        try {
            return $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
}

// Get user by ID
if (!function_exists('getUserById')) {
    function getUserById($userId) {
        global $db;
        
        try {
            return $db->fetch("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
        } catch (Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return false;
        }
    }
}

// Logout user
if (!function_exists('logoutUser')) {
    function logoutUser() {
        // Clear user session
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        
        // Remove remember me cookie
        if (isset($_COOKIE['teddy_remember'])) {
            setcookie('teddy_remember', '', time() - 3600, '/');
        }
    }
}

// Review functions
if (!function_exists('getProductReviews')) {
    function getProductReviews($productId, $limit = 10) {
        global $db;
        try {
            return $db->fetchAll(
                "SELECT * FROM reviews WHERE product_id = ? AND status IN ('approved', 'pending') ORDER BY created_at DESC LIMIT ?",
                [$productId, $limit]
            );
        } catch (Exception $e) {
            error_log("Get product reviews error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('addReview')) {
    function addReview($productId, $userId, $customerName, $rating, $comment) {
        global $db;
        try {
            return $db->execute(
                "INSERT INTO reviews (product_id, user_id, customer_name, rating, comment) VALUES (?, ?, ?, ?, ?)",
                [$productId, $userId, $customerName, $rating, $comment]
            );
        } catch (Exception $e) {
            error_log("Add review error: " . $e->getMessage());
            return false;
        }
    }
}

// Settings functions
if (!function_exists('getSetting')) {
    function getSetting($key, $default = null) {
        global $db;
        try {
            $result = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

// Generate slug from Vietnamese text
if (!function_exists('generateSlug')) {
    function generateSlug($text) {
        $text = strtolower($text);
        
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        
        $english = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        $text = str_replace($vietnamese, $english, $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
}

// Format date in Vietnamese
if (!function_exists('formatDateVN')) {
    function formatDateVN($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
}

// Upload image function
if (!function_exists('uploadImage')) {
    function uploadImage($file, $folder = 'products') {
        $uploadDir = ROOT_PATH . "/images/";
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Định dạng file không được hỗ trợ'];
        }
        
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return ['success' => false, 'message' => 'File quá lớn'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename, 'path' => "/images/$filename"];
        } else {
            return ['success' => false, 'message' => 'Không thể upload file'];
        }
    }
}

// Calculate shipping fee
if (!function_exists('calculateShipping')) {
    function calculateShipping($totalAmount, $customerLocation = null) {
        if ($totalAmount >= FREE_SHIPPING_MIN) {
            return 0;
        }
        return DEFAULT_SHIPPING_FEE;
    }
}

// Create demo data
if (!function_exists('createDemoData')) {
    function createDemoData() {
        global $db;
        
        try {
            $userCount = 0;
            try {
                $result = $db->fetch("SELECT COUNT(*) as count FROM users");
                $userCount = $result['count'] ?? 0;
            } catch (Exception $e) {
                return;
            }
            
            if ($userCount == 0) {
                $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
                
                try {
                    $db->execute(
                        "INSERT INTO users (full_name, email, password, role, status, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        ['Super Admin', 'admin@teddyshop.com', $hashedPassword, 'admin', 'active']
                    );
                    
                    $customerPassword = password_hash('123456', PASSWORD_DEFAULT);
                    $db->execute(
                        "INSERT INTO users (full_name, email, password, role, status, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())",
                        ['Demo User', 'user@demo.com', $customerPassword, 'user', 'active']
                    );
                    
                    if (APP_DEBUG) {
                        error_log("Demo users created successfully");
                    }
                } catch (Exception $e) {
                    if (APP_DEBUG) {
                        error_log("Could not create demo users: " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            if (APP_DEBUG) {
                error_log("Create demo data error: " . $e->getMessage());
            }
        }
    }
}

// Load settings from database
try {
    if (isset($db)) {
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
        if ($settings) {
            foreach ($settings as $setting) {
                $constantName = 'SETTING_' . strtoupper($setting['setting_key']);
                if (!defined($constantName)) {
                    define($constantName, $setting['setting_value']);
                }
            }
        }
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Could not load settings: ' . $e->getMessage());
    }
}

// Create demo data
createDemoData();

// Related products function
if (!function_exists('getRelatedProducts')) {
    function getRelatedProducts($product_id, $category_id, $limit = 4) {
        global $db;
        try {
            return $db->fetchAll(
                "SELECT * FROM products 
                 WHERE status = 'active' AND category_id = ? AND id != ?
                 ORDER BY RAND() LIMIT ?",
                [$category_id, $product_id, $limit]
            );
        } catch (Exception $e) {
            error_log("Get related products error: " . $e->getMessage());
            return [];
        }
    }
}

// DO NOT LOAD includes/functions.php - ALL FUNCTIONS ARE HERE
?>