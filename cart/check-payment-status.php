<?php
/**
 * Check Payment Status - Kiểm tra trạng thái thanh toán
 * API endpoint để check xem đơn hàng đã thanh toán chưa
 */

session_start();
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Cho phép CORS

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin đơn hàng'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // Lấy thông tin đơn hàng
    $order = $db->fetch(
        "SELECT payment_status, order_status FROM orders WHERE order_number = ?",
        [$order_number]
    );

    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Đơn hàng không tồn tại'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Trả về trạng thái thanh toán
    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'payment_status' => $order['payment_status'],
        'order_status' => $order['order_status']
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Check payment status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi kiểm tra trạng thái'
    ], JSON_UNESCAPED_UNICODE);
}


