<?php
/**
 * Add to Cart Handler - Teddy Bear Shop
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/config.php';
require_once '../config/cart_functions.php';

// TEMP FIX: Accept both GET and POST for testing
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Test with sample data
    $_POST['product_id'] = $_GET['product_id'] ?? 1;
    $_POST['quantity'] = $_GET['quantity'] ?? 1;
    echo "<!-- DEBUG: GET request converted to POST -->";
}
// Chỉ reject nếu không phải GET hoặc POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $size = trim($_POST['size'] ?? ''); // Lấy size từ POST
    
    // Validate input
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check database connection
    if (!isset($db) || !$db) {
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check if product exists and is available
    $product = $db->fetch(
        "SELECT id, name, price, sale_price, stock_quantity, status FROM products WHERE id = ?", 
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($product['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không khả dụng!'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho! Chỉ còn ' . $product['stock_quantity'] . ' sản phẩm.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check if user is logged in
    $user_id = $_SESSION['user_id'] ?? null;
    
    if ($user_id) {
        // Database cart for logged-in users
        $result = addToCartDB($product_id, $quantity, $user_id, $product, $db, $size);
    } else {
        // Session cart for guests
        $result = addToCartSession($product_id, $quantity, $product, $size);
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    error_log('POST: ' . print_r($_POST, true));
    error_log('SESSION user_id: ' . print_r($_SESSION['user_id'] ?? null, true));
    error_log('PRODUCT: ' . print_r(isset($product) ? $product : null, true));
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Add to cart for logged-in users (database)
 */
function addToCartDB($product_id, $quantity, $user_id, $product, $db, $size = '') {
    try {
        // Check if item already exists in cart (handle NULL size)
        if ($size === '') {
            $existing = $db->fetch(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND (size = '' OR size IS NULL)", 
                [$user_id, $product_id]
            );
        } else {
            $existing = $db->fetch(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?", 
                [$user_id, $product_id, $size]
            );
        }
        
        if ($existing) {
            // Update existing item
            $new_quantity = $existing['quantity'] + $quantity;
            
            // Check stock limit
            if ($new_quantity > $product['stock_quantity']) {
                return [
                    'success' => false, 
                    'message' => 'Không thể thêm! Tổng số lượng (' . $new_quantity . ') vượt quá tồn kho (' . $product['stock_quantity'] . ').'
                ];
            }
            
            $success = $db->execute(
                "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", 
                [$new_quantity, $existing['id']]
            );
            
            if (!$success) {
                error_log("addToCartDB: Failed to update cart item");
                return ['success' => false, 'message' => 'Không thể cập nhật giỏ hàng!'];
            }
            
            error_log("addToCartDB: Updated product $product_id, size '$size', qty $new_quantity for user $user_id");
            $message = "Đã cập nhật số lượng " . htmlspecialchars($product['name']) . " trong giỏ hàng!";
        } else {
            // Add new item (luôn có trường size)
            $success = $db->execute(
                "INSERT INTO cart (user_id, product_id, quantity, size, created_at) VALUES (?, ?, ?, ?, NOW())", 
                [$user_id, $product_id, $quantity, $size]
            );
            
            if (!$success) {
                error_log("addToCartDB: Failed to insert cart item");
                return ['success' => false, 'message' => 'Không thể thêm vào giỏ hàng!'];
            }
            
            error_log("addToCartDB: Inserted product $product_id, size '$size', qty $quantity for user $user_id");
            $message = "Đã thêm " . htmlspecialchars($product['name']) . " vào giỏ hàng!";
        }
        
        // Get updated cart count
        $cart_count = getCartCount($user_id);
        
        return [
            'success' => true,
            'message' => $message,
            'cart_count' => $cart_count,
            'product_name' => $product['name']
        ];
        
    } catch (Exception $e) {
        error_log("Database cart error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
    }
}

/**
 * Add to cart for guests (session)
 */
function addToCartSession($product_id, $quantity, $product, $size = '') {
    try {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $cart_key = $product_id . ($size ? ('|' . $size) : '');
        $current_quantity = $_SESSION['cart'][$cart_key] ?? 0;
        $new_quantity = $current_quantity + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            return [
                'success' => false,
                'message' => 'Không thể thêm! Tổng số lượng (' . $new_quantity . ') vượt quá tồn kho (' . $product['stock_quantity'] . ').'
            ];
        }
        $_SESSION['cart'][$cart_key] = $new_quantity;
        $cart_count = array_sum($_SESSION['cart']);
        $message = $current_quantity > 0
            ? "Đã cập nhật số lượng " . htmlspecialchars($product['name']) . " (" . htmlspecialchars($size) . ") trong giỏ hàng!"
            : "Đã thêm " . htmlspecialchars($product['name']) . " (" . htmlspecialchars($size) . ") vào giỏ hàng!";
        return [
            'success' => true,
            'message' => $message,
            'cart_count' => $cart_count,
            'product_name' => $product['name'],
            'size' => $size
        ];
    } catch (Exception $e) {
        error_log("Session cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi session!'];
    }
}
?>