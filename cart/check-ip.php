<?php
/**
 * Check IP Helper - Kiểm tra và hiển thị IP của server
 * Giúp user biết IP để cấu hình cho QR code
 */

require_once '../config/config.php';

$page_title = "Kiểm tra IP Server";

// Lấy IP
function getServerIP() {
    $ip = $_SERVER['SERVER_ADDR'] ?? '';
    
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
        $hostname = gethostname();
        $ip = gethostbyname($hostname);
        
        if ($ip === $hostname || $ip === '127.0.0.1') {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = @shell_exec('ipconfig');
                if ($output && preg_match('/IPv4 Address[\.\s]+:\s*(\d+\.\d+\.\d+\.\d+)/i', $output, $matches)) {
                    $ip = $matches[1];
                }
            } else {
                $output = @shell_exec('hostname -I 2>/dev/null');
                if ($output) {
                    $ip = trim(explode(' ', $output)[0]);
                }
            }
        }
    }
    
    return ($ip && $ip !== '127.0.0.1' && $ip !== '::1' && filter_var($ip, FILTER_VALIDATE_IP)) ? $ip : null;
}

$server_ip = getServerIP();
$current_site_url = SITE_URL;

include '../includes/header.php';
?>

<style>
.ip-check-container {
    min-height: 80vh;
    padding: 50px 0;
    background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%);
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.ip-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 700px;
    margin: 0 auto;
}

.ip-display {
    background: #e3f2fd;
    border: 2px solid #1976d2;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
    font-family: 'Courier New', monospace;
    font-size: 1.5rem;
    font-weight: bold;
    color: #1976d2;
}

.instructions {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;
}

.instructions ol {
    margin: 10px 0;
    padding-left: 20px;
}

.instructions code {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}
</style>

<div class="ip-check-container">
    <div class="container">
        <div class="ip-card">
            <h1 style="text-align: center; margin-bottom: 30px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                <span style="font-size: 2rem; margin-right: 10px;">🔍</span> Kiểm tra IP Server
            </h1>
            
            <?php if ($server_ip): ?>
            <div style="text-align: center; margin-bottom: 30px;">
                <p style="color: #666; margin-bottom: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    IP của máy tính bạn:
                </p>
                <div class="ip-display">
                    <?php echo htmlspecialchars($server_ip); ?>
                </div>
                <p style="color: #28a745; margin-top: 15px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    ✅ Hệ thống đã tự động lấy IP. QR code sẽ hoạt động!
                </p>
            </div>
            <?php else: ?>
            <div style="text-align: center; margin-bottom: 30px;">
                <p style="color: #dc3545; margin-bottom: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    ⚠️ Không thể tự động lấy IP
                </p>
                <p style="color: #666; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    Vui lòng làm theo hướng dẫn bên dưới để lấy IP thủ công
                </p>
            </div>
            <?php endif; ?>
            
            <div class="instructions">
                <h3 style="margin-bottom: 15px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    📋 Hướng dẫn lấy IP:
                </h3>
                
                <h4 style="margin-top: 20px; margin-bottom: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    Windows:
                </h4>
                <ol style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <li>Mở Command Prompt (nhấn Win + R, gõ <code>cmd</code>)</li>
                    <li>Gõ lệnh: <code>ipconfig</code></li>
                    <li>Tìm dòng "IPv4 Address" (ví dụ: 192.168.1.100)</li>
                </ol>
                
                <h4 style="margin-top: 20px; margin-bottom: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    Mac/Linux:
                </h4>
                <ol style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <li>Mở Terminal</li>
                    <li>Gõ lệnh: <code>ifconfig</code> hoặc <code>ip addr</code></li>
                    <li>Tìm dòng "inet" (ví dụ: 192.168.1.100)</li>
                </ol>
                
                <h4 style="margin-top: 20px; margin-bottom: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    🔧 Cấu hình IP trong code:
                </h4>
                <ol style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <li>Mở file <code>config/config.php</code></li>
                    <li>Tìm dòng: <code>define('SITE_URL', 'http://localhost/teddy_bear_shop');</code></li>
                    <li>Thay <code>localhost</code> bằng IP vừa tìm được</li>
                    <li>Ví dụ: <code>define('SITE_URL', 'http://192.168.1.100/teddy_bear_shop');</code></li>
                    <li>Lưu file và refresh trang</li>
                </ol>
            </div>
            
            <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 20px; margin-top: 30px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                <h5 style="color: #856404; margin-bottom: 10px;">
                    <span style="font-size: 1.2rem; margin-right: 8px;">💡</span> Lưu ý quan trọng:
                </h5>
                <ul style="color: #856404; margin: 0; padding-left: 20px;">
                    <li>Đảm bảo điện thoại và máy tính cùng mạng WiFi</li>
                    <li>Nếu IP thay đổi, cần cập nhật lại trong config</li>
                    <li>Sau khi sửa config, cần refresh trang thanh toán</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #666; margin-bottom: 15px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    <strong>URL hiện tại:</strong><br>
                    <code style="font-size: 0.9rem; word-break: break-all;"><?php echo htmlspecialchars($current_site_url); ?></code>
                </p>
                <a href="mock-payment.php" style="display: inline-block; padding: 12px 30px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 10px; font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">
                    Quay lại trang thanh toán
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


