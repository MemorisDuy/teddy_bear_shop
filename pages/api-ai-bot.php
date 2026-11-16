<?php
/*
 * TEDDY AI CHATBOT API - FINAL WORKING VERSION
 * File: pages/api-ai-bot.php
 * Version: 4.0 - Guaranteed to work with multiple fallbacks
 */

session_start();

// ===== CORS & HEADERS =====
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../config/config.php';

if (!isset($db) || !$db) {
    echo json_encode(['answer' => '🔧 Hệ thống đang bảo trì, vui lòng thử lại sau ít phút! 😊'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== INPUT VALIDATION =====
$question = isset($_POST['question']) ? trim($_POST['question']) : '';

if ($question === '') {
    echo json_encode(['answer' => 'Bạn muốn Teddy AI tư vấn gì về gấu bông? 🧸✨'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($question, 'UTF-8') > 200) {
    echo json_encode(['answer' => 'Câu hỏi quá dài! Vui lòng rút gọn dưới 200 ký tự để AI hiểu tốt hơn nhé! 😊'], JSON_UNESCAPED_UNICODE);
    exit;
}

$question = htmlspecialchars($question, ENT_QUOTES, 'UTF-8');

// ===== CACHING =====
$cache_key = 'ai_cache_' . md5(strtolower(trim($question)));

if (isset($_SESSION[$cache_key]) && !empty($_SESSION[$cache_key])) {
    $cached_response = $_SESSION[$cache_key];
    $cached_response['cached'] = true;
    echo json_encode($cached_response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== GET CONTEXT DATA =====
$product_context = '';
$promo_context = '';

try {
    // Lấy 5 sản phẩm mới nhất
    $latest_products = $db->fetchAll("SELECT name, price, sale_price, color, size FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
    // Lấy sản phẩm nổi bật (nếu muốn dùng)
    // $featured_products = $db->fetchAll("SELECT name, price, sale_price, color, size FROM products WHERE status = 'active' AND is_featured = 1 ORDER BY created_at DESC LIMIT 5");
    // Get promotions
    $now = date('Y-m-d');
    $active_coupon = $db->fetch("SELECT code, type, value, minimum_amount FROM coupons WHERE status = 'active' AND start_date <= ? AND end_date >= ? ORDER BY value DESC LIMIT 1", [$now, $now]);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $latest_products = [];
    $active_coupon = null;
}

// ===== GEMINI API - PRIORITY FIRST =====
// Try Gemini API first for better AI responses
$gemini_api_key = 'AIzaSyCe-hID7G0JiK4zgpzFRFuDU1LWUP8Ai0g';
$response_text = '';
$response_source = 'gemini_ai';

try {
    // Build context from database
    $context_info = '';
    if (!empty($latest_products)) {
        $context_info .= "\n\nSản phẩm hiện có:\n";
        foreach (array_slice($latest_products, 0, 5) as $p) {
            $price = !empty($p['sale_price']) ? $p['sale_price'] : $p['price'];
            $context_info .= "- " . $p['name'] . " (Giá: " . number_format($price, 0, ',', '.') . "đ)\n";
        }
    }
    
    if ($active_coupon) {
        $discount = ($active_coupon['type'] === 'percentage') ? $active_coupon['value'].'%' : number_format($active_coupon['value']).'đ';
        $context_info .= "\nKhuyến mãi: Mã {$active_coupon['code']} - Giảm {$discount}\n";
    }
    
    $gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_api_key;
    
    $system_prompt = "Bạn là Teddy AI - trợ lý AI chuyên nghiệp của shop gấu bông Teddy Bear Paradise (Việt Nam). 
📊 KIẾN THỨC VỀ SẢN PHẨM:
• Gấu Teddy: size S/M/L/XL (20cm-1m2), màu nâu/hồng/trắng, giá 80k-350k, chất liệu bông cao cấp, mềm mại an toàn
• Gấu Panda: đen trắng, biểu cảm dễ thương, size 60-80cm, giá 200-280k, phù hợp làm quà
• Gấu Khổng Lồ: size 1m+, rất ấn tượng, giá 320-500k, tặng người yêu/bạn bè tuyệt vời
• Gấu Đặc Biệt: limited edition, hình dáng độc đáo, nhiều dạng Koala/Cừu/Mèo...
🏪 THÔNG TIN SHOP:
- Hotline: 0358591058 | Zalo: 0358591058
- Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM
- Giờ mở: 8:00-22:00 hàng ngày
- Giao hàng: Toàn quốc, COD, miễn phí từ 200k
- Bảo hành: 100% chất lượng, hoàn tiền nếu không hài lòng
💡 PHONG CÁCH TRẢ LỜI:
✅ Thân thiện, nhiệt tình, sử dụng emoji phù hợp
✅ Trả lời ngắn gọn (50-120 từ), rõ ràng, có cấu trúc
✅ Luôn cố gắng tư vấn sản phẩm phù hợp với nhu cầu
✅ Nêu giá, kích thước, màu sắc cụ thể
✅ Gợi ý các sản phẩm phổ biến khi khách chưa chắc chắn
🎯 KHI NÀO HƯỚNG DẪN LIÊN HỆ SHOP (QR Code):
→ Yêu cầu may đo/chỉnh sửa đặc biệt
→ Hỏi về đơn hàng, ship, thanh toán phức tạp
→ Cần tư vấn kỹ từ chủ shop (tặng theo dịp, sự kiện riêng)
→ Muốn xem hàng trực tiếp, ghé shop
→ Hỏi về collaborations, custom design
" . $context_info . "\n\nCâu hỏi của khách hàng: " . $question;
    
    $data = [
        'contents' => [['parts' => [['text' => $system_prompt]]]],
        'generationConfig' => [
            'temperature' => 0.8,
            'maxOutputTokens' => 200,
            'topP' => 0.9,
            'topK' => 40
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
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
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code === 200 && !empty($result)) {
        $response_data = json_decode($result, true);
        if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            $gemini_response = trim($response_data['candidates'][0]['content']['parts'][0]['text']);
            if (!empty($gemini_response) && strlen($gemini_response) > 10) {
                $response_text = $gemini_response;
                $response_source = 'gemini_ai';
            }
        }
    } else {
        // Log error for debugging
        error_log("Gemini API error: HTTP $http_code - " . ($curl_error ?: substr($result, 0, 200)));
    }
} catch (Exception $e) {
    // Gemini failed, will use intelligent engine fallback
    error_log("Gemini API exception: " . $e->getMessage());
}

// ===== INTELLIGENT RESPONSE ENGINE (FALLBACK) =====
// Only use if Gemini didn't provide a response
if (empty($response_text)) {
$keywords = mb_strtolower($question, 'UTF-8');
$response_source = 'intelligent_engine';

// CHECK IF NEED TO REFER TO SHOP OWNER
$need_shop_contact = false;
if (preg_match('/(may đo|chỉnh sửa|tùy chỉnh|custom|design riêng|hỏi chủ|chủ shop|gặp trực tiếp|xem hàng|tặng theo|sự kiện|event|đơn hàng|ship|thanh toán|vay|trả góp)/ui', $keywords)) {
    $need_shop_contact = true;
}

if ($need_shop_contact) {
    $response_text = "💬 <strong>Câu hỏi này cần tư vấn riêng từ chủ shop!</strong><br>👨‍💼 <strong>Hãy liên hệ trực tiếp:</strong><br>📱 <strong>Hotline:</strong> 0358591058<br>💬 <strong>Zalo:</strong> 0358591058<br>🏪 <strong>Ghé shop:</strong> 123 Đường ABC, Quận XYZ, TP.HCM<br>🕒 <strong>Giờ mở:</strong> 8:00-22:00 hàng ngày<br>✨ Chủ shop sẽ tư vấn chi tiết và tìm giải pháp tốt nhất cho bạn!";
}

// 1. Greetings
elseif (preg_match('/^(hi|hello|xin chào|chào|hey|lolo|alo)/ui', $keywords)) {
    $greetings = [
        "Chào bạn! 🤖 Tôi là Teddy AI của Teddy Bear Paradise! Shop có nhiều gấu bông xinh xắn: Teddy nâu (120k), Panda đen trắng (200k), Gấu khổng lồ 1m2 (320k). Bạn muốn loại nào? 🧸",
        "Xin chào! 🧸 Tôi là trợ lý AI của shop gấu bông Teddy Bear Paradise! Chúng tôi có đầy đủ các loại gấu bông từ nhỏ đến lớn, nhiều màu sắc xinh xắn. Bạn cần tư vấn gì không? 💝",
        "Hello! 👋 Teddy AI ở đây! Shop có rất nhiều gấu bông dễ thương: size S/M/L/XL, màu hồng/nâu/trắng/xanh, giá từ 120k-350k. Bạn thích loại nào? 🎯"
    ];
    $response_text = $greetings[array_rand($greetings)];
}

// 2. Questions about AI
elseif (preg_match('/(bạn|cậu|ai|gì|như nào|thế nào)/ui', $keywords)) {
    $about_responses = [
        "Tôi là Teddy AI - trợ lý thông minh của shop gấu bông Teddy Bear Paradise! 🤖 Tôi có thể tư vấn sản phẩm, giá cả, khuyến mãi, thông tin liên hệ. Bạn muốn hỏi gì? 💝",
        "Tôi là AI chuyên tư vấn gấu bông! 🧸 Có thể giúp bạn chọn gấu phù hợp theo màu sắc, kích thước, dịp tặng. Shop có đầy đủ từ gấu mini đến khổng lồ, giá tốt nhất! 🎁",
        "Chào bạn! Tôi là Teddy AI - chuyên gia tư vấn gấu bông của Teddy Bear Paradise! 🤖 Có thể giúp bạn tìm gấu bông hoàn hảo cho mọi dịp. Bạn cần gấu cho ai? 💖"
    ];
    $response_text = $about_responses[array_rand($about_responses)];
}

// 3. Product price inquiries
elseif (preg_match('/(giá|bao nhiêu|price|cost|tiền|sản phẩm mới|có gì mới|mẫu mới|gấu|teddy|panda)/ui', $keywords)) {
    if (!empty($latest_products)) {
        $response_text = "🧸 <strong>5 sản phẩm mới nhất tại Teddy Bear Paradise:</strong><br>";
        foreach ($latest_products as $p) {
            $gia = !empty($p['sale_price']) ? '<span style=\'color:#e91e63;font-weight:bold;\'>' . number_format($p['sale_price'], 0, ',', '.') . 'đ</span> <del>' . number_format($p['price'], 0, ',', '.') . 'đ</del>' : '<span style=\'color:#e91e63;font-weight:bold;\'>' . number_format($p['price'], 0, ',', '.') . 'đ</span>';
            $response_text .= '• <strong>' . htmlspecialchars($p['name']) . '</strong> (' . ($p['size'] ?: '-') . ', ' . ($p['color'] ?: '-') . '): ' . $gia . '<br>';
        }
        $response_text .= '🎁 Xem thêm tất cả sản phẩm tại <a href=\'' . SITE_URL . '/pages/products.php\' target=\'_blank\'>Sản phẩm mới</a> hoặc gọi 0901.234.567 để tư vấn!';
    } else {
        $response_text = "Hiện tại shop đang cập nhật sản phẩm mới, bạn vui lòng quay lại sau nhé!";
    }
}

// 4. Specific product inquiries
elseif (preg_match('/(panda|teddy|gấu)/ui', $keywords)) {
    if (preg_match('/panda/ui', $keywords)) {
        $response_text = "🐼 <strong>Gấu Panda Đen Trắng đặc biệt:</strong><br>💝 Giá: <span style='color:#e91e63;font-weight:bold;'>200,000đ</span> <del>220,000đ</del><br>🎨 Màu: Đen Trắng cổ điển<br>📏 Size: L (60-70cm)<br>✨ Chất liệu: Bông cao cấp, mềm mại<br>🎁 Biểu cảm dễ thương, phù hợp làm quà!<br>📞 Đặt ngay: 0901.234.567";
    } elseif (preg_match('/size.*l|l.*size|lớn/ui', $keywords)) {
        $response_text = "📏 <strong>Các mẫu gấu bông size L:</strong><br>🐼 Gấu Panda L (60cm): 200k<br>🧸 Gấu Teddy L (65cm): 180k<br>🎯 Gấu Khổng Lồ XL (1m2): 320k<br>💝 Tất cả đều có giảm giá và bảo hành!<br>📞 Xem trực tiếp: 0901.234.567";
    } else {
        $response_text = "🧸 <strong>Gấu bông Teddy Bear Paradise:</strong><br>• Gấu Teddy Nâu: 120k (size M, chất liệu cao cấp)<br>• Gấu Panda: 200k (size L, màu đen trắng)<br>• Gấu Khổng Lồ: 320k (size XL, 1m2)<br>🎨 Nhiều màu: hồng, nâu, trắng, xanh<br>📞 Tư vấn: 0901.234.567";
    }
}

// 5. Size inquiries
elseif (preg_match('/(size|kích thước|to|nhỏ|lớn)/ui', $keywords)) {
    if (preg_match('/nhỏ|s|mini/ui', $keywords)) {
        $response_text = "🧸 <strong>Gấu bông size nhỏ (S):</strong><br>📏 Kích thước: 20-30cm<br>💰 Giá: 80k-120k<br>🎯 Phù hợp: Để bàn, quà tặng nhỏ xinh<br>🎨 Có đủ màu: hồng, nâu, trắng, xanh<br>💝 Rất đáng yêu và tiết kiệm!<br>📞 Đặt ngay: 0901.234.567";
    } elseif (preg_match('/lớn|l|xl|khổng lồ/ui', $keywords)) {
        $response_text = "🎯 <strong>Gấu bông size lớn:</strong><br>📏 Size L (60-80cm): 200k-280k<br>📏 Size XL (1m+): 320k-500k<br>🏆 Đặc biệt: Gấu Khổng Lồ 1m2 chỉ 320k!<br>💝 Quà tặng ấn tượng, chất lượng cao<br>📞 Xem ngay: 0901.234.567";
    } else {
        $response_text = "📏 <strong>Bảng size gấu bông:</strong><br>• Size S (20-30cm): 80k-120k<br>• Size M (40-50cm): 120k-200k<br>• Size L (60-80cm): 200k-280k<br>• Size XL (1m+): 320k-500k<br>🎯 Mỗi size đều có nhiều mẫu xinh!<br>📞 Tư vấn: 0901.234.567";
    }
}

// 6. Contact information
elseif (preg_match('/(liên hệ|contact|địa chỉ|phone|điện thoại|zalo|ở đâu)/ui', $keywords)) {
    $response_text = "📞 <strong>Thông tin liên hệ Teddy Bear Paradise:</strong><br>🏪 <strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM<br>📱 <strong>Hotline:</strong> 0358591058<br>💬 <strong>Zalo:</strong> 0358591058<br>📧 <strong>Email:</strong> info@teddybear.vn<br>🕒 <strong>Giờ mở cửa:</strong> 8:00 - 22:00 hàng ngày<br>🚗 Giao hàng toàn quốc, hỗ trợ COD!";
}

// 7. Promotion inquiries
elseif (preg_match('/(khuyến mãi|giảm|mã|sale|ưu đãi|km)/ui', $keywords)) {
    if ($active_coupon) {
        $discount = ($active_coupon['type'] === 'percentage') ? $active_coupon['value'].'%' : number_format($active_coupon['value']).'đ';
        $response_text = "🎁 <strong>Khuyến mãi đặc biệt:</strong><br>📋 Mã: <code style='background:#ffeb3b;padding:2px 6px;border-radius:4px;'>{$active_coupon['code']}</code><br>💰 Giảm: <span style='color:#e91e63;font-weight:bold;'>{$discount}</span><br>🛒 Đơn tối thiểu: " . number_format($active_coupon['minimum_amount']) . "đ<br>⏰ Nhập mã khi thanh toán để được giảm giá!<br>📞 Hotline: 0901.234.567";
    } else {
        $response_text = "🎁 <strong>Ưu đãi hiện tại:</strong><br>🔥 Mua 2 tặng 1 phụ kiện<br>💝 Giảm giá sốc các mẫu hot<br>🚚 Free ship đơn từ 200k<br>💰 Hỗ trợ trả góp 0% lãi suất<br>📞 Gọi ngay 0901.234.567 để biết thêm ưu đãi!";
    }
}

// 8. Default comprehensive response
else {
    $default_responses = [
        "🧸 <strong>Teddy Bear Paradise - Shop gấu bông #1 Việt Nam!</strong><br>✨ <strong>Sản phẩm hot:</strong> Teddy nâu (120k), Panda (200k), Khổng lồ 1m (320k)<br>🎨 <strong>Đa dạng:</strong> Hơn 50 mẫu, size S/M/L/XL, 10+ màu sắc<br>💝 <strong>Ưu đãi:</strong> Giảm giá, miễn phí ship từ 200k, bảo hành 100%<br>📞 <strong>Chat:</strong> 0358591058 (Zalo/Hotline) | Mở 8h-22h hàng ngày",
        
        "💖 <strong>Teddy Bear Paradise - Gấu bông dễ thương đắt hàng!</strong><br>🏆 <strong>Bestseller Top 3:</strong> Gấu Panda đen trắng, Teddy nâu, Khổng lồ XL<br>🎁 <strong>Mục đích:</strong> Tặng người yêu, bạn, bé nhỏ, trang trí phòng<br>💰 <strong>Giá sốc:</strong> Từ 80k-500k, có sale lên đến 30%<br>👥 <strong>Tư vấn FREE:</strong> 0358591058 (Zalo/Hotline)",
        
        "🤖 <strong>Teddy AI sẵn sàng tư vấn chi tiết!</strong><br>❓ <strong>Hỏi về:</strong> Sản phẩm, giá cả, size, màu sắc, khuyến mãi, ship<br>🎯 <strong>Ví dụ:</strong> 'gấu panda bao nhiêu tiền?', 'size nào tốt?', 'có giảm giá không?'<br>🔥 <strong>Hot nhất:</strong> Gấu 1m chỉ 320k, gấu Panda 200k (siêu hot)<br>📱 <strong>Cần tư vấn sâu:</strong> Gọi 0358591058",
        
        "✨ <strong>Chào mừng bạn đến Teddy Bear Paradise!</strong><br>🧸 <strong>Shop chuyên:</strong> Gấu bông cao cấp, mềm mại, an toàn cho bé<br>🛍️ <strong>Những gì bạn tìm?</strong><br>- Gấu tặng người yêu? → Khuyên Panda hoặc Teddy khổng lồ<br>- Gấu cho bé? → Size S/M an toàn, đủ màu<br>- Budget hạn? → Có từ 80k<br>📲 Chat ngay: 0358591058"
    ];
    $response_text = $default_responses[array_rand($default_responses)];
}
} // End of intelligent engine fallback block

// ===== FINAL RESPONSE =====
$start_time = microtime(true);
$response_time = (microtime(true) - $start_time) * 1000;

$final_response = [
    'answer' => $response_text,
    'response_time' => round($response_time, 2),
    'source' => $response_source,
    'powered_by' => ($response_source === 'gemini_ai') ? 'Google Gemini' : 'Intelligent Engine'
];

// Cache the response
$_SESSION[$cache_key] = $final_response;

echo json_encode($final_response, JSON_UNESCAPED_UNICODE);
?>