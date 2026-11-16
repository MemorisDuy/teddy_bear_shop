<?php
/**
 * Mock Payment Page - Thanh toán giả lập
 * Hiển thị QR code và xử lý thanh toán giả lập
 */

session_start();
require_once '../config/config.php';

$page_title = "Thanh toán - Teddy Bear Shop";

// Lấy thông tin đơn hàng từ session hoặc GET
$order_number = $_GET['order_number'] ?? $_SESSION['pending_order_number'] ?? '';

if (empty($order_number)) {
    setFlashMessage('error', 'Không tìm thấy thông tin đơn hàng!');
    header('Location: ../pages/products.php');
    exit();
}

// Xóa session sau khi đã lấy
unset($_SESSION['pending_order_number']);

// Lấy thông tin đơn hàng
$order = $db->fetch(
    "SELECT * FROM orders WHERE order_number = ?",
    [$order_number]
);

if (!$order) {
    setFlashMessage('error', 'Đơn hàng không tồn tại!');
    header('Location: ../pages/products.php');
    exit();
}

// Lấy phương thức thanh toán
$payment_method = $order['payment_method'];

// Tạo URL thanh toán - Sử dụng trang demo công khai (KHÔNG CẦN IP)
// 
// CÁCH SỬ DỤNG:
// 1. Upload file payment-demo.html lên GitHub Pages, Netlify, hoặc Vercel (miễn phí)
// 2. Lấy URL công khai (ví dụ: https://your-username.github.io/teddy-payment-demo/payment-demo.html)
// 3. Thay URL bên dưới bằng URL của bạn
// 4. Xem hướng dẫn chi tiết trong file: cart/README-PAYMENT-DEMO.md
//
// URL TRANG DEMO CÔNG KHAI (đã cấu hình):
$demo_base_url = 'https://thanhtoanthanhcong.netlify.app/';

// Tạo token bảo mật
$payment_token = md5($order_number . $payment_method . 'teddy_bear_secret_key');

// Tạo URL thanh toán với parameters
$auto_pay_url = $demo_base_url . '?order=' . urlencode($order_number) . 
                '&method=' . urlencode($payment_method) . 
                '&token=' . $payment_token;

$ip_config_needed = false; // Không cần IP khi dùng trang demo công khai

// Tạo QR code data giả lập
$qr_data = [
    'cod' => [
        'title' => 'Thanh toán khi nhận hàng (COD)',
        'icon' => '💰',
        'description' => 'Bạn sẽ thanh toán bằng tiền mặt khi nhận hàng',
        'qr_text' => 'COD-' . $order_number,
        'qr_url' => $auto_pay_url,
        'amount' => $order['total_amount']
    ],
    'bank_transfer' => [
        'title' => 'Chuyển khoản ngân hàng',
        'icon' => '🏦',
        'description' => 'Quét mã QR để chuyển khoản tự động',
        'qr_text' => 'BANK-' . $order_number,
        'qr_url' => $auto_pay_url,
        'amount' => $order['total_amount'],
        'bank_info' => [
            'name' => 'Vietcombank',
            'account' => '1234567890',
            'holder' => 'TEDDY BEAR PARADISE'
        ]
    ],
    'momo' => [
        'title' => 'Thanh toán qua Ví MoMo',
        'icon' => '📱',
        'description' => 'Quét mã QR để thanh toán tự động bằng MoMo',
        'qr_text' => 'MOMO-' . $order_number,
        'qr_url' => $auto_pay_url,
        'amount' => $order['total_amount']
    ],
    'zalopay' => [
        'title' => 'Thanh toán qua ZaloPay',
        'icon' => '💙',
        'description' => 'Quét mã QR để thanh toán tự động bằng ZaloPay',
        'qr_text' => 'ZALOPAY-' . $order_number,
        'qr_url' => $auto_pay_url,
        'amount' => $order['total_amount']
    ]
];

$payment_info = $qr_data[$payment_method] ?? $qr_data['cod'];

include '../includes/header.php';
?>

<style>
.payment-container {
    min-height: 80vh;
    padding: 50px 0;
    background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.payment-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
}

.payment-header {
    text-align: center;
    margin-bottom: 30px;
}

.payment-icon {
    font-size: 4rem;
    margin-bottom: 15px;
}

.payment-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.payment-description {
    color: #666;
    font-size: 1rem;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.qr-section {
    text-align: center;
    margin: 30px 0;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 15px;
}

.qr-code-container {
    display: inline-block;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.qr-code-image {
    width: 250px;
    height: 250px;
    border: 2px solid #ddd;
    border-radius: 10px;
}

.amount-display {
    font-size: 2rem;
    font-weight: bold;
    color: #ff6b6b;
    margin: 20px 0;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.order-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.order-info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

.order-info-item:last-child {
    border-bottom: none;
}

.order-info-label {
    color: #666;
    font-weight: 500;
}

.order-info-value {
    color: #333;
    font-weight: 600;
}

.bank-info {
    background: #e3f2fd;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.bank-info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.bank-info-label {
    color: #1976d2;
    font-weight: 500;
}

.bank-info-value {
    color: #333;
    font-weight: 600;
    font-family: 'Courier New', monospace;
}

.payment-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-payment {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-payment:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-payment:active {
    transform: translateY(0);
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 30px;
    font-size: 1rem;
    margin-top: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.btn-cancel:hover {
    background: #5a6268;
}

.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-content {
    background: white;
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.loading-spinner {
    font-size: 3rem;
    margin-bottom: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Countdown Container - Giao diện đẹp */
.payment-countdown-container {
    padding: 40px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    border-radius: 20px;
    margin-bottom: 20px;
    text-align: center;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.payment-countdown-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
    50% { transform: translate(-50%, -50%) rotate(180deg); }
}

.countdown-icon-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 20px;
}

.countdown-icon {
    font-size: 4rem;
    position: relative;
    z-index: 2;
    animation: bounce 2s ease-in-out infinite;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}

@keyframes bounce {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-10px) scale(1.05); }
}

.pulse-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100px;
    height: 100px;
    border: 3px solid rgba(255,255,255,0.6);
    border-radius: 50%;
    animation: pulse 2s ease-out infinite;
}

.pulse-ring.delay-1 {
    animation-delay: 1s;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
}

.countdown-title {
    color: white;
    margin-bottom: 20px;
    font-size: 1.5rem;
    font-weight: 600;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.countdown-number {
    color: white;
    font-size: 4.5rem;
    font-weight: 800;
    margin: 15px 0;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
    animation: numberPulse 1s ease-in-out infinite;
    letter-spacing: 2px;
}

@keyframes numberPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.countdown-label {
    color: rgba(255,255,255,0.9);
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 25px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    overflow: hidden;
    margin: 20px 0;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #fff, #f0f0f0, #fff);
    background-size: 200% 100%;
    border-radius: 10px;
    width: 0%;
    transition: width 1s linear;
    animation: progressShine 2s linear infinite;
    box-shadow: 0 2px 10px rgba(255,255,255,0.5);
}

@keyframes progressShine {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.countdown-description {
    color: rgba(255,255,255,0.95);
    margin: 20px 0 0 0;
    font-size: 0.95rem;
    text-shadow: 0 1px 5px rgba(0,0,0,0.2);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
    line-height: 1.6;
}
</style>

<div class="payment-container">
    <div class="container">
        <div class="payment-card">
            <div class="payment-header">
                <div class="payment-icon"><?php echo $payment_info['icon']; ?></div>
                <h1 class="payment-title"><?php echo $payment_info['title']; ?></h1>
                <p class="payment-description"><?php echo $payment_info['description']; ?></p>
            </div>

            <div class="amount-display">
                <?php echo formatPrice($payment_info['amount']); ?>
            </div>

            <?php if ($payment_method !== 'cod'): ?>
            <div class="qr-section">
                <h4 style="margin-bottom: 20px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">Quét mã QR để thanh toán tự động</h4>
                
                <!-- Countdown tự động giả lập quét QR (10 giây) - Giao diện đẹp -->
                <div id="autoCountdown" class="payment-countdown-container">
                    <div class="countdown-icon-wrapper">
                        <div class="countdown-icon">📱</div>
                        <div class="pulse-ring"></div>
                        <div class="pulse-ring delay-1"></div>
                    </div>
                    <h3 class="countdown-title">Đang xử lý thanh toán...</h3>
                    <div id="countdownText" class="countdown-number">
                        10
                    </div>
                    <div class="countdown-label">giây</div>
                    <div class="progress-bar-container">
                        <div id="progressBar" class="progress-bar"></div>
                    </div>
                    <p class="countdown-description">
                        Đang giả lập quét QR code và xử lý thanh toán...
                    </p>
                </div>
                
                <?php if ($ip_config_needed): ?>
                <!-- Thông báo cần cấu hình IP -->
                <div style="padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; margin-bottom: 20px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <h5 style="color: #856404; margin-bottom: 15px;">
                        <span style="font-size: 1.5rem; margin-right: 8px;">⚠️</span> Cần cấu hình IP để quét QR
                    </h5>
                    <p style="color: #856404; margin-bottom: 15px;">
                        Để quét QR code trên điện thoại, bạn cần thay <code>localhost</code> bằng IP thật của máy tính.
                    </p>
                    <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <strong style="color: #333;">Cách lấy IP:</strong>
                        <ol style="margin: 10px 0; padding-left: 20px; color: #333;">
                            <li>Mở Command Prompt (Windows) hoặc Terminal (Mac/Linux)</li>
                            <li>Gõ lệnh: <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px;">ipconfig</code> (Windows) hoặc <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px;">ifconfig</code> (Mac/Linux)</li>
                            <li>Tìm dòng "IPv4 Address" (ví dụ: 192.168.1.100)</li>
                            <li>Sửa file <code>config/config.php</code>, thay <code>localhost</code> bằng IP vừa tìm được</li>
                        </ol>
                    </div>
                    <p style="color: #856404; font-size: 0.9rem; margin: 0;">
                        Ví dụ: <code>http://192.168.1.100/teddy_bear_shop</code>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="qr-code-container">
                    <!-- QR Code thật - có thể quét được -->
                    <img src="qr-generator.php?data=<?php echo urlencode($payment_info['qr_url']); ?>&size=300" 
                         alt="QR Code" 
                         class="qr-code-image"
                         style="width: 300px; height: 300px; border: 2px solid #ddd; border-radius: 10px; background: white;"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'300\'%3E%3Crect width=\'300\' height=\'300\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'20\' fill=\'%23999\'%3EQR Code%3C/text%3E%3C/svg%3E';">
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #e3f2fd; border-radius: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <p style="color: #1976d2; margin-bottom: 10px; font-weight: 600;">
                        <span style="font-size: 1.2rem; margin-right: 8px;">📱</span> Hướng dẫn quét QR:
                    </p>
                    <ol style="color: #333; margin: 0; padding-left: 20px; text-align: left;">
                        <li>Mở ứng dụng Camera trên điện thoại</li>
                        <li>Hướng camera vào mã QR ở trên</li>
                        <li>Nhấn vào thông báo/liên kết hiện ra</li>
                        <li>Hệ thống sẽ tự động xử lý thanh toán</li>
                    </ol>
                </div>
                <p style="color: #28a745; margin-top: 15px; font-size: 0.9rem; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    ✅ Hoặc nhấn nút "Đã thanh toán thành công" bên dưới để thanh toán thủ công
                </p>
                <!-- Debug info (chỉ hiện khi cần) -->
                <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                <details style="margin-top: 15px; font-size: 0.8rem; color: #666;">
                    <summary style="cursor: pointer;">🔍 Debug Info</summary>
                    <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: left;">
                        <strong>QR URL:</strong><br>
                        <code style="word-break: break-all; font-size: 0.75rem;"><?php echo htmlspecialchars($payment_info['qr_url']); ?></code>
                    </div>
                </details>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($payment_method === 'bank_transfer'): ?>
            <div class="bank-info">
                <h5 style="margin-bottom: 15px; color: #1976d2; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">📋 Thông tin chuyển khoản</h5>
                <div class="bank-info-item">
                    <span class="bank-info-label">Ngân hàng:</span>
                    <span class="bank-info-value"><?php echo $payment_info['bank_info']['name']; ?></span>
                </div>
                <div class="bank-info-item">
                    <span class="bank-info-label">Số tài khoản:</span>
                    <span class="bank-info-value"><?php echo $payment_info['bank_info']['account']; ?></span>
                </div>
                <div class="bank-info-item">
                    <span class="bank-info-label">Chủ tài khoản:</span>
                    <span class="bank-info-value"><?php echo $payment_info['bank_info']['holder']; ?></span>
                </div>
                <div class="bank-info-item">
                    <span class="bank-info-label">Nội dung:</span>
                    <span class="bank-info-value"><?php echo $order_number; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="order-info">
                <h5 style="margin-bottom: 15px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">📦 Thông tin đơn hàng</h5>
                <div class="order-info-item">
                    <span class="order-info-label">Mã đơn hàng:</span>
                    <span class="order-info-value">#<?php echo $order_number; ?></span>
                </div>
                <div class="order-info-item">
                    <span class="order-info-label">Tạm tính:</span>
                    <span class="order-info-value"><?php echo formatPrice($order['subtotal']); ?></span>
                </div>
                <?php if ($order['shipping_fee'] > 0): ?>
                <div class="order-info-item">
                    <span class="order-info-label">Phí vận chuyển:</span>
                    <span class="order-info-value"><?php echo formatPrice($order['shipping_fee']); ?></span>
                </div>
                <?php else: ?>
                <div class="order-info-item">
                    <span class="order-info-label">Phí vận chuyển:</span>
                    <span class="order-info-value" style="color: #28a745;">Miễn phí</span>
                </div>
                <?php endif; ?>
                <div class="order-info-item">
                    <span class="order-info-label">Tổng cộng:</span>
                    <span class="order-info-value" style="color: #ff6b6b; font-size: 1.2rem;"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>

            <div class="payment-actions">
                <?php if ($payment_method === 'cod'): ?>
                    <button class="btn-payment" onclick="confirmPayment()">
                        <span style="font-size: 1.2rem; margin-right: 8px;">✅</span> Xác nhận đặt hàng
                    </button>
                <?php else: ?>
                    <button class="btn-payment" onclick="confirmPayment()">
                        <span style="font-size: 1.2rem; margin-right: 8px;">✅</span> Đã thanh toán thành công
                    </button>
                <?php endif; ?>
                <br>
                <button class="btn-cancel" onclick="cancelPayment()">
                    <span style="font-size: 1rem; margin-right: 5px;">❌</span> Hủy thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Status Alert -->
<div id="paymentStatusAlert" style="display: none; position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 10000; max-width: 400px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 2rem;">✅</div>
        <div>
            <h4 style="margin: 0 0 5px 0; font-size: 1.2rem;">Thanh toán thành công!</h4>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Đơn hàng <strong id="alertOrderNumber"><?php echo $order_number; ?></strong> đã được thanh toán.</p>
        </div>
    </div>
    <button onclick="window.location.reload()" style="margin-top: 15px; padding: 8px 20px; background: white; color: #28a745; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">
        Xem chi tiết
    </button>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner">⏳</div>
        <h4 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">Đang xử lý thanh toán...</h4>
        <p style="color: #666; margin-top: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">Vui lòng đợi trong giây lát</p>
    </div>
</div>

<script>
function confirmPayment() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'flex';
    
    // Gọi API xử lý thanh toán giả lập
    fetch('process-mock-payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_number: '<?php echo $order_number; ?>',
            payment_method: '<?php echo $payment_method; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            setTimeout(() => {
                window.location.href = 'order-success.php?order_number=<?php echo $order_number; ?>&paid=1';
            }, 1500);
        } else {
            loadingOverlay.style.display = 'none';
            alert('❌ ' + (data.message || 'Có lỗi xảy ra khi xử lý thanh toán'));
        }
    })
    .catch(error => {
        loadingOverlay.style.display = 'none';
        console.error('Error:', error);
        alert('❌ Có lỗi xảy ra. Vui lòng thử lại!');
    });
}

function cancelPayment() {
    if (confirm('Bạn có chắc muốn hủy thanh toán?')) {
        window.location.href = '../cart/index.php';
    }
}

// Tự động giả lập quét QR và đếm ngược 10 giây
const orderNumber = '<?php echo $order_number; ?>';
const paymentMethod = '<?php echo $payment_method; ?>';

let countdownSeconds = 10;

function startAutoPayment() {
    const countdownText = document.getElementById('countdownText');
    const countdownDiv = document.getElementById('autoCountdown');
    const progressBar = document.getElementById('progressBar');
    const totalSeconds = 10;
    let currentSeconds = totalSeconds;
    
    // Cập nhật countdown mỗi giây
    const updateCountdown = () => {
        if (currentSeconds > 0) {
            countdownText.textContent = currentSeconds;
            
            // Cập nhật progress bar
            const progress = ((totalSeconds - currentSeconds) / totalSeconds) * 100;
            progressBar.style.width = progress + '%';
            
            currentSeconds--;
            setTimeout(updateCountdown, 1000);
        } else {
            // Sau 10 giây, tự động xử lý thanh toán
            countdownText.textContent = '✅';
            countdownText.style.fontSize = '5rem';
            countdownDiv.querySelector('.countdown-title').textContent = 'Thanh toán thành công!';
            countdownDiv.querySelector('.countdown-description').textContent = 'Đang chuyển đến trang chi tiết đơn hàng...';
            progressBar.style.width = '100%';
            progressBar.style.background = 'linear-gradient(90deg, #28a745, #20c997)';
            
            // Thay đổi màu nền
            countdownDiv.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            
            // Gọi API để xử lý thanh toán
            fetch('process-mock-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_number: orderNumber,
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                // Redirect đến trang thành công sau 1.5 giây
                setTimeout(() => {
                    window.location.href = 'order-success.php?order_number=' + orderNumber + '&paid=1';
                }, 1500);
            })
            .catch(error => {
                // Nếu có lỗi, vẫn redirect
                console.error('Error processing payment:', error);
                setTimeout(() => {
                    window.location.href = 'order-success.php?order_number=' + orderNumber + '&paid=1';
                }, 1500);
            });
        }
    };
    
    // Bắt đầu countdown ngay khi trang load
    updateCountdown();
}

// Bắt đầu tự động khi trang load
document.addEventListener('DOMContentLoaded', function() {
    startAutoPayment();
});

// Dừng check khi user rời trang
window.addEventListener('beforeunload', () => {
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
    }
});
</script>

<?php include '../includes/footer.php'; ?>

