<?php
/**
 * Confirm Payment - Xác nhận thanh toán từ Netlify
 * Trang này sẽ tự động xử lý thanh toán khi user redirect từ Netlify
 */

session_start();
require_once '../config/config.php';

$order_number = $_GET['order_number'] ?? '';
$payment_method = $_GET['payment_method'] ?? '';
$token = $_GET['token'] ?? '';

// Validate token
$expected_token = md5($order_number . $payment_method . 'teddy_bear_secret_key');

if (empty($order_number) || $token !== $expected_token) {
    setFlashMessage('error', 'Link xác nhận thanh toán không hợp lệ!');
    header('Location: ../pages/products.php');
    exit();
}

// Kiểm tra đơn hàng
$order = $db->fetch("SELECT * FROM orders WHERE order_number = ?", [$order_number]);

if (!$order) {
    setFlashMessage('error', 'Đơn hàng không tồn tại!');
    header('Location: ../pages/products.php');
    exit();
}

// Nếu đã thanh toán rồi, redirect đến trang thành công
if ($order['payment_status'] === 'paid') {
    header('Location: order-success.php?order_number=' . urlencode($order_number) . '&paid=1');
    exit();
}

// Xử lý thanh toán
try {
    $db->beginTransaction();
    
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
    
    // Tạo log
    try {
        $db->insert(
            "INSERT INTO order_logs (order_id, status, message, created_at) VALUES (?, ?, ?, NOW())",
            [$order['id'], 'paid', "Thanh toán thành công qua {$payment_method} (QR Code)"]
        );
    } catch (Exception $e) {
        error_log("Order log insert failed: " . $e->getMessage());
    }
    
    $db->commit();
    
    // Redirect đến trang thành công
    header('Location: order-success.php?order_number=' . urlencode($order_number) . '&paid=1');
    exit();
    
} catch (Exception $e) {
    try {
        $db->rollback();
    } catch (Exception $rollback_error) {
        // Ignore rollback errors
    }
    error_log("Confirm payment error: " . $e->getMessage());
    setFlashMessage('error', 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage());
    header('Location: mock-payment.php?order_number=' . urlencode($order_number));
    exit();
}

