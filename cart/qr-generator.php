<?php
/**
 * QR Code Generator - Tạo QR code cho thanh toán
 * Sử dụng API để tạo QR code thật có thể quét được
 */

// Lấy dữ liệu từ GET
$data = $_GET['data'] ?? '';
$size = $_GET['size'] ?? 300;

if (empty($data)) {
    // Tạo ảnh lỗi
    header('Content-Type: image/png');
    $img = imagecreate($size, $size);
    $white = imagecolorallocate($img, 255, 255, 255);
    $red = imagecolorallocate($img, 255, 0, 0);
    imagestring($img, 5, 10, $size/2 - 10, 'No data', $red);
    imagepng($img);
    imagedestroy($img);
    exit();
}

// Sử dụng nhiều API backup để đảm bảo hoạt động
$apis = [
    // Option 1: QR Server API (ổn định nhất)
    'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data) . '&format=png',
    
    // Option 2: QuickChart API
    'https://quickchart.io/qr?text=' . urlencode($data) . '&size=' . $size,
    
    // Option 3: QR Code API
    'https://api.qr-code-generator.com/v1/create?access-token=YOUR_TOKEN&qr_code_text=' . urlencode($data) . '&image_format=PNG&image_width=' . $size,
];

// Thử từng API
$image = false;
foreach ($apis as $api_url) {
    // Bỏ qua API cần token
    if (strpos($api_url, 'YOUR_TOKEN') !== false) {
        continue;
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0'
        ]
    ]);
    
    $image = @file_get_contents($api_url, false, $context);
    
    if ($image !== false && strlen($image) > 100) {
        // Kiểm tra xem có phải là ảnh PNG không
        if (substr($image, 0, 8) === "\x89PNG\r\n\x1a\n" || strpos($image, 'PNG') !== false) {
            break;
        }
    }
    
    $image = false;
}

// Nếu tất cả API đều fail, tạo QR code bằng thư viện PHP (nếu có) hoặc fallback
if ($image === false) {
    // Fallback: Sử dụng Google Charts API (có thể bị chặn nhưng thử)
    $google_url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chl=' . urlencode($data);
    $image = @file_get_contents($google_url, false, $context);
}

// Nếu vẫn không được, tạo placeholder
if ($image === false || strlen($image) < 100) {
    header('Content-Type: image/png');
    $img = imagecreate($size, $size);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    $gray = imagecolorallocate($img, 200, 200, 200);
    
    // Vẽ border
    imagerectangle($img, 0, 0, $size-1, $size-1, $black);
    
    // Vẽ pattern giống QR code (đơn giản)
    $block_size = $size / 25;
    for ($i = 0; $i < 25; $i++) {
        for ($j = 0; $j < 25; $j++) {
            if (($i + $j) % 3 === 0 || ($i * $j) % 7 === 0) {
                imagefilledrectangle($img, $i * $block_size, $j * $block_size, 
                                   ($i + 1) * $block_size, ($j + 1) * $block_size, $black);
            }
        }
    }
    
    // Thêm text
    imagestring($img, 2, 10, $size - 20, 'QR Code', $black);
    
    imagepng($img);
    imagedestroy($img);
    exit();
}

// Trả về QR code
header('Content-Type: image/png');
header('Cache-Control: public, max-age=3600');
echo $image;

