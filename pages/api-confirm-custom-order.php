<?php
/**
 * API: Xác nhận ảnh + Đánh giá sao + Gửi email cho shop
 * POST: /pages/api-confirm-custom-order.php
 * 
 * Bước 2: Khách chọn ảnh, đánh giá sao, xác nhận → gửi email shop
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("API Error: [$errno] $errstr in $errfile:$errline");
    return true;
}, E_ALL);

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $exception->getMessage()
    ]);
    exit;
});

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    require_once '../config/config.php';
    require_once '../includes/functions.php';
    require_once '../includes/email_functions.php';
    
    if (!isset($db) || !$db) {
        throw new Exception('Database connection failed');
    }
    
    // Lấy dữ liệu
    $custom_order_id = intval($_POST['custom_order_id'] ?? 0);
    $selected_image_url = trim($_POST['selected_image_url'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    
    // DEBUG: Log input data
    error_log("=== API-CONFIRM-CUSTOM-ORDER DEBUG ===");
    error_log("Input - custom_order_id: {$custom_order_id}");
    error_log("Input - selected_image_url: " . substr($selected_image_url, 0, 100) . "...");
    error_log("Input - rating: {$rating}");
    
    // Validate
    if ($custom_order_id <= 0) {
        throw new Exception('Mã yêu cầu không hợp lệ', 400);
    }
    
    if (empty($selected_image_url)) {
        throw new Exception('Vui lòng chọn một ảnh', 400);
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Vui lòng đánh giá từ 1-5 sao', 400);
    }
    
    // Kiểm tra yêu cầu tồn tại
    $custom_order = $db->fetch(
        "SELECT * FROM custom_orders WHERE id = ?",
        [$custom_order_id]
    );
    
    if (!$custom_order) {
        throw new Exception('Yêu cầu không tồn tại', 404);
    }
    
    error_log("Found order: " . print_r($custom_order, true));
    
    // Cập nhật database
    error_log("Updating order with: image_url={$selected_image_url}, rating={$rating}");
    $db->query(
        "UPDATE custom_orders SET selected_image_url = ?, rating = ?, status = 'confirmed', updated_at = NOW() WHERE id = ?",
        [$selected_image_url, $rating, $custom_order_id]
    );
    
    // Lấy thông tin cập nhật
    $custom_order = $db->fetch(
        "SELECT * FROM custom_orders WHERE id = ?",
        [$custom_order_id]
    );
    
    error_log("After update - selected_image_url: " . ($custom_order['selected_image_url'] ?? 'NULL'));
    error_log("After update - rating: " . ($custom_order['rating'] ?? 'NULL'));
    error_log("After update - status: " . ($custom_order['status'] ?? 'NULL'));
    
    // Gửi email cho shop với ảnh đã chọn + nội dung yêu cầu
    $shop_email_sent = false;
    try {
        $shop_email_sent = sendCustomOrderConfirmationToShop($custom_order);
    } catch (Exception $e) {
        error_log('Shop email error: ' . $e->getMessage());
    }
    
    // Gửi email xác nhận cho khách
    $customer_email_sent = false;
    try {
        $customer_subject = "✅ Yêu cầu #{$custom_order_id} đã được gửi cho shop";
        $customer_html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #4caf50, #45a049); color: white; padding: 20px; border-radius: 8px; text-align: center; }
                .content { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Yêu cầu đã được gửi!</h1>
                </div>
                
                <div class='content'>
                    <h3>Cảm ơn bạn!</h3>
                    <p>Yêu cầu đặt mẫu riêng của bạn (mã <strong>#" . $custom_order_id . "</strong>) đã được gửi đến shop.</p>
                    
                    <div style='background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p style='margin: 0;'><strong>🎉 Những bước tiếp theo:</strong></p>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>Shop sẽ liên hệ bạn trong vòng 2-4 giờ</li>
                            <li>Thảo luận giá cả và thời gian thực hiện</li>
                            <li>Thực hiện thiết kế theo ý bạn</li>
                            <li>Giao hàng nhanh chóng</li>
                        </ul>
                    </div>
                    
                    <p style='color: #666;'>Vui lòng kiểm tra email và điện thoại thường xuyên để nhận tin từ shop.</p>
                </div>
                
                <div class='footer'>
                    <p>Teddy Bear Paradise - Cửa hàng gấu bông chất lượng cao</p>
                </div>
            </div>
        </body>
        </html>";
        
        $customer_email_sent = sendEmail($custom_order['customer_email'], $customer_subject, $customer_html);
    } catch (Exception $e) {
        error_log('Customer confirmation email error: ' . $e->getMessage());
    }
    
    // Trả về thành công
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '✅ Yêu cầu đã được gửi cho shop thành công! Shop sẽ liên hệ bạn sớm.',
        'custom_order_id' => $custom_order_id,
        'emails_sent' => [
            'shop' => $shop_email_sent,
            'customer' => $customer_email_sent
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    ob_clean();
    error_log('Confirm Custom Order API Error: ' . $e->getMessage());
    $code = intval($e->getCode()) ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

ob_clean();
http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Lỗi không xác định'
]);
?>
