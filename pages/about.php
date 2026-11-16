<?php
/**
 * Trang Giới thiệu - Teddy Bear Paradise
 * Giao diện hiện đại, sạch sẽ, bố cục chuẩn thương mại điện tử
 */
require_once "../config/config.php";
$page_title = "Giới thiệu - " . SITE_NAME;
$page_description = "Tìm hiểu về câu chuyện và sứ mệnh của Teddy Bear Paradise - thiên đường gấu bông cao cấp.";
include "../includes/header.php";
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="text-center mb-5">
        <span class="badge bg-pink-light text-pink px-3 py-2 rounded-pill mb-2 fw-semibold">
            Về chúng tôi
        </span>
        <h1 class="fw-bold text-dark mb-3">Câu Chuyện Teddy Bear Paradise</h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">
            Nơi gửi gắm những yêu thương trọn vẹn qua từng chú gấu bông cao cấp, an toàn và tràn đầy cảm xúc.
        </p>
    </div>

    <!-- Main Story Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-md-5 text-center">
                    <div class="position-relative d-inline-block">
                        <img src="../images/teddy-01.jpg" alt="Teddy Bear Paradise" 
                             class="img-fluid rounded-4 shadow-sm" 
                             style="max-height: 320px; object-fit: cover;">
                    </div>
                </div>
                <div class="col-md-7">
                    <h3 class="fw-bold text-dark mb-3">Sứ Mệnh Của Chúng Tôi</h3>
                    <p class="text-secondary leading-relaxed mb-3">
                        Teddy Bear Paradise được thành lập với niềm đam mê mang lại niềm vui, sự an ủi và những khoảnh khắc ấm áp cho mọi lứa tuổi. Chúng tôi tin rằng mỗi chú gấu bông không chỉ là món đồ chơi, mà còn là người bạn đồng hành gắn bó, lưu giữ những kỷ niệm quý giá trong cuộc đời.
                    </p>
                    <p class="text-secondary leading-relaxed mb-4">
                        Toàn bộ sản phẩm tại cửa hàng đều được tuyển chọn kỹ lưỡng từ chất liệu bông PP xoắn 3 chiều siêu êm ái, lớp vải nhung mềm mại không rụng lông, đảm bảo an toàn tuyệt đối cho trẻ nhỏ và người có làn da nhạy cảm.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="products.php" class="btn btn-primary px-4 py-2 rounded-pill">
                            <i class="fas fa-shopping-bag me-2"></i> Khám phá sản phẩm
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary px-4 py-2 rounded-pill">
                            <i class="fas fa-envelope me-2"></i> Liên hệ chúng tôi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark mb-2">Giá Trị Cốt Lõi</h3>
        <p class="text-muted">Những tiêu chuẩn tạo nên niềm tin của hàng ngàn khách hàng</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Value 1 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-all">
                <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-shield-alt text-pink fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Chất Lượng & An Toàn</h5>
                <p class="text-muted mb-0">
                    100% bông tinh khiết kháng khuẩn, đường chỉ may tỉ mỉ, mềm mịn và an toàn cho mọi lứa tuổi.
                </p>
            </div>
        </div>
        <!-- Value 2 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-all">
                <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-gift text-pink fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Đa Dạng Mẫu Mã</h5>
                <p class="text-muted mb-0">
                    Cập nhật liên tục các xu hướng gấu bông hot nhất, hỗ trợ gói quà tinh tế và thiệp chúc mừng ý nghĩa.
                </p>
            </div>
        </div>
        <!-- Value 3 -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-all">
                <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-headset text-pink fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Tận Tâm Phục Vụ</h5>
                <p class="text-muted mb-0">
                    Tư vấn nhiệt tình 24/7, đóng gói cẩn thận, giao hàng hỏa tốc và chính sách bảo hành rõ ràng.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
