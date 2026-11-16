<?php
/**
 * API: Xử lý yêu cầu đặt hàng theo mẫu riêng
 * POST: /pages/api-custom-order.php
 */

// START OUTPUT BUFFERING NGAY LẬP TỨC
ob_start();

// Set header JSON trước khi có output khác
header('Content-Type: application/json; charset=utf-8');

// Disable error display (để tránh HTML output)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Custom error handler để không output HTML
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
    // Kiểm tra method POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // Load config - bao trong try/catch
    if (!file_exists('../config/config.php')) {
        throw new Exception('Config file not found');
    }
    
    require_once '../config/config.php';
    require_once '../includes/functions.php';
    require_once '../includes/email_functions.php';
    
    // Check database
    if (!isset($db) || !$db) {
        throw new Exception('Database connection failed');
    }
    
    // Lấy dữ liệu từ form
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $search_keyword = trim($_POST['search_keyword'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = '';
    
    // Validate dữ liệu
    if (empty($customer_name)) {
        throw new Exception('Vui lòng nhập tên của bạn', 400);
    }
    
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Vui lòng nhập email hợp lệ', 400);
    }
    
    if (empty($customer_phone)) {
        throw new Exception('Vui lòng nhập số điện thoại', 400);
    }
    
    if (empty($search_keyword)) {
        throw new Exception('Vui lòng nhập từ khóa tìm kiếm', 400);
    }
    
    if (empty($description)) {
        throw new Exception('Vui lòng mô tả chi tiết mẫu gấu bạn muốn', 400);
    }
    
    // Xử lý upload ảnh nếu có
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        // Kiểm tra loại file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_mime = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($file_mime, $allowed_types)) {
            throw new Exception('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)', 400);
        }
        
        // Kiểm tra kích thước file (max 5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Kích thước ảnh không vượt quá 5MB', 400);
        }
        
        // Tạo thư mục upload nếu chưa có
        $upload_dir = '../images/custom-orders';
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                throw new Exception('Không thể tạo folder upload. Vui lòng kiểm tra quyền hệ thống.', 500);
            }
        }
        
        // Kiểm tra quyền ghi
        if (!is_writable($upload_dir)) {
            throw new Exception('Folder upload không có quyền ghi. Vui lòng kiểm tra quyền hệ thống.', 500);
        }
        
        // Tạo tên file unique
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = 'custom_order_' . time() . '_' . uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . '/' . $file_name;
        
        // Di chuyển file
        if (!@move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            throw new Exception('Lỗi tải lên ảnh. Vui lòng thử lại.', 500);
        }
        
        // Lưu URL ảnh
        $image_url = SITE_URL . '/images/custom-orders/' . $file_name;
    }
    
    // Lấy IP khách hàng
    $ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? 
                 $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
                 $_SERVER['REMOTE_ADDR'] ?? 
                 'unknown';
    
    // Lưu vào database
    $db->query(
        "INSERT INTO custom_orders 
        (customer_name, customer_email, customer_phone, search_keyword, description, image_url, ip_address, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'waiting_images', NOW())",
        [
            $customer_name,
            $customer_email,
            $customer_phone,
            $search_keyword,
            $description,
            $image_url,
            $ip_address
        ]
    );
    
    $custom_order_id = $db->getLastInsertId();
    
    if ($custom_order_id <= 0) {
        throw new Exception('Lỗi lưu dữ liệu vào database', 500);
    }
    
    // Lấy thông tin yêu cầu vừa tạo
    $custom_order = $db->fetch(
        "SELECT * FROM custom_orders WHERE id = ?",
        [$custom_order_id]
    );
    
    if (!$custom_order) {
        throw new Exception('Không thể lấy thông tin yêu cầu vừa tạo', 500);
    }
    
    // Step 1 Done: Gửi email thông báo cho khách hàng rằng đã nhận yêu cầu
    $customer_email_sent = false;
    try {
        // Email cho khách: "Chúng tôi đã nhận yêu cầu, đang tìm kiếm ảnh..."
        $customer_email_sent = sendCustomOrderAcknowledgmentToCustomer($custom_order);
    } catch (Exception $e) {
        error_log('Customer acknowledgment email error: ' . $e->getMessage());
    }
    
    // Bước 2: Tìm ảnh từ Gemini enhance + Pollinations AI tạo ảnh
    $pixabay_images = [];
    try {
        error_log('Generating AI images for keyword: ' . $search_keyword . ', description: ' . $description);
        $pixabay_images = generateCustomOrderImages($description);
        error_log('Generated ' . count($pixabay_images) . ' images');
    } catch (Exception $e) {
        error_log('AI image generation error: ' . $e->getMessage());
        // Không throw - vẫn trả về thành công nhưng có thể không có ảnh
    }
    
    // Trả về thành công - Bước 1 hoàn thành, chuyển sang Step 2
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Yêu cầu của bạn đã được nhận! Đang tìm kiếm ảnh gợi ý...',
        'step' => 'step2_review_images',
        'custom_order_id' => $custom_order_id,
        'images' => $pixabay_images,
        'customer_email_sent' => $customer_email_sent
    ]);
    exit;
    
} catch (Exception $e) {
    
    // Clean output buffer - loại bỏ bất kỳ output HTML nào
    ob_clean();
    
    // Log lỗi
    error_log('Custom Order API Error: ' . $e->getMessage());
    
    // Trả về lỗi dưới dạng JSON
    $code = intval($e->getCode()) ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

// Fallback - không bao giờ đạt được đây
ob_clean();
http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Lỗi không xác định. Vui lòng thử lại.'
]);
?>
