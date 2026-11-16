<?php
/**
 * Create Payment Link - Tạo link thanh toán công khai
 * Sử dụng service bên thứ 3 hoặc tạo link demo
 */

session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

$order_number = $_GET['order_number'] ?? '';
$payment_method = $_GET['method'] ?? 'momo';

if (empty($order_number)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
    exit();
}

// Tạo token
$token = md5($order_number . $payment_method . 'teddy_bear_secret_key');

// Option 1: Sử dụng GitHub Pages hoặc Netlify (cần deploy file payment-demo.html)
// $demo_url = 'https://your-username.github.io/teddy-payment-demo/payment-demo.html';

// Option 2: Sử dụng một service URL shortener có API (như bit.ly, tinyurl)
// Nhưng cần API key

// Option 3: Tạo link localhost với hướng dẫn (tạm thời)
// Hoặc sử dụng ngrok để tạo public URL

// Option 4: Sử dụng một trang demo công khai đơn giản
// Tạo link với parameters

// Lấy base URL của trang demo
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname(dirname($_SERVER['SCRIPT_NAME']));

// Tạo link đến trang demo (có thể host trên GitHub Pages)
// Hoặc sử dụng một service như:
// - GitHub Pages: https://username.github.io/repo/payment-demo.html
// - Netlify: https://your-site.netlify.app/payment-demo.html
// - Vercel: https://your-site.vercel.app/payment-demo.html

// Tạm thời dùng localhost với hướng dẫn, hoặc tạo file HTML tĩnh
$demo_url = $protocol . '://' . $host . $script_path . '/cart/payment-demo.html';

// Tạo URL với parameters
$payment_url = $demo_url . '?order=' . urlencode($order_number) . 
               '&method=' . urlencode($payment_method) . 
               '&token=' . $token;

// Nếu muốn dùng service bên thứ 3, có thể tạo short link ở đây
// Ví dụ: sử dụng API của bit.ly hoặc tinyurl

echo json_encode([
    'success' => true,
    'payment_url' => $payment_url,
    'order_number' => $order_number,
    'payment_method' => $payment_method
], JSON_UNESCAPED_UNICODE);


