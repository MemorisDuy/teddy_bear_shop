<?php
/**
 * Migration: Tạo bảng custom_orders
 * Lưu trữ các yêu cầu đặt hàng theo mẫu riêng từ khách
 */

require_once '../config/config.php';
require_once '../config/Database.php';

try {
    $db = new Database();
    
    // DROP table nếu tồn tại (để có thể chạy lại)
    $db->query("DROP TABLE IF EXISTS custom_orders");
    
    // Tạo bảng mới
    $sql = "CREATE TABLE custom_orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20),
        search_keyword VARCHAR(255) NOT NULL,
        description LONGTEXT,
        image_url VARCHAR(500),
        selected_image_url VARCHAR(500) COMMENT 'Ảnh mà khách chọn sau khi tìm kiếm',
        rating INT DEFAULT 0 COMMENT '0=chưa đánh giá, 1-5=đánh giá sao từ khách',
        status ENUM('waiting_images', 'image_selected', 'confirmed', 'viewed', 'responded', 'ordered', 'rejected') DEFAULT 'waiting_images' COMMENT 'waiting_images=chờ ảnh, image_selected=khách chọn ảnh, confirmed=khách xác nhận, viewed=shop xem, ...',
        shop_message LONGTEXT COMMENT 'Phản hồi từ shop',
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_email (customer_email),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql);
    
    echo "✅ Bảng custom_orders đã được tạo thành công!<br>";
    echo "Các columns: id, customer_name, customer_email, customer_phone, search_keyword, description, image_url, rating, status, shop_message, ip_address, created_at, updated_at<br>";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
