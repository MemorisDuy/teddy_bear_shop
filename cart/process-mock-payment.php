<?php
/**
 * Process Mock Payment - Xử lý thanh toán giả lập
 * API endpoint để xử lý callback thanh toán giả
 */

session_start();
require_once '../config/config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *'); // Cho phép CORS từ Netlify
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cho phép cả GET và POST để dễ gọi từ Netlify
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy từ GET parameters
    $order_number = $_GET['order_number'] ?? '';
    $payment_method = $_GET['payment_method'] ?? '';
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy từ POST body hoặc form data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $order_number = $input['order_number'] ?? '';
        $payment_method = $input['payment_method'] ?? '';
    } else {
        $order_number = $_POST['order_number'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Dữ liệu đã được lấy ở trên (từ GET hoặc POST)
    if (empty($order_number)) {
        throw new Exception('Thiếu thông tin đơn hàng');
    }

    // Lấy thông tin đơn hàng
    $order = $db->fetch(
        "SELECT * FROM orders WHERE order_number = ?",
        [$order_number]
    );

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }

    // Kiểm tra trạng thái đơn hàng
    if ($order['payment_status'] === 'paid') {
        echo json_encode([
            'success' => true,
            'message' => 'Đơn hàng đã được thanh toán trước đó',
            'order_number' => $order_number
        ]);
        exit();
    }

    // Bắt đầu transaction
    $db->beginTransaction();

    // Cập nhật trạng thái thanh toán
    $update_result = $db->execute(
        "UPDATE orders SET 
            payment_status = 'paid',
            order_status = CASE 
                WHEN payment_method = 'cod' THEN 'pending'
                ELSE 'confirmed'
            END,
            updated_at = NOW()
        WHERE order_number = ?",
        [$order_number]
    );

    if (!$update_result) {
        throw new Exception('Không thể cập nhật trạng thái thanh toán');
    }

    // Tạo log thanh toán
    try {
        $db->insert(
            "INSERT INTO order_logs (order_id, status, message, created_at) 
             VALUES (?, ?, ?, NOW())",
            [
                $order['id'],
                'paid',
                "Thanh toán thành công qua {$payment_method} (Mock Payment)"
            ]
        );
    } catch (Exception $e) {
        // Log table có thể không tồn tại, bỏ qua
        error_log("Order log insert failed: " . $e->getMessage());
    }

    // Commit transaction
    $db->commit();

    // Log thành công
    error_log("Mock payment processed: Order #$order_number, Method: $payment_method");

    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Thanh toán thành công!',
        'order_number' => $order_number,
        'payment_method' => $payment_method,
        'payment_status' => 'paid'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Rollback nếu có lỗi
    try {
        $db->rollback();
    } catch (Exception $rollback_error) {
        // Ignore rollback errors
    }

    error_log("Mock payment error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    try {
        $db->rollback();
    } catch (Exception $rollback_error) {
        // Ignore rollback errors
    }

    error_log("Mock payment fatal error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi hệ thống xảy ra. Vui lòng thử lại.'
    ], JSON_UNESCAPED_UNICODE);
}

