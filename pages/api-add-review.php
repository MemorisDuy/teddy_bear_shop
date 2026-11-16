<?php
/**
 * API - Add Product Review
 * POST /pages/api-add-review.php
 */

require_once '../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $customer_name = isset($_POST['reviewer_name']) ? htmlspecialchars(trim($_POST['reviewer_name']), ENT_QUOTES, 'UTF-8') : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? htmlspecialchars(trim($_POST['comment']), ENT_QUOTES, 'UTF-8') : '';
    
    // Validate
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => '❌ Sản phẩm không hợp lệ']);
        exit;
    }
    
    if (!$customer_name || strlen($customer_name) < 2) {
        echo json_encode(['success' => false, 'message' => '❌ Tên phải ít nhất 2 ký tự']);
        exit;
    }
    
    if (!$rating || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => '❌ Chọn đánh giá từ 1 đến 5 sao']);
        exit;
    }
    
    if (!$comment || strlen($comment) < 5) {
        echo json_encode(['success' => false, 'message' => '❌ Nội dung phải ít nhất 5 ký tự']);
        exit;
    }
    
    // Check if product exists
    $product = $db->fetch("SELECT id FROM products WHERE id = ?", [$product_id]);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => '❌ Sản phẩm không tồn tại']);
        exit;
    }
    
    // Insert review into database (match existing schema)
    $result = $db->execute(
        "INSERT INTO reviews (product_id, customer_name, rating, comment, status, created_at) 
         VALUES (?, ?, ?, ?, 'pending', NOW())",
        [$product_id, $customer_name, $rating, $comment]
    );
    
    // Debug logging
    error_log("API Review Insert - Product: $product_id, Name: $customer_name, Rating: $rating, Result: " . ($result ? "SUCCESS" : "FAILED"));
    
    if (!$result) {
        $conn = $db->getConnection();
        $error_msg = $conn ? $conn->error : 'Unknown error';
        error_log("Database Error: $error_msg");
        throw new Exception('Failed to save review: ' . $error_msg);
    }
    
    // Verify insert was successful
    $last_id = $db->getLastInsertId();
    error_log("Insert successful with ID: $last_id");
    
    // Double check by fetching the inserted record
    $inserted = $db->fetch("SELECT * FROM reviews WHERE id = ?", [$last_id]);
    if (!$inserted) {
        error_log("WARNING: Record inserted but cannot be fetched immediately. ID: $last_id");
    }
    
    // Update product rating
    if (function_exists('updateProductRating')) {
        updateProductRating($product_id);
    }
    
    echo json_encode([
        'success' => true,
        'message' => '✅ Cảm ơn bạn đã đánh giá! Đánh giá của bạn sẽ được duyệt trong thời gian ngắn.',
        'review' => [
            'customer_name' => $customer_name,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '❌ Lỗi: ' . htmlspecialchars($e->getMessage())
    ]);
}
?>
