<?php
/**
 * Auto Payment Page - Trang thanh toán tự động khi quét QR
 * Khi user quét QR code, link này sẽ hiển thị trang loading rồi tự động xử lý thanh toán
 */

session_start();
require_once '../config/config.php';

$page_title = "Đang xử lý thanh toán...";

// Lấy thông tin từ GET
$order_number = $_GET['order'] ?? '';
$payment_method = $_GET['method'] ?? '';
$token = $_GET['token'] ?? '';

// Validate token (bảo mật cơ bản)
$expected_token = md5($order_number . $payment_method . 'teddy_bear_secret_key');

if (empty($order_number) || empty($payment_method) || $token !== $expected_token) {
    // Hiển thị trang lỗi
    include '../includes/header.php';
    ?>
    <div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%); font-family: 'Nunito', 'Poppins', Arial, sans-serif;">
        <div style="text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 20px;">❌</div>
            <h2 style="color: #dc3545; margin-bottom: 15px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Link thanh toán không hợp lệ!</h2>
            <p style="color: #666; margin-bottom: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Vui lòng quét lại mã QR hoặc liên hệ hỗ trợ.</p>
            <a href="../pages/products.php" style="display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 10px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Về trang chủ</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; exit();
}

// Lấy thông tin đơn hàng
$order = $db->fetch(
    "SELECT * FROM orders WHERE order_number = ?",
    [$order_number]
);

if (!$order) {
    include '../includes/header.php';
    ?>
    <div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%); font-family: 'Nunito', 'Poppins', Arial, sans-serif;">
        <div style="text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 20px;">❌</div>
            <h2 style="color: #dc3545; margin-bottom: 15px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Đơn hàng không tồn tại!</h2>
            <p style="color: #666; margin-bottom: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Mã đơn hàng: <strong><?php echo htmlspecialchars($order_number); ?></strong></p>
            <a href="../pages/products.php" style="display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 10px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Về trang chủ</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; exit();
}

// Kiểm tra đã thanh toán chưa
if ($order['payment_status'] === 'paid') {
    // Đã thanh toán rồi, hiển thị thông báo và redirect
    include '../includes/header.php';
    ?>
    <div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%); font-family: 'Nunito', 'Poppins', Arial, sans-serif;">
        <div style="text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 20px;">✅</div>
            <h2 style="color: #28a745; margin-bottom: 15px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Đơn hàng đã được thanh toán!</h2>
            <p style="color: #666; margin-bottom: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Đang chuyển đến trang chi tiết đơn hàng...</p>
            <div style="font-size: 2rem; animation: spin 1s linear infinite;">⏳</div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'order-success.php?order_number=<?php echo $order_number; ?>&paid=1';
        }, 2000);
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    <?php include '../includes/footer.php'; exit();
}

// Hiển thị trang loading trước khi xử lý thanh toán
include '../includes/header.php';
?>

<style>
.payment-processing {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%);
    font-family: 'Nunito', 'Poppins', Arial, sans-serif;
}

.processing-card {
    text-align: center;
    background: white;
    padding: 50px 40px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 90%;
}

.processing-icon {
    font-size: 5rem;
    margin-bottom: 20px;
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.processing-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    font-family: 'Nunito', 'Poppins', Arial, sans-serif;
}

.processing-text {
    color: #666;
    font-size: 1rem;
    margin-bottom: 30px;
    font-family: 'Nunito', 'Poppins', Arial, sans-serif;
}

.order-info-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
    font-family: 'Nunito', 'Poppins', Arial, sans-serif;
}

.order-info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
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

.loading-spinner {
    font-size: 3rem;
    animation: spin 1s linear infinite;
    margin: 20px 0;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="payment-processing">
    <div class="processing-card">
        <div class="processing-icon">💳</div>
        <h1 class="processing-title">Đang xử lý thanh toán...</h1>
        <p class="processing-text">Vui lòng đợi trong giây lát</p>
        
        <div class="loading-spinner">⏳</div>
        
        <div class="order-info-box">
            <div class="order-info-item">
                <span class="order-info-label">Mã đơn hàng:</span>
                <span class="order-info-value">#<?php echo htmlspecialchars($order_number); ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Số tiền:</span>
                <span class="order-info-value" style="color: #ff6b6b; font-size: 1.2rem;"><?php echo formatPrice($order['total_amount']); ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Phương thức:</span>
                <span class="order-info-value">
                    <?php 
                    $methods = [
                        'momo' => '📱 MoMo',
                        'zalopay' => '💙 ZaloPay',
                        'bank_transfer' => '🏦 Chuyển khoản',
                        'cod' => '💰 COD'
                    ];
                    echo $methods[$payment_method] ?? $payment_method;
                    ?>
                </span>
            </div>
        </div>
        
        <p style="color: #999; font-size: 0.9rem; margin-top: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">
            Đang xử lý thanh toán tự động...
        </p>
    </div>
</div>

<script>
// Tự động xử lý thanh toán sau 2 giây
setTimeout(function() {
    // Gọi API xử lý thanh toán
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
            // Thành công - redirect đến trang thành công
            window.location.href = 'order-success.php?order_number=<?php echo $order_number; ?>&paid=1';
        } else {
            // Lỗi - hiển thị thông báo
            document.querySelector('.processing-card').innerHTML = `
                <div style="font-size: 4rem; margin-bottom: 20px;">❌</div>
                <h2 style="color: #dc3545; margin-bottom: 15px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Có lỗi xảy ra</h2>
                <p style="color: #666; margin-bottom: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">${data.message || 'Không thể xử lý thanh toán'}</p>
                <a href="mock-payment.php?order_number=<?php echo $order_number; ?>" style="display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 10px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Thử lại</a>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.querySelector('.processing-card').innerHTML = `
            <div style="font-size: 4rem; margin-bottom: 20px;">❌</div>
            <h2 style="color: #dc3545; margin-bottom: 15px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Lỗi kết nối</h2>
            <p style="color: #666; margin-bottom: 20px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Vui lòng kiểm tra kết nối và thử lại</p>
            <a href="mock-payment.php?order_number=<?php echo $order_number; ?>" style="display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 10px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Quay lại</a>
        `;
    });
}, 2000);
</script>

<?php include '../includes/footer.php'; ?>

