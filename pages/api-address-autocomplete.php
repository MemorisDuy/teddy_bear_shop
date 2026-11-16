<?php
/**
 * ADDRESS AUTO-COMPLETE API
 * File: pages/api-address-autocomplete.php
 * Kết hợp Goong Maps + Gemini AI để auto-complete địa chỉ
 */

session_start();

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/config.php';

// ===== API KEYS =====
$goong_api_key = "m3E3F2plTUfkXsD9EmLB4hNXax9J3JfhKlbBNwJ9";
$gemini_api_key = "AIzaSyCe-hID7G0JiK4zgpzFRFuDU1LWUP8Ai0g";

// ===== INPUT VALIDATION =====
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || mb_strlen($query, 'UTF-8') < 2) {
    echo json_encode(['results' => []], JSON_UNESCAPED_UNICODE);
    exit();
}

// ===== GOONG MAPS AUTO-COMPLETE =====
function getAddressSuggestions($query, $api_key) {
    $url = "https://rsapi.goong.io/geocode?address=" . urlencode($query) . "&api_key=" . $api_key;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && !empty($result)) {
        $data = json_decode($result, true);
        
        if (isset($data['results']) && is_array($data['results'])) {
            $suggestions = [];
            
            foreach (array_slice($data['results'], 0, 5) as $result) {
                $address = $result['formatted_address'] ?? $result['name'] ?? '';
                
                if (!empty($address)) {
                    $suggestions[] = [
                        'address' => $address,
                        'lat' => $result['geometry']['location']['lat'] ?? null,
                        'lng' => $result['geometry']['location']['lng'] ?? null,
                        'full_address' => $address
                    ];
                }
            }
            
            return $suggestions;
        }
    }
    
    return [];
}

// ===== GEMINI AI - NORMALIZE & ENHANCE ADDRESS =====
function normalizeAddressWithGemini($address, $api_key) {
    
    $system_prompt = "Bạn là chuyên gia xử lý địa chỉ Việt Nam. 
Nhiệm vụ: Chuẩn hóa và làm phong phú thông tin địa chỉ.
Trả về JSON với các trường:
{
  'province': 'Tên tỉnh/thành phố chính xác (64 hoặc 34 tỉnh thành mới)',
  'district': 'Quận/Huyện',
  'ward': 'Phường/Xã',
  'street': 'Số nhà, tên đường',
  'is_new_format': true/false (true nếu là 34 tỉnh thành mới)
}";
    
    $user_message = "Chuẩn hóa địa chỉ này: $address";
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
    
    $data = [
        'contents' => [[
            'parts' => [
                ['text' => $system_prompt . "\n\n" . $user_message]
            ]
        ]],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 200,
            'topP' => 0.9,
            'topK' => 40
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && !empty($result)) {
        $response_data = json_decode($result, true);
        
        if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $response_data['candidates'][0]['content']['parts'][0]['text'];
            
            // Parse JSON từ Gemini response
            $json_match = [];
            if (preg_match('/\{.*\}/s', $text, $json_match)) {
                $parsed = json_decode($json_match[0], true);
                
                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        }
    }
    
    return null;
}

// ===== MAIN LOGIC =====
try {
    // Lấy gợi ý từ Goong Maps
    $suggestions = getAddressSuggestions($query, $goong_api_key);
    
    if (empty($suggestions)) {
        echo json_encode(['results' => []], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Enhance mỗi gợi ý với Gemini AI
    $enhanced_results = [];
    
    foreach ($suggestions as $suggestion) {
        $normalized = normalizeAddressWithGemini($suggestion['address'], $gemini_api_key);
        
        $item = [
            'address' => $suggestion['address'],
            'full_address' => $suggestion['full_address'],
            'lat' => $suggestion['lat'],
            'lng' => $suggestion['lng'],
            'normalized' => $normalized ? [
                'province' => $normalized['province'] ?? $suggestion['address'],
                'district' => $normalized['district'] ?? '',
                'ward' => $normalized['ward'] ?? '',
                'street' => $normalized['street'] ?? '',
                'is_new_format' => $normalized['is_new_format'] ?? false
            ] : null
        ];
        
        $enhanced_results[] = $item;
    }
    
    echo json_encode([
        'results' => $enhanced_results,
        'count' => count($enhanced_results)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Address autocomplete error: " . $e->getMessage());
    echo json_encode([
        'results' => [],
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
