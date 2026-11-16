<?php
/**
 * Trang Liên hệ - Teddy Bear Paradise
 * Giao diện hiện đại, sạch sẽ, đầy đủ thông tin & biểu mẫu gửi tin nhắn
 */
require_once "../config/config.php";
$page_title = "Liên hệ - " . SITE_NAME;
$page_description = "Liên hệ với Teddy Bear Paradise để được tư vấn và đặt hàng nhanh chóng.";

$success = '';
$error = '';

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
                $success = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.";
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại!";
            }
        } catch (Exception $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    } else {
        $error = "Vui lòng điền đầy đủ họ tên, email và nội dung!";
    }
}

include "../includes/header.php";
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-pink-light text-pink px-3 py-2 rounded-pill mb-2 fw-semibold">
            Hỗ trợ khách hàng
        </span>
        <h2 class="fw-bold text-dark mb-2">Liên Hệ Với Chúng Tôi</h2>
        <p class="text-muted mx-auto" style="max-width: 550px;">
            Bạn có thắc mắc về sản phẩm hoặc cần đặt gấu bông theo yêu cầu? Đừng ngần ngại liên hệ với shop nhé!
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Cột Thông tin liên hệ -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white">
                <div class="card-header bg-pink text-white py-3 px-4 border-0">
                    <h5 class="fw-bold mb-0 text-white">
                        <i class="fas fa-store me-2"></i> Thông tin cửa hàng
                    </h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-pink-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-map-marker-alt text-pink"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Địa chỉ cửa hàng</h6>
                                <p class="text-muted small mb-0">123 Đường Cute, Quận 1, TP. Hồ Chí Minh</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-pink-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-phone-alt text-pink"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hotline tư vấn</h6>
                                <p class="mb-0"><a href="tel:0123456789" class="text-pink fw-semibold text-decoration-none">0123-456-789</a></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-pink-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-envelope text-pink"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Email hỗ trợ</h6>
                                <p class="mb-0"><a href="mailto:info@teddyparadise.com" class="text-secondary text-decoration-none">info@teddyparadise.com</a></p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-pink-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fas fa-clock text-pink"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Giờ mở cửa</h6>
                                <p class="text-muted small mb-0">Thứ 2 - Chủ nhật: 8:00 - 22:00</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mt-4 small text-secondary">
                        <i class="fas fa-comments text-pink me-1"></i>
                        Đội ngũ CSKH trực tuyến luôn sẵn sàng hỗ trợ bạn trong mọi khung giờ làm việc.
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Form gửi tin nhắn -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold text-dark mb-4">Gửi tin nhắn cho shop</h5>

                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3 small mb-4">
                            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 small mb-4">
                            <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Họ và tên *</label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="Nguyễn Văn A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Email *</label>
                                <input type="email" name="email" class="form-control rounded-3" placeholder="email@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control rounded-3" placeholder="0912345678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Chủ đề</label>
                                <input type="text" name="subject" class="form-control rounded-3" placeholder="Tư vấn đặt hàng...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small">Nội dung tin nhắn *</label>
                                <textarea name="message" class="form-control rounded-3" rows="4" placeholder="Nhập lời nhắn của bạn..." required></textarea>
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 w-100 fw-semibold">
                                    <i class="fas fa-paper-plane me-2"></i> Gửi tin nhắn ngay
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
