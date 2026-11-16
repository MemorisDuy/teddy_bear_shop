<?php
/**
 * Update Cart Handler - Teddy Bear Shop
 * File: cart/update-cart.php
 * Cập nhật số lượng sản phẩm trong giỏ hàng
 */

session_start();

// Include config and cart functions with error handling
$config_path = dirname(__DIR__) . '/config/config.php';
$cart_functions_path = dirname(__DIR__) . '/config/cart_functions.php';

if (!file_exists($config_path) || !file_exists($cart_functions_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'File cấu hình hoặc chức năng không tồn tại']);
    exit;
}

require_once $config_path;
require_once $cart_functions_path;

    // Set JSON content type and CORS headers
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ cho phép phương thức POST']);
    exit;
}

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $json_data = json_decode($input, true);

    $product_id = isset($json_data['product_id']) ? (int)$json_data['product_id'] : (int)($_POST['product_id'] ?? 0);
    $quantity = isset($json_data['quantity']) ? (int)$json_data['quantity'] : (int)($_POST['quantity'] ?? 0);

    // Validation
    if ($product_id <= 0) {
        throw new Exception('ID sản phẩm không hợp lệ!');
    }

    if ($quantity < 0 || $quantity > 99) {
        throw new Exception('Số lượng phải từ 0 đến 99!');
    }

    // Get user ID
    $user_id = $_SESSION['user_id'] ?? null;

    // Check if database connection exists
    if (!isset($db) || !$db) {
        throw new Exception('Kết nối database không khả dụng!');
    }

    // Nếu quantity = 0, xóa sản phẩm
    if ($quantity == 0) {
        if ($user_id) {
            $db->execute("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        } else {
            unset($_SESSION['cart'][$product_id]);
            foreach ($_SESSION['cart'] ?? [] as $key => $qty) {
                $parts = explode('|', $key);
                if ((int)$parts[0] === (int)$product_id) {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
        $cart_count = getCartCount($user_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Đã xóa sản phẩm!',
            'cart_count' => $cart_count,
            'removed' => true
        ]);
        exit;
    }

    // Kiểm tra stock
    $product = $db->fetch("SELECT stock_quantity, price, sale_price FROM products WHERE id = ?", [$product_id]);
    if (!$product || $product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho!']);
        exit;
    }

    // Update quantity
    if ($user_id) {
        $db->execute(
            "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?", 
            [$quantity, $user_id, $product_id]
        );
    } else {
        // For session cart, use key format: product_id|size if size exists
        // Check if there's a size in the cart key
        $cart_key = $product_id;
        foreach ($_SESSION['cart'] ?? [] as $key => $qty) {
            $parts = explode('|', $key);
            if ($parts[0] == $product_id) {
                $cart_key = $key;
                break;
            }
        }
        $_SESSION['cart'][$cart_key] = $quantity;
    }

    // Tính toán lại giá
    $price = $product['sale_price'] ?: $product['price'];
    $item_total = $price * $quantity;

    // Get cart summary
    $cart_summary = getCartSummary($user_id);

    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật số lượng!',
        'item_total' => $item_total,
        'cart_count' => $cart_summary['count'],
        'total_quantity' => $cart_summary['count'], // Alias for backward compatibility
        'cart_subtotal' => $cart_summary['subtotal'],
        'cart_total' => $cart_summary['total'],
        'shipping_fee' => $cart_summary['shipping_fee'],
        'free_shipping_remaining' => $cart_summary['free_shipping_remaining'] ?? 0
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Update cart error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'cart_count' => 0,
        'cart_total' => 0,
        'cart_subtotal' => 0,
        'item_total' => 0
    ]);
}
exit;
?>