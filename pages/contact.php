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
                        <p class="mb-0">Thứ 2 - Chủ nhật: 8:00 - 22:00</p>
                    </div>
                    <div class="alert alert-info text-center mt-4 mb-0" style="border-radius: 12px;">
                        <span>💬</span> Bạn có thắc mắc hoặc cần đặt mẫu gấu bông theo yêu cầu? Hãy gọi điện hoặc nhắn tin cho shop qua số hotline <strong class="text-primary">0123-456-789</strong> để được tư vấn nhanh nhất nhé!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>