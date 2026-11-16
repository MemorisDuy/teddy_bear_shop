<?php
/**
 * Product Detail Page - Chi tiết sản phẩm
 * Giao diện hiện đại, sạch sẽ, icon chuẩn FontAwesome, tối ưu UX/UI
 */

require_once '../config/config.php';

// Lấy slug sản phẩm
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin chi tiết sản phẩm
$product = getProductBySlug($slug);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Cập nhật lượt xem
updateProductViews($product['id']);

// Lấy sản phẩm liên quan
$related_products = getRelatedProducts($product['id'], $product['category_id'], 4);

// Lấy đánh giá sản phẩm
$reviews = getProductReviews($product['id'], 5);

// Lấy thư viện ảnh bổ sung nếu có
$gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];

// SEO Meta
$page_title = $product['name'] . ' - ' . SITE_NAME;
$page_description = $product['short_description'];

include '../includes/header.php';
?>

<!-- Breadcrumb điều hướng -->
<div class="bg-light py-3 border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none text-muted">Sản phẩm</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category_id']); ?>" class="text-decoration-none text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'Sản phẩm'); ?></a></li>
                <li class="breadcrumb-item active text-pink fw-semibold" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Nội dung chi tiết sản phẩm -->
<div class="container mb-5">
    <div class="row align-items-start g-4">
        <!-- Gallery Hình ảnh -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-3 bg-white">
                <!-- Ảnh chính -->
                <div class="main-image position-relative rounded-4 overflow-hidden mb-3 text-center" style="background:#fff5f7; min-height: 400px; display:flex; align-items:center; justify-content:center;">
                    <img id="mainImage" src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="img-fluid rounded-4"
                         style="max-height: 420px; object-fit: contain;"
                         onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                    
                    <!-- Huy hiệu trạng thái -->
                    <?php if (!empty($product['sale_price'])): ?>
                    <div class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fs-6">
                        -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['is_limited'])): ?>
                    <div class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill">
                        Giới hạn
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail thu nhỏ -->
                <?php if (!empty($gallery)): ?>
                <div class="d-flex gap-2 overflow-auto pb-2">
                    <div class="thumbnail rounded-3 border p-1 cursor-pointer" onclick="changeMainImage('<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>', this)">
                        <img src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>" alt="Main" style="width: 70px; height: 70px; object-fit: cover;" class="rounded-2">
                    </div>
                    <?php foreach ($gallery as $image): ?>
                    <div class="thumbnail rounded-3 border p-1 cursor-pointer" onclick="changeMainImage('<?php echo IMG_PATH . htmlspecialchars($image); ?>', this)">
                        <img src="<?php echo IMG_PATH . htmlspecialchars($image); ?>" alt="Gallery" style="width: 70px; height: 70px; object-fit: cover;" class="rounded-2">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Cam kết dịch vụ -->
                <div class="row g-2 mt-3 pt-3 border-top text-center">
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-light">
                            <i class="fas fa-truck-fast text-pink mb-1"></i>
                            <div class="small fw-semibold text-dark" style="font-size: 0.75rem;">Giao siêu tốc</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-light">
                            <i class="fas fa-shield-alt text-pink mb-1"></i>
                            <div class="small fw-semibold text-dark" style="font-size: 0.75rem;">100% An toàn</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-light">
                            <i class="fas fa-undo-alt text-pink mb-1"></i>
                            <div class="small fw-semibold text-dark" style="font-size: 0.75rem;">Đổi trả 7 ngày</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded-3 bg-light">
                            <i class="fas fa-headset text-pink mb-1"></i>
                            <div class="small fw-semibold text-dark" style="font-size: 0.75rem;">Hỗ trợ 24/7</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thông tin sản phẩm & Đặt mua -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <!-- Danh mục & Lượt xem -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="products.php?category=<?php echo urlencode($product['category_id']); ?>" 
                       class="badge bg-pink-light text-pink text-decoration-none px-3 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($product['category_name'] ?? 'Gấu bông'); ?>
                    </a>
                    <span class="text-muted small">
                        <i class="fas fa-eye me-1"></i> <?php echo number_format($product['views'] ?? 0); ?> lượt xem
                    </span>
                </div>
                
                <!-- Tên sản phẩm -->
                <h2 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <!-- Đánh giá sao -->
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-2">
                        <?php 
                        $rating = (float)($product['rating'] ?? 5);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <i class="fas <?php echo $i <= $rating ? 'fa-star' : 'fa-star text-muted opacity-25'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-semibold text-dark me-2"><?php echo number_format($rating, 1); ?></span>
                    <span class="text-muted small">(<?php echo $product['review_count'] ?? 0; ?> đánh giá)</span>
                </div>
                
                <!-- Giá sản phẩm -->
                <div class="p-3 rounded-4 mb-4" style="background: #fff5f7;">
                    <?php if (!empty($product['sale_price'])): ?>
                        <div class="d-flex align-items-baseline gap-3">
                            <span class="fs-2 fw-bold text-danger"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="text-decoration-line-through text-muted fs-5"><?php echo formatPrice($product['price']); ?></span>
                        </div>
                        <div class="text-danger small mt-1">
                            <i class="fas fa-check-circle me-1"></i> Tiết kiệm <?php echo formatPrice($product['price'] - $product['sale_price']); ?>
                        </div>
                    <?php else: ?>
                        <span class="fs-2 fw-bold text-primary"><?php echo formatPrice($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Mô tả ngắn gọn -->
                <?php if (!empty($product['short_description'])): ?>
                <p class="text-secondary mb-4 leading-relaxed">
                    <?php echo htmlspecialchars($product['short_description']); ?>
                </p>
                <?php endif; ?>
                
                <!-- Tùy chọn Kích thước & Trạng thái kho -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark small mb-1">Kích thước</label>
                        <?php
                        $sizes = [];
                        if (!empty($product['size'])) {
                            $sizes = array_filter(array_map('trim', preg_split('/[\s,]+/', $product['size'])));
                            if (count($sizes) === 1) {
                                $sizes = ['S', 'M', 'L', 'XL'];
                            }
                        }
                        ?>
                        <select id="selectSize" class="form-select rounded-3">
                            <?php if (count($sizes) > 0): ?>
                                <?php foreach ($sizes as $sz): ?>
                                    <option value="<?php echo htmlspecialchars($sz); ?>"><?php echo htmlspecialchars($sz); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Tiêu chuẩn">Tiêu chuẩn</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark small mb-1">Tình trạng hàng</label>
                        <div>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-3 w-100 text-center d-block">
                                    <i class="fas fa-check-circle me-1"></i> Còn hàng (<?php echo $product['stock_quantity']; ?> sản phẩm)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-3 w-100 text-center d-block">
                                    <i class="fas fa-times-circle me-1"></i> Tạm hết hàng
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Chọn số lượng & Nút đặt mua -->
                <?php if ($product['stock_quantity'] > 0): ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark small mb-2">Số lượng đặt mua</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary px-3" type="button" onclick="decreaseQuantity()">
                                <i class="fas fa-minus small"></i>
                            </button>
                            <input type="number" id="quantity" class="form-control text-center fw-bold" 
                                   value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button class="btn btn-outline-secondary px-3" type="button" onclick="increaseQuantity()">
                                <i class="fas fa-plus small"></i>
                            </button>
                        </div>
                        <span class="text-muted small">Tối đa: <?php echo $product['stock_quantity']; ?></span>
                    </div>
                </div>
                
                <div class="d-flex flex-column gap-2 mb-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-lg flex-grow-1 rounded-pill py-2" id="addToCartBtn">
                            <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ
                        </button>
                        <button class="btn btn-outline-danger btn-lg rounded-pill px-4" onclick="addToWishlist(<?php echo $product['id']; ?>)" title="Yêu thích">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary btn-lg rounded-pill py-2 fw-semibold" id="buyNowBtn">
                        <i class="fas fa-bolt me-2"></i> Mua ngay bây giờ
                    </button>
                </div>
                <?php else: ?>
                <div class="alert alert-warning rounded-4 p-4 text-center mb-4">
                    <i class="fas fa-box-open fs-3 text-warning mb-2"></i>
                    <h6 class="fw-bold mb-1">Sản phẩm hiện đang tạm hết hàng</h6>
                    <p class="text-muted small mb-3">Vui lòng để lại email để chúng tôi thông báo khi đợt hàng mới về!</p>
                    <button class="btn btn-outline-primary rounded-pill px-4" onclick="notifyWhenAvailable()">
                        <i class="fas fa-bell me-2"></i> Nhận thông báo có hàng
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Chia sẻ sản phẩm -->
                <div class="d-flex align-items-center gap-2 pt-3 border-top">
                    <span class="text-muted small fw-semibold me-2">Chia sẻ:</span>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="shareToFacebook()">
                        <i class="fab fa-facebook-f me-1"></i> Facebook
                    </button>
                    <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="shareToTwitter()">
                        <i class="fab fa-twitter me-1"></i> Twitter
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="copyLink()">
                        <i class="fas fa-link me-1"></i> Sao chép link
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab chi tiết sản phẩm: Mô tả, Đánh giá, Chính sách -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs border-0" id="productTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active px-4 py-3 fw-bold text-dark border-0 rounded-0" data-bs-toggle="tab" data-bs-target="#desc">
                                <i class="fas fa-file-alt text-pink me-2"></i> Mô tả chi tiết
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link px-4 py-3 fw-bold text-dark border-0 rounded-0" data-bs-toggle="tab" data-bs-target="#reviews">
                                <i class="fas fa-star text-warning me-2"></i> Đánh giá (<?php echo $product['review_count'] ?? 0; ?>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link px-4 py-3 fw-bold text-dark border-0 rounded-0" data-bs-toggle="tab" data-bs-target="#shipping">
                                <i class="fas fa-shipping-fast text-info me-2"></i> Giao hàng & Đổi trả
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="tab-content" id="productTabsContent">
                        <!-- Mô tả -->
                        <div class="tab-pane fade show active" id="desc">
                            <h5 class="fw-bold text-dark mb-3">Thông Tin Chi Tiết Sản Phẩm</h5>
                            <div class="text-secondary leading-relaxed mb-4">
                                <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả chi tiết cho sản phẩm này.')); ?>
                            </div>
                            
                            <!-- Thông số kỹ thuật -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between">
                                        <span class="text-muted">Chất liệu:</span>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($product['material'] ?? 'Vải nhung lông mịn & Bông PP'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between">
                                        <span class="text-muted">Xuất xứ:</span>
                                        <span class="fw-semibold text-dark">Việt Nam</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between">
                                        <span class="text-muted">Màu sắc:</span>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($product['color'] ?? 'Nhiều màu'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between">
                                        <span class="text-muted">Bảo hành:</span>
                                        <span class="fw-semibold text-dark">12 tháng đường may</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Đánh giá -->
                        <div class="tab-pane fade" id="reviews">
                            <h5 class="fw-bold text-dark mb-4">Đánh Giá Từ Khách Hàng</h5>
                            
                            <!-- Form gửi đánh giá -->
                            <div class="card border rounded-4 p-4 mb-4 bg-light">
                                <h6 class="fw-bold text-dark mb-3">Viết nhận xét của bạn</h6>
                                <form id="customerReviewForm" autocomplete="off">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control rounded-3" id="reviewerName" placeholder="Họ và tên của bạn *" required maxlength="32">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="email" class="form-control rounded-3" id="reviewerEmail" placeholder="Email của bạn (tùy chọn)" maxlength="100">
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select rounded-3" id="reviewerRating" required>
                                                <option value="">Chọn số sao đánh giá</option>
                                                <option value="5">⭐⭐⭐⭐⭐ - Rất hài lòng</option>
                                                <option value="4">⭐⭐⭐⭐ - Hài lòng</option>
                                                <option value="3">⭐⭐⭐ - Bình thường</option>
                                                <option value="2">⭐⭐ - Không hài lòng</option>
                                                <option value="1">⭐ - Rất tệ</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <textarea class="form-control rounded-3" id="reviewerComment" rows="3" placeholder="Chia sẻ cảm nhận chi tiết của bạn về sản phẩm..." required maxlength="300"></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                                <i class="fas fa-paper-plane me-2"></i> Gửi đánh giá
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Danh sách nhận xét -->
                            <div id="reviewsList">
                                <?php if (!empty($reviews)): ?>
                                    <?php foreach ($reviews as $review): ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo timeAgo($review['created_at']); ?></small>
                                        </div>
                                        <div class="text-warning small mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas <?php echo $i <= $review['rating'] ? 'fa-star' : 'fa-star text-muted opacity-25'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-secondary mb-0 small"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="far fa-comments fs-2 mb-2 d-block opacity-50"></i>
                                        Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên để lại nhận xét!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Chính sách vận chuyển -->
                        <div class="tab-pane fade" id="shipping">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-4 rounded-4 bg-light h-100">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-truck text-pink me-2"></i> Chính sách giao hàng</h6>
                                        <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i> Giao hàng toàn quốc từ 1 - 3 ngày làm việc</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Miễn phí vận chuyển cho đơn hàng từ 500.000đ</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Đóng gói hút chân không cẩn thận, sạch sẽ</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Được kiểm tra hàng trước khi thanh toán (COD)</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-4 rounded-4 bg-light h-100">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-sync text-pink me-2"></i> Chính sách đổi trả</h6>
                                        <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i> Đổi trả miễn phí trong vòng 7 ngày nếu lỗi sản xuất</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Sản phẩm giữ nguyên tem mác, không rách hay bẩn</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Hỗ trợ đổi kích cỡ hoặc màu sắc linh hoạt</li>
                                            <li><i class="fas fa-check text-success me-2"></i> Hoàn tiền 100% nếu giao sai mẫu mã</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sản phẩm tương tự -->
    <?php if (!empty($related_products)): ?>
    <div class="mt-5">
        <h4 class="fw-bold text-dark mb-4">Sản Phẩm Tương Tự</h4>
        <div class="row g-4">
            <?php foreach (array_slice($related_products, 0, 4) as $related): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card product-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="position-relative overflow-hidden" style="height: 220px; background: #fff5f7;">
                        <img src="<?php echo IMG_PATH . htmlspecialchars($related['main_image']); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                             class="card-img-top w-100 h-100 object-fit-cover"
                             onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="card-title fw-bold text-dark mb-2 text-truncate" title="<?php echo htmlspecialchars($related['name']); ?>">
                            <a href="product-detail.php?slug=<?php echo urlencode($related['slug']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h6>
                        <div class="mt-auto mb-3">
                            <?php if (!empty($related['sale_price'])): ?>
                                <span class="fw-bold text-danger"><?php echo formatPrice($related['sale_price']); ?></span>
                                <small class="text-decoration-line-through text-muted ms-1"><?php echo formatPrice($related['price']); ?></small>
                            <?php else: ?>
                                <span class="fw-bold text-primary"><?php echo formatPrice($related['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="product-detail.php?slug=<?php echo urlencode($related['slug']); ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
window.productId = <?php echo (int)$product['id']; ?>;
window.maxQuantity = <?php echo (int)$product['stock_quantity']; ?>;

document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('addToCartBtn');
    const buyBtn = document.getElementById('buyNowBtn');
    
    if (addBtn) {
        addBtn.onclick = function() {
            const quantity = document.getElementById('quantity')?.value || 1;
            addProductToCart(window.productId, quantity, false);
        };
    }
    
    if (buyBtn) {
        buyBtn.onclick = function() {
            const quantity = document.getElementById('quantity')?.value || 1;
            addProductToCart(window.productId, quantity, true);
        };
    }
});

function addProductToCart(productId, quantity, buyNow = false) {
    const size = getSelectedSize();
    fetch("../cart/add-to-cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${productId}&quantity=${quantity}&size=${encodeURIComponent(size)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            const cartBadge = document.getElementById("cartCount");
            if (cartBadge && data.cart_count !== undefined) {
                cartBadge.textContent = data.cart_count;
                cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }
            if (buyNow) {
                window.location.href = "../cart/checkout.php";
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification("Có lỗi xảy ra, vui lòng thử lại sau!", 'error');
    });
}

function changeMainImage(src, thumbnail) {
    const mainImage = document.getElementById('mainImage');
    if (!mainImage || !src) return;
    mainImage.src = src;
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('border-pink'));
    if (thumbnail) {
        thumbnail.classList.add('border-pink');
    }
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const val = parseInt(input.value) || 1;
    if (val < window.maxQuantity) {
        input.value = val + 1;
    } else {
        showNotification(`Số lượng tối đa có sẵn là ${window.maxQuantity}!`, 'warning');
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const val = parseInt(input.value) || 1;
    if (val > 1) {
        input.value = val - 1;
    }
}

function getSelectedSize() {
    const sel = document.getElementById('selectSize');
    return sel ? sel.value : '';
}

function addToWishlist(productId) {
    showNotification('Đã lưu sản phẩm vào danh sách yêu thích!', 'success');
}

function notifyWhenAvailable() {
    const email = prompt('Nhập địa chỉ email của bạn để nhận thông báo:');
    if (!email) return;
    showNotification('Đã ghi nhận yêu cầu thông báo khi có hàng!', 'success');
}

function shareToFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareToTwitter() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://twitter.com/intent/tweet?url=${url}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Đã sao chép liên kết sản phẩm!', 'success');
        });
    }
}

function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-success text-white' : type === 'error' ? 'bg-danger text-white' : 'bg-warning text-dark';
    alertDiv.className = `position-fixed top-0 end-0 m-4 p-3 rounded-4 shadow-lg ${bgClass}`;
    alertDiv.style.zIndex = '99999';
    alertDiv.style.minWidth = '250px';
    alertDiv.innerHTML = `<div class="d-flex align-items-center justify-content-between">
        <span>${message}</span>
        <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.parentElement.remove()"></button>
    </div>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 4000);
}
</script>

<!-- Product Review Handler -->
<script src="../js/product-review.js"></script>

<?php include '../includes/footer.php'; ?>
