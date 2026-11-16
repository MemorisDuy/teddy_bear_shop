<?php
session_start();
require_once "../config/config.php";
require_once "../config/cart_functions.php";

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Debug function
function debugLog($message, $data = null) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log("COUPON DEBUG: $message " . ($data ? json_encode($data) : ''));
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    // Lấy user_id (có thể null cho guest)
    $user_id = $_SESSION['user_id'] ?? null;
    // Lấy mã giảm giá từ POST (hỗ trợ case-insensitive)
    $coupon_code = trim($_POST['coupon_code'] ?? $_POST['code'] ?? '');
    debugLog("Received coupon code", ['code' => $coupon_code, 'user_id' => $user_id]);
    if (empty($coupon_code)) {
        throw new Exception('Vui lòng nhập mã giảm giá');
    }
    if (strlen($coupon_code) > 50) {
        throw new Exception('Mã giảm giá không hợp lệ');
    }
    $original_code = $coupon_code;
    $coupon_code = preg_replace('/[^a-zA-Z0-9]/', '', $coupon_code);
    debugLog("Sanitized coupon code", ['original' => $original_code, 'sanitized' => $coupon_code]);
    // Kiểm tra giỏ hàng có sản phẩm không
    $cart_items = getCartItems($user_id);
    if (empty($cart_items)) {
        throw new Exception('Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi áp dụng mã giảm giá.');
    }
    // Lấy tổng tiền giỏ hàng
    $cart_summary = getCartSummary($user_id);
    $cart_total = $cart_summary['subtotal'];
    debugLog("Cart summary", $cart_summary);
    // Tìm mã giảm giá trong database (CASE INSENSITIVE)
    $coupon = $db->fetch(
        "SELECT * FROM coupons WHERE UPPER(code) = UPPER(?) AND status = 'active'", 
        [$coupon_code]
    );
    debugLog("Database query result", $coupon);
    if (!$coupon) {
        // Tìm các mã tương tự để gợi ý
        $similar_coupons = $db->fetchAll(
            "SELECT code FROM coupons WHERE UPPER(code) LIKE UPPER(?) AND status = 'active' LIMIT 3", 
            ['%' . $coupon_code . '%']
        );
        $suggestions = array_column($similar_coupons, 'code');
        throw new Exception('Mã giảm giá không tồn tại hoặc đã hết hạn' . 
            (count($suggestions) > 0 ? '. Có thể bạn muốn dùng: ' . implode(', ', $suggestions) : ''));
    }
    // Kiểm tra ngày bắt đầu
    $today = date('Y-m-d');
    if ($coupon['start_date'] > $today) {
        throw new Exception('Mã giảm giá chưa có hiệu lực. Có hiệu lực từ: ' . date('d/m/Y', strtotime($coupon['start_date'])));
    }
    // Kiểm tra ngày hết hạn
    if ($coupon['end_date'] < $today) {
        throw new Exception('Mã giảm giá đã hết hạn vào ngày: ' . date('d/m/Y', strtotime($coupon['end_date'])));
    }
    // Kiểm tra số lần sử dụng
    if ($coupon['usage_limit'] > 0 && $coupon['usage_count'] >= $coupon['usage_limit']) {
        throw new Exception('Mã giảm giá đã được sử dụng hết. Còn lại: 0/' . $coupon['usage_limit']);
    }
    // Kiểm tra minimum amount
    if ($cart_total < $coupon['minimum_amount']) {
        $remaining = $coupon['minimum_amount'] - $cart_total;
        throw new Exception('Đơn hàng tối thiểu ' . number_format($coupon['minimum_amount']) . 'đ để sử dụng mã này. Còn thiếu: ' . number_format($remaining) . 'đ');
    }
    // Tính toán giảm giá
    $discount_amount = 0;
    if ($coupon['type'] === 'percentage') {
        $discount_amount = ($cart_total * $coupon['value']) / 100;
    } elseif ($coupon['type'] === 'fixed') {
        $discount_amount = $coupon['value'];
    }
    $discount_amount = min($discount_amount, $cart_total);
    debugLog("Calculated discount", [
        'type' => $coupon['type'],
        'value' => $coupon['value'],
        'cart_total' => $cart_total,
        'discount_amount' => $discount_amount
    ]);
    // Lưu thông tin mã giảm giá vào session
    $_SESSION['applied_coupon'] = [
        'id' => $coupon['id'],
        'code' => $coupon['code'],
        'name' => $coupon['name'],
        'type' => $coupon['type'],
        'value' => $coupon['value'],
        'discount' => $discount_amount,
        'applied_at' => time()
    ];
    debugLog("Coupon applied successfully", $_SESSION['applied_coupon']);
    // Tính total sau giảm giá
    $total_after_discount = $cart_total - $discount_amount;
    $shipping_fee = $cart_summary['shipping_fee'];
    $final_total = $total_after_discount + $shipping_fee;
    echo json_encode([
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công!',
        'coupon' => [
            'code' => $coupon['code'],
            'name' => $coupon['name'],
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'discount_amount' => $discount_amount,
            'discount_formatted' => number_format($discount_amount) . 'đ'
        ],
        'cart' => [
            'subtotal' => $cart_total,
            'subtotal_formatted' => number_format($cart_total) . 'đ',
            'discount' => $discount_amount,
            'discount_formatted' => number_format($discount_amount) . 'đ',
            'shipping_fee' => $shipping_fee,
            'shipping_formatted' => number_format($shipping_fee) . 'đ',
            'total_after_discount' => $total_after_discount,
            'total_after_discount_formatted' => number_format($total_after_discount) . 'đ',
            'final_total' => $final_total,
            'final_total_formatted' => number_format($final_total) . 'đ'
        ]
    ]);
} catch (Exception $e) {
    debugLog("Error occurred", ['message' => $e->getMessage()]);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => defined('APP_DEBUG') && APP_DEBUG ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
} catch (Error $e) {
    debugLog("Fatal error occurred", ['message' => $e->getMessage()]);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi hệ thống xảy ra. Vui lòng thử lại.',
        'debug' => defined('APP_DEBUG') && APP_DEBUG ? [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    ]);
} 