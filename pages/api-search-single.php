<?php
/**
 * Search API - Chỉ trả về JSON
 * FIXED: Tách riêng khỏi search.php
 */

// FIXED: Chặn output HTML
ob_start();

require_once '../config/config.php';
require_once '../includes/functions.php';

// FIXED: Set JSON header ngay từ đầu
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // FIXED: Validate input
    $keyword = '';
    if (isset($_GET['q'])) {
        $keyword = htmlspecialchars(trim($_GET['q']), ENT_QUOTES, 'UTF-8');
        $keyword = strip_tags($keyword);
    }
    
    if ($keyword === '') {
        ob_clean();
        echo json_encode([
            'success' => false, 
            'count' => 0, 
            'message' => 'Từ khóa không được để trống'
        ]);
        exit;
    }
    
    if (strlen($keyword) < 2) {
        ob_clean();
        echo json_encode([
            'success' => false, 
            'count' => 0, 
            'message' => 'Từ khóa phải có ít nhất 2 ký tự'
        ]);
        exit;
    }
    
    // FIXED: Kiểm tra database connection
    if (!isset($db) || !$db) {
        throw new Exception('Database connection failed');
    }
    
    // FIXED: Prepared statement để tránh SQL injection
    $sql = "SELECT id, slug, name, main_image, price, sale_price 
            FROM products 
            WHERE status = 'active' 
            AND (name LIKE ? OR description LIKE ? OR short_description LIKE ?) 
            ORDER BY 
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                END,
                created_at DESC 
            LIMIT 10";
    
    $exact_match = $keyword;
    $starts_with = $keyword . '%';
    $contains = '%' . $keyword . '%';
    
    $params = [
        $contains,      // name LIKE
        $contains,      // description LIKE  
        $contains,      // short_description LIKE
        $exact_match,   // exact match priority
        $starts_with    // starts with priority
    ];
    
    $products = $db->fetchAll($sql, $params);
    
    ob_clean();
    if (count($products) === 1) {
        // FIXED: Trả về thông tin chi tiết cho single result
        $product = $products[0];
        echo json_encode([
            'success' => true,
            'count' => 1, 
            'id' => $product['id'], 
            'slug' => $product['slug'],
            'name' => $product['name'],
            'image' => $product['main_image'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price']
        ]);
    } else {
        // FIXED: Trả về danh sách sản phẩm
        $product_list = [];
        foreach ($products as $product) {
            $product_list[] = [
                'id' => $product['id'],
                'slug' => $product['slug'], 
                'name' => $product['name'],
                'image' => $product['main_image'],
                'price' => $product['price'],
                'sale_price' => $product['sale_price']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'count' => count($products),
            'products' => $product_list,
            'message' => count($products) > 0 ? 'Tìm thấy ' . count($products) . ' sản phẩm' : 'Không tìm thấy sản phẩm nào'
        ]);
    }
    
} catch (Exception $e) {
    // FIXED: Log error và trả về thông báo user-friendly
    error_log('Search API error: ' . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false, 
        'count' => 0, 
        'error_code' => 'SEARCH_ERROR'
    ]);
} finally {
    // Flush output buffer
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}

exit;