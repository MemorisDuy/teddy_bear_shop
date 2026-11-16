<?php
/**
 * AI Search API với Gemini - Tìm kiếm sản phẩm thông minh
 * Sử dụng Google Gemini API để hiểu ý định tìm kiếm và gợi ý sản phẩm
 */

session_start();

// CORS & Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/config.php';

// Gemini API Key
$gemini_api_key = 'AIzaSyCe-hID7G0JiK4zgpzFRFuDU1LWUP8Ai0g';

// Validate input
$query = isset($_POST['q']) ? trim($_POST['q']) : (isset($_GET['q']) ? trim($_GET['q']) : '');

if (empty($query)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập từ khóa tìm kiếm'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($query, 'UTF-8') < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Từ khóa phải có ít nhất 2 ký tự'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Lấy danh sách sản phẩm từ database
    $all_products = [];
    if (isset($db) && $db) {
        $all_products = $db->fetchAll(
            "SELECT id, name, slug, price, sale_price, main_image, short_description, category_id 
             FROM products 
             WHERE status = 'active' 
             ORDER BY created_at DESC 
             LIMIT 50"
        );
    }
    
    // Tạo context cho Gemini
    $products_context = '';
    if (!empty($all_products)) {
        $products_context = "\n\nDanh sách sản phẩm hiện có:\n";
        foreach (array_slice($all_products, 0, 20) as $p) {
            $price = !empty($p['sale_price']) ? $p['sale_price'] : $p['price'];
            $products_context .= "- ID: {$p['id']}, Tên: {$p['name']}, Giá: " . number_format($price, 0, ',', '.') . "đ\n";
        }
    }
    
    // Gọi Gemini API để phân tích query và gợi ý sản phẩm
    $gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_api_key;
    
    $prompt = "Bạn là AI tìm kiếm sản phẩm của shop gấu bông Teddy Bear Paradise.

Nhiệm vụ: Phân tích câu hỏi tìm kiếm của khách hàng và trả về danh sách ID sản phẩm phù hợp nhất.

Câu hỏi khách hàng: \"$query\"
$products_context

Hãy phân tích và trả về JSON với format:
{
  \"keywords\": [\"từ khóa 1\", \"từ khóa 2\"],
  \"suggested_ids\": [1, 2, 3],
  \"reason\": \"Lý do gợi ý ngắn gọn\"
}

Chỉ trả về JSON, không có text khác.";

    $data = [
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 500,
            'topP' => 0.8,
            'topK' => 40
        ]
    ];

    $ch = curl_init($gemini_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE)
    ]);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $suggested_ids = [];
    $ai_keywords = [];
    $ai_reason = '';

    if ($http_code === 200 && !empty($result)) {
        $response_data = json_decode($result, true);
        if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_response = trim($response_data['candidates'][0]['content']['parts'][0]['text']);
            
            // Extract JSON from response
            if (preg_match('/\{[^}]+\}/s', $ai_response, $matches)) {
                $ai_json = json_decode($matches[0], true);
                if ($ai_json) {
                    $suggested_ids = isset($ai_json['suggested_ids']) ? $ai_json['suggested_ids'] : [];
                    $ai_keywords = isset($ai_json['keywords']) ? $ai_json['keywords'] : [];
                    $ai_reason = isset($ai_json['reason']) ? $ai_json['reason'] : '';
                }
            }
        }
    }

    // Nếu Gemini không trả về kết quả, dùng tìm kiếm truyền thống
    if (empty($suggested_ids)) {
        $search_keywords = !empty($ai_keywords) ? $ai_keywords : [$query];
        $search_pattern = '%' . implode('%', $search_keywords) . '%';
        
        $sql = "SELECT id, slug, name, main_image, price, sale_price, short_description 
                FROM products 
                WHERE status = 'active' 
                AND (name LIKE ? OR short_description LIKE ?) 
                ORDER BY 
                    CASE WHEN name LIKE ? THEN 1 ELSE 2 END,
                    created_at DESC 
                LIMIT 10";
        
        $products = $db->fetchAll($sql, [
            $search_pattern,
            $search_pattern,
            '%' . $query . '%'
        ]);
    } else {
        // Lấy sản phẩm theo ID được AI gợi ý
        $placeholders = implode(',', array_fill(0, count($suggested_ids), '?'));
        $sql = "SELECT id, slug, name, main_image, price, sale_price, short_description 
                FROM products 
                WHERE status = 'active' 
                AND id IN ($placeholders)
                ORDER BY FIELD(id, $placeholders)
                LIMIT 10";
        
        $products = $db->fetchAll($sql, array_merge($suggested_ids, $suggested_ids));
    }

    // Format response
    $product_list = [];
    foreach ($products as $product) {
        $product_list[] = [
            'id' => $product['id'],
            'slug' => $product['slug'],
            'name' => $product['name'],
            'image' => $product['main_image'],
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'description' => $product['short_description']
        ];
    }

    // If no products found, get AI suggestions for alternative keywords
    $ai_suggestions = [];
    if (empty($product_list)) {
        try {
            $suggestion_prompt = "Khách hàng tìm kiếm \"$query\" nhưng không có sản phẩm trong shop gấu bông.
            
Danh sách sản phẩm hiện có trong shop:
$products_context

Hãy gợi ý 5-8 từ khóa tìm kiếm tương tự hoặc liên quan mà có thể tìm thấy sản phẩm trong shop.
Trả về JSON format:
{
  \"suggestions\": [\"từ khóa 1\", \"từ khóa 2\", \"từ khóa 3\"],
  \"message\": \"Thông điệp thân thiện ngắn gọn\"
}

Chỉ trả về JSON, không có text khác.";

            $suggestion_data = [
                'contents' => [['parts' => [['text' => $suggestion_prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 300,
                    'topP' => 0.8,
                    'topK' => 40
                ]
            ];

            $ch = curl_init($gemini_url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($suggestion_data, JSON_UNESCAPED_UNICODE)
            ]);

            $suggestion_result = curl_exec($ch);
            $suggestion_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($suggestion_http_code === 200 && !empty($suggestion_result)) {
                $suggestion_response = json_decode($suggestion_result, true);
                if (isset($suggestion_response['candidates'][0]['content']['parts'][0]['text'])) {
                    $suggestion_text = trim($suggestion_response['candidates'][0]['content']['parts'][0]['text']);
                    // Extract JSON from response
                    if (preg_match('/\{[^}]+\}/s', $suggestion_text, $matches)) {
                        $suggestion_json = json_decode($matches[0], true);
                        if ($suggestion_json && isset($suggestion_json['suggestions'])) {
                            $ai_suggestions = $suggestion_json['suggestions'];
                            $ai_reason = isset($suggestion_json['message']) ? $suggestion_json['message'] : 'Gợi ý từ AI';
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log('AI suggestion error: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => count($product_list) > 0,
        'count' => count($product_list),
        'products' => $product_list,
        'ai_suggested' => !empty($suggested_ids),
        'ai_reason' => $ai_reason,
        'keywords' => $ai_keywords,
        'ai_suggestions' => $ai_suggestions, // Gợi ý từ khóa khi không có kết quả
        'no_results_message' => empty($product_list) ? 'Sản phẩm chưa có trong shop. Mời bạn tìm mẫu tương tự hoặc liên hệ shop để đặt hàng theo ý!' : null
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('AI Search API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tìm kiếm',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

