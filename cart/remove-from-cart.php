<?php
/**
 * Remove From Cart Handler - Teddy Bear Shop
 * File: cart/remove-from-cart.php
 * Xóa sản phẩm khỏi giỏ hàng
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
    // Get POST data
    $input = file_get_contents('php://input');
    $json_data = json_decode($input, true);
    
    if ($json_data) {
        $product_id = (int)($json_data['product_id'] ?? 0);
    } else {
        $product_id = (int)($_POST['product_id'] ?? 0);
    }
    
    // Validation
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID sản phẩm không hợp lệ!'
        ]);
        exit;
    }
    
    // Get user ID
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Get product name before removing
    $product = $db->fetch("SELECT name FROM products WHERE id = ?", [$product_id]);
    $product_name = $product ? $product['name'] : 'Sản phẩm';
    
    // Remove from cart
    $result = removeCartItem($product_id, $user_id);
    
    if (is_array($result) && isset($result['success']) && $result['success']) {
        // Get updated cart info
        $cart_summary = getCartSummary($user_id);
        
        echo json_encode([
            'success' => true,
            'message' => "Đã xóa {$product_name} khỏi giỏ hàng!",
            'cart_count' => $cart_summary['count'],
            'cart_total' => $cart_summary['total'],
            'cart_subtotal' => $cart_summary['subtotal'],
            'product_name' => $product_name
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm khỏi giỏ hàng!']);
    }
    
} catch (Exception $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi hệ thống xảy ra!'
    ]);
}
exit;
?>