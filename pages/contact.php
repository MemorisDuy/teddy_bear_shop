<?php
require_once "../config/config.php";
$page_title = "Liên hệ - Teddy Bear Shop";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = sanitizeInput($_POST["name"] ?? "");
    $email = sanitizeInput($_POST["email"] ?? "");
    $phone = sanitizeInput($_POST["phone"] ?? "");
    $subject = sanitizeInput($_POST["subject"] ?? "");
    $message = sanitizeInput($_POST["message"] ?? "");
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $result = $db->execute(
                "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
                [$name, $email, $phone, $subject, $message]
            );
            
            if ($result) {
                $success = "✅ Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.";
            } else {
                $error = "❌ Có lỗi xảy ra, vui lòng thử lại!";
            }
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    } else {
        $error = "❌ Vui lòng điền đầy đủ thông tin!";
    }
}

include "../includes/header.php";
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📍 Thông tin liên hệ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6><span style="font-size: 1.2rem; color: #dc3545; margin-right: 8px;">📍</span> Địa chỉ</h6>
                        <p>123 Đường Cute, Quận 1, TP.HCM</p>
                    </div>
                    <div class="mb-4">
                        <h6><span style="font-size: 1.2rem; color: #28a745; margin-right: 8px;">📞</span> Điện thoại</h6>
                        <p><a href="tel:0123456789">0123-456-789</a></p>
                    </div>
                    <div class="mb-4">
                        <h6><span style="font-size: 1.2rem; color: #007bff; margin-right: 8px;">✉️</span> Email</h6>
                        <p><a href="mailto:info@teddyparadise.com">info@teddyparadise.com</a></p>
                    </div>
                    <div class="mb-4">
                        <h6><span style="font-size: 1.2rem; color: #ffc107; margin-right: 8px;">🕐</span> Giờ làm việc</h6>
                        <p>Thứ 2 - Chủ nhật: 8:00 - 22:00</p>
                    </div>
                    <div style="text-align:center;margin-top:32px;">
                        <div style="font-size:1.3rem;color:#d63384;font-weight:600;margin-bottom:10px;">Vui lòng quét mã QR Zalo để liên hệ với shop!</div>
                        <span style="font-size:7rem;display:block;">🧸</span>
                    </div>
                    <div style="text-align:center; margin-top:24px;">
                        <button id="showQRBtn" class="btn btn-primary" style="font-size:1.1rem; padding:10px 28px; border-radius:24px; background:#008fe5; border:none;">
                            <span style="display:inline-block;width:28px;height:28px;vertical-align:middle;margin-right:8px;background:url('../images/QR.jpg') center/cover no-repeat;border-radius:6px;"></span> Quét mã QR Zalo để liên hệ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal QR Zalo giữ nguyên -->
    <div id="qrModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
        <div style="background:white;padding:32px 24px 24px 24px;border-radius:18px;max-width:95vw;max-height:90vh;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;position:relative;">
            <img src="../images/QR.jpg" alt="Zalo QR" style="max-width:320px;width:90vw;height:auto;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
            <div style="margin-top:18px;font-size:1.1rem;color:#008fe5;font-weight:600;">Quét mã QR này bằng Zalo để liên hệ shop!</div>
            <button onclick="document.getElementById('qrModal').style.display='none'" style="position:absolute;top:8px;right:12px;font-size:1.5rem;background:none;border:none;color:#d63384;cursor:pointer;">&times;</button>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('showQRBtn');
        var modal = document.getElementById('qrModal');
        if(btn && modal) {
            btn.onclick = function() {
                modal.style.display = 'flex';
            };
            modal.onclick = function(e) {
                if(e.target === modal) modal.style.display = 'none';
            };
        }
    });
    </script>
</div>

<?php include "../includes/footer.php"; ?>