<?php
/**
 * Clear Cart Handler - Teddy Bear Shop
 * File: cart/clear-cart.php
 * Xóa toàn bộ giỏ hàng
 */

session_start();
require_once '../config/config.php';
require_once '../config/cart_functions.php';

// Set JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Chỉ cho phép phương thức POST'
    ]);
    exit;
}

try {
    // Get user ID
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Clear cart
    $result = clearCart($user_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng!',
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xóa giỏ hàng!'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Clear cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi hệ thống xảy ra!'
    ]);
}
exit;
?>