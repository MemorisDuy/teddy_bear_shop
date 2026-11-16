<?php
/**
 * Product Detail Page - Chi tiết sản phẩm 🧸
 * COMPLETE VERSION - File duy nhất hoàn chỉnh
 */

require_once '../config/config.php';

// Get product slug
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: products.php');
    exit;
}

// Get product details
$product = getProductBySlug($slug);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Update view count
updateProductViews($product['id']);

// Get related products
$related_products = getRelatedProducts($product['id'], $product['category_id'], 4);

// Get product reviews
$reviews = getProductReviews($product['id'], 5);

// Get product gallery (if exists)
$gallery = json_decode($product['gallery'] ?? '[]', true) ?: [];

// SEO Meta
$page_title = $product['name'] . ' - Teddy Bear Paradise';
$page_description = $product['short_description'];

include '../includes/header.php';
?>

<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/product-detail-colors.css">

<!-- Breadcrumb -->
<div class="breadcrumb-container">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">🏠 Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="products.php">🧸 Sản phẩm</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category_id']); ?>"><?php echo htmlspecialchars($product['category_name'] ?? 'Sản phẩm'); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Detail -->
<div class="container my-5">
    <div class="row align-items-start">
        <!-- Product Gallery -->
        <div class="col-lg-7 col-md-6 mb-4">
            <div class="product-gallery">
                <!-- Main Image -->
                <div class="main-image">
                    <img id="mainImage" src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                    
                    <!-- Badges -->
                    <?php if (!empty($product['sale_price'])): ?>
                    <div class="product-badge sale">
                        SALE -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['is_limited'])): ?>
                    <div class="product-badge limited">
                        LIMITED
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                    <div class="product-badge low-stock">
                        Chỉ còn <?php echo $product['stock_quantity']; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail Gallery -->
                <?php if (!empty($gallery)): ?>
                <div class="thumbnail-gallery">
                    <div class="thumbnail active" onclick="changeMainImage('<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>', this)">
                        <img src="<?php echo IMG_PATH . htmlspecialchars($product['main_image']); ?>" alt="Main">
                    </div>
                    <?php foreach ($gallery as $image): ?>
                    <div class="thumbnail" onclick="changeMainImage('<?php echo IMG_PATH . htmlspecialchars($image); ?>', this)">
                        <img src="<?php echo IMG_PATH . htmlspecialchars($image); ?>" alt="Gallery">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Additional Content để lấp khoảng trống -->
                <div class="gallery-additional-content mt-4">
                    <!-- Product Info Cards -->
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <span style="font-size: 1rem; margin-right: 5px;">🚚</span>
                                </div>
                                <div class="info-content">
                                    <h6>Giao hàng nhanh</h6>
                                    <small>24-48h toàn quốc</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <span style="font-size: 1rem; margin-right: 5px;">🛡️</span>
                                </div>
                                <div class="info-content">
                                    <h6>Chất lượng</h6>
                                    <small>Đảm bảo an toàn</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <span style="font-size: 1rem; margin-right: 5px;">↩️</span>
                                </div>
                                <div class="info-content">
                                    <h6>Đổi trả dễ dàng</h6>
                                    <small>Trong vòng 7 ngày</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <span style="font-size: 1rem; margin-right: 5px;">📞</span>
                                </div>
                                <div class="info-content">
                                    <h6>Hỗ trợ 24/7</h6>
                                    <small>Tư vấn miễn phí</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Highlights -->
                    <div class="product-highlights mt-4">
                        <h6 class="fw-bold mb-3">
                            <span style="font-size: 1rem; color: #ffc107;">⭐</span> 
                            Tại sao chọn sản phẩm này?
                        </h6>
                        <div class="highlights-list">
                            <div class="highlight-item">
                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">✅</span>
                                <span>Chất liệu cao cấp, mềm mại và an toàn</span>
                            </div>
                            <div class="highlight-item">
                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">✅</span>
                                <span>Thiết kế dễ thương, phù hợp mọi lứa tuổi</span>
                            </div>
                            <div class="highlight-item">
                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">✅</span>
                                <span>Dễ dàng vệ sinh và bảo quản</span>
                            </div>
                            <div class="highlight-item">
                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">✅</span>
                                <span>Quà tặng ý nghĩa cho người thân</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Size Guide -->
                    <div class="size-guide mt-4">
                        <h6 class="fw-bold mb-3">
                            <span style="font-size: 1rem; color: #007bff; margin-right: 5px;">📏</span> 
                            Hướng dẫn chọn size
                        </h6>
                        <div class="size-chart">
                            <div class="size-item">
                                <strong>Size S (20-25cm)</strong>
                                <p>Phù hợp làm quà tặng, trang trí bàn làm việc</p>
                            </div>
                            <div class="size-item">
                                <strong>Size M (30-40cm)</strong>
                                <p>Kích thước vừa phải, phù hợp cho trẻ em</p>
                            </div>
                            <div class="size-item">
                                <strong>Size L (50-60cm)</strong>
                                <p>Kích thước lớn, thích hợp để ôm và trang trí</p>
                            </div>
                            <div class="size-item">
                                <strong>Size XL (70cm+)</strong>
                                <p>Khổng lồ, tạo điểm nhấn trong không gian</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Care Instructions -->
                    <div class="care-instructions mt-4">
                        <h6 class="fw-bold mb-3">
                            <span style="font-size: 1rem; color: #dc3545; margin-right: 5px;">💖</span> 
                            Hướng dẫn bảo quản
                        </h6>
                        <div class="care-list">
                            <div class="care-item">
                                <span style="font-size: 0.9rem; color: #17a2b8; margin-right: 5px;">💧</span>
                                <span>Giặt tay nhẹ nhàng với nước ấm</span>
                            </div>
                            <div class="care-item">
                                <span style="font-size: 0.9rem; color: #ffc107; margin-right: 5px;">☀️</span>
                                <span>Phơi ở nơi thoáng mát, tránh ánh nắng trực tiếp</span>
                            </div>
                            <div class="care-item">
                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">💨</span>
                                <span>Sử dụng máy sấy ở nhiệt độ thấp</span>
                            </div>
                            <div class="care-item">
                                <span style="font-size: 0.9rem; color: #007bff; margin-right: 5px;">✨</span>
                                <span>Vỗ nhẹ để giữ độ phồng của lông</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-5 col-md-6">
            <div class="product-info">
                <!-- Category -->
                <div class="product-category mb-2">
                    <a href="products.php?category=<?php echo urlencode($product['category_id']); ?>" 
                       class="text-primary text-decoration-none">
                        <span style="font-size: 0.9rem; margin-right: 5px;">🏷️</span> <?php echo htmlspecialchars($product['category_name'] ?? 'Sản phẩm'); ?>
                    </a>
                </div>
                
                <!-- Product Name -->
                <h1 class="product-title mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Rating -->
                <div class="product-rating mb-3">
                    <div class="stars d-inline-block">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span style="font-size: 0.9rem; color: <?php echo $i <= ($product['rating'] ?? 0) ? '#ffc107' : '#ddd'; ?>;">⭐</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text ms-2">
                        <strong><?php echo number_format($product['rating'] ?? 0, 1); ?></strong>/5 
                        (<?php echo $product['review_count'] ?? 0; ?> đánh giá)
                    </span>
                    <span class="views-count text-muted ms-2">
                        | <span style="font-size: 0.9rem; margin-right: 5px;">👁️</span> <?php echo number_format($product['views'] ?? 0); ?> lượt xem
                    </span>
                </div>
                
                <!-- Price -->
                <div class="product-price mb-4">
                    <?php if (!empty($product['sale_price'])): ?>
                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                        <span class="original-price ms-3"><?php echo formatPrice($product['price']); ?></span>
                        <div class="discount-info mt-2">
                            <span class="badge bg-danger">
                                Tiết kiệm <?php echo formatPrice($product['price'] - $product['sale_price']); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Short Description -->
                <div class="product-description mb-4">
                    <p class="lead"><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></p>
                </div>
                
                <!-- Product Description -->
                <div class="product-description" style="margin-top:20px;">
                    <h3>Giới thiệu sản phẩm</h3>
                    <div>
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')); ?>
                    </div>
                </div>
                
                <!-- Specifications -->
                <div class="spec-table">
                    <h6 class="fw-bold mb-3">📋 Thông số sản phẩm</h6>
                    <div class="row">
                        <?php
                        $sizes = [];
                        if (!empty($product['size'])) {
                            $sizes = array_filter(array_map('trim', preg_split('/[\s,]+/', $product['size'])));
                            // Nếu chỉ có 1 size, tự động demo đủ các size phổ biến
                            if (count($sizes) === 1) {
                                $sizes = ['S', 'M', 'L', 'XL'];
                            }
                        }
                        ?>
                        <div class="col-6 mb-2">
                            <strong>Kích thước:</strong>
                            <?php if (count($sizes) > 0): ?>
                                <select id="selectSize" name="size" class="form-select form-select-sm d-inline-block w-auto ms-2">
                                    <?php foreach ($sizes as $sz): ?>
                                        <option value="<?php echo htmlspecialchars($sz); ?>"><?php echo htmlspecialchars($sz); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <select id="selectSize" class="form-select form-select-sm d-inline-block w-auto ms-2" disabled>
                                    <option>Không rõ</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($product['color'])): ?>
                        <div class="col-6 mb-2">
                            <strong>Màu sắc:</strong> <?php echo htmlspecialchars($product['color']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['material'])): ?>
                        <div class="col-6 mb-2">
                            <strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['material']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['weight'])): ?>
                        <div class="col-6 mb-2">
                            <strong>Khối lượng:</strong> <?php echo htmlspecialchars($product['weight']); ?>kg
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-6 mb-2">
                            <strong>Tình trạng:</strong> 
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="text-success">Còn hàng (<?php echo $product['stock_quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="text-danger">Hết hàng</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-6 mb-2">
                            <strong>SKU:</strong> TB-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity Selector -->
                <?php if ($product['stock_quantity'] > 0): ?>
                <div class="quantity-selector">
                    <h6 class="fw-bold mb-3">🛒 Số lượng</h6>
                    <div class="quantity-controls d-flex align-items-center gap-3">
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">
                                <span style="font-size: 1rem;">➖</span>
                            </button>
                            <input type="number" id="quantity" class="form-control text-center" 
                                   value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">
                                <span style="font-size: 1rem;">➕</span>
                            </button>
                        </div>
                        <span class="text-muted">
                            (Tối đa: <?php echo $product['stock_quantity']; ?>)
                        </span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-lg me-3 mb-2" onclick="addToCartDetail()" id="addToCartBtn">
                        <span style="font-size: 1.1rem; margin-right: 5px;">➕</span>
                    </button>
                    <button class="btn btn-outline-danger btn-lg mb-2" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                        <span style="font-size: 1.1rem; margin-right: 5px;">💖</span> Yêu thích
                    </button>
                    <div class="mt-3">
                        <button class="btn btn-success btn-lg w-100" onclick="buyNow()" id="buyNowBtn">
                            <span style="font-size: 1.1rem; margin-right: 5px;">⚡</span> Mua ngay
                        </button>
                    </div>
                </div>
                
                <!-- Product Features -->
                <div class="product-features">
                    <h6 class="fw-bold mb-3">✨ Đặc điểm nổi bật</h6>
                    <div class="features-list">
                        <div class="feature-item">
                            <span style="font-size: 1rem; color: #28a745; margin-right: 8px;">🛡️</span>
                            <span>Chất liệu an toàn cho trẻ em</span>
                        </div>
                        <div class="feature-item">
                            <span style="font-size: 1rem; color: #dc3545; margin-right: 8px;">💖</span>
                            <span>Thiết kế đáng yêu, dễ thương</span>
                        </div>
                        <div class="feature-item">
                            <span style="font-size: 1rem; color: #007bff; margin-right: 8px;">🚚</span>
                            <span>Giao hàng nhanh 24-48h</span>
                        </div>
                        <div class="feature-item">
                            <span style="font-size: 1rem; color: #ffc107; margin-right: 8px;">🏅</span>
                            <span>Bảo hành chất lượng 12 tháng</span>
                        </div>
                    </div>
                </div>
                
                <!-- Trust Badges -->
                <div class="trust-badges">
                    <h6 class="fw-bold mb-3">🏆 Cam kết chất lượng</h6>
                    <div class="badges-grid">
                        <div class="trust-badge">
                            <span style="font-size: 1rem; color: #28a745; margin-right: 5px;">✅</span>
                            <small>Hàng chính hãng</small>
                        </div>
                        <div class="trust-badge">
                            <span style="font-size: 1rem; color: #007bff; margin-right: 5px;">↩️</span>
                            <small>Đổi trả 7 ngày</small>
                        </div>
                        <div class="trust-badge">
                            <span style="font-size: 1rem; color: #17a2b8; margin-right: 5px;">🚚</span>
                            <small>Miễn phí ship</small>
                        </div>
                        <div class="trust-badge">
                            <span style="font-size: 1rem; color: #6c757d; margin-right: 5px;">📞</span>
                            <small>Hỗ trợ 24/7</small>
                        </div>
                    </div>
                </div>
                
                <!-- Share Social -->
                <div class="social-share">
                    <h6 class="fw-bold mb-3">📢 Chia sẻ sản phẩm</h6>
                    <div class="share-buttons">
                        <button class="btn btn-outline-primary btn-sm" onclick="shareToFacebook()">
                            <span style="font-size: 1rem; margin-right: 5px;">📘</span> Facebook
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="shareToTwitter()">
                            <span style="font-size: 1rem; margin-right: 5px;">🐦</span> Twitter
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="copyLink()">
                            <span style="font-size: 1rem; margin-right: 5px;">🔗</span> Copy link
                        </button>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="out-of-stock text-center py-4">
                    <h5 class="text-danger">😢 Sản phẩm tạm hết hàng</h5>
                    <p class="text-muted">Vui lòng liên hệ để được thông báo khi có hàng trở lại</p>
                    <button class="btn btn-outline-primary" onclick="notifyWhenAvailable()">
                        <span style="font-size: 1rem; margin-right: 5px;">🔔</span> Thông báo khi có hàng
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Product Details Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="product-details-tabs">
                <ul class="nav nav-tabs" id="productTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#reviews">
                            ⭐ Đánh giá (<?php echo $product['review_count'] ?? 0; ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping">
                            🚚 Giao hàng & Đổi trả
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="productTabsContent">
                    <!-- Reviews (active) -->
                    <div class="tab-pane fade show active" id="reviews">
                        <div class="tab-content-wrapper">
                            <!-- Form đánh giá khách hàng -->
                            <div class="review-form mb-4 p-3 rounded shadow-sm bg-white">
                                <form id="customerReviewForm" autocomplete="off">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" id="reviewerName" placeholder="Tên của bạn" required maxlength="32">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="email" class="form-control" id="reviewerEmail" placeholder="Email (tùy chọn)" maxlength="100">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <select class="form-select" id="reviewerRating" required>
                                                <option value="">Chọn số sao</option>
                                                <option value="5">⭐⭐⭐⭐⭐ - Tuyệt vời</option>
                                                <option value="4">⭐⭐⭐⭐ - Tốt</option>
                                                <option value="3">⭐⭐⭐ - Bình thường</option>
                                                <option value="2">⭐⭐ - Chưa hài lòng</option>
                                                <option value="1">⭐ - Rất tệ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <textarea class="form-control" id="reviewerComment" rows="2" placeholder="Nội dung đánh giá..." required maxlength="300"></textarea>
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="reviews-list" id="reviewsList">
                                <h6 class="fw-bold">💬 Đánh giá từ khách hàng</h6>
                                <?php if (!empty($reviews)): ?>
                                    <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header d-flex justify-content-between">
                                            <div class="reviewer-info">
                                                <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span style="font-size: 0.9rem; color: <?php echo $i <= $review['rating'] ? '#ffc107' : '#ddd'; ?>;">⭐</span>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="review-date text-muted d-flex align-items-center gap-2">
                                                <span><?php echo timeAgo($review['created_at']); ?></span>
                                                <?php if ($review['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning" title="Chờ quản lý duyệt">⏳</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="review-content mt-2">
                                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="no-reviews text-center py-4">
                                    <span style="font-size: 3rem; color: #ddd;">💬</span>
                                    <p class="text-muted mt-3">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Shipping -->
                    <div class="tab-pane fade" id="shipping">
                        <div class="tab-content-wrapper">
                            <div class="shipping-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">🚚 Chính sách giao hàng</h6>
                                        <ul class="list-unstyled">
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Giao hàng toàn quốc</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Giao hàng trong 24-48h</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Miễn phí ship đơn trên 500k</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Kiểm tra hàng trước khi thanh toán</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">🔄 Chính sách đổi trả</h6>
                                        <ul class="list-unstyled">
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Đổi trả trong 7 ngày</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Sản phẩm còn nguyên tem, mác</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Hoàn tiền 100% nếu lỗi NSX</li>
                                            <li><span style="font-size: 0.9rem; color: #28a745; margin-right: 8px;">✅</span> Hỗ trợ đổi size miễn phí</li>
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
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="section-title text-center mb-4">
                <span class="title-icon">💝</span>
                Sản phẩm tương tự
            </h3>
            
            <div class="row">
                <?php foreach (array_slice($related_products, 0, 4) as $related): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="product-image position-relative">
                            <img src="<?php echo IMG_PATH . htmlspecialchars($related['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                 class="img-fluid"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                            
                            <div class="product-actions">
                                <button class="btn btn-sm btn-primary" onclick="addToCart(<?php echo $related['id']; ?>)">
                                    <span style="font-size: 1rem;">➕</span>
                                </button>
                                <a href="product-detail.php?slug=<?php echo urlencode($related['slug']); ?>" 
                                   class="btn btn-sm btn-outline-light">
                                    <span style="font-size: 1rem;">👁️</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="product-info p-3">
                            <h6 class="product-name">
                                <a href="product-detail.php?slug=<?php echo urlencode($related['slug']); ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </a>
                            </h6>
                            <div class="product-price">
                                <?php if (!empty($related['sale_price'])): ?>
                                    <span class="sale-price"><?php echo formatPrice($related['sale_price']); ?></span>
                                    <small class="original-price"><?php echo formatPrice($related['price']); ?></small>
                                <?php else: ?>
                                    <span class="current-price"><?php echo formatPrice($related['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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
            addProductToCart(window.productId, quantity);
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
            showNotification("✅ " + data.message, 'success');
            // Update cart count
            const cartBadge = document.getElementById("cartCount");
            if (cartBadge && data.cart_count !== undefined) {
                cartBadge.textContent = data.cart_count;
                cartBadge.style.display = data.cart_count > 0 ? 'flex' : 'none';
            }
            if (buyNow) {
                window.location.href = "../cart/checkout.php";
            }
        } else {
            showNotification("❌ " + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification("❌ Có lỗi xảy ra!", 'error');
    });
}
</script>

<script>
// Image gallery functions với better error handling
function changeMainImage(src, thumbnail) {
    const mainImage = document.getElementById('mainImage');
    if (!mainImage || !src) return;
    
    // Preload image để tránh flicker
    const newImg = new Image();
    
    newImg.onload = function() {
        mainImage.src = src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        if (thumbnail) {
            thumbnail.classList.add('active');
        }
    };
    
    newImg.onerror = function() {
        console.error('Failed to load image:', src);
        // Fallback to placeholder
        mainImage.src = '<?php echo IMG_PATH; ?>placeholder.jpg';
    };
    
    newImg.src = src;
}

// Quantity controls
function increaseQuantity() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const currentValue = parseInt(input.value) || 1;
    const maxQuantity = window.maxQuantity;
    if (currentValue < maxQuantity) {
        input.value = currentValue + 1;
    } else {
        showNotification(`📦 Số lượng tối đa là ${maxQuantity}!`, 'warning');
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const currentValue = parseInt(input.value) || 1;
    if (currentValue > 1) {
        input.value = currentValue - 1;
    } else {
        showNotification('📦 Số lượng tối thiểu là 1!', 'warning');
    }
}

// Đảm bảo đường dẫn fetch luôn đúng dù ở bất kỳ thư mục nào
function getBasePath() {
    var path = window.location.pathname;
    if (path.includes('/pages/')) return '../';
    if (path.includes('/cart/')) return './';
    if (path.includes('/admin/')) return '../';
    return '/teddy_bear_shop/';
}

// Add to cart from detail page
function getSelectedSize() {
    var sel = document.getElementById('selectSize');
    return sel ? sel.value : '';
}

// Add to wishlist
function addToWishlist(productId) {
    showNotification('💖 Đã thêm vào danh sách yêu thích!', 'success');
}

// Notify when available
function notifyWhenAvailable() {
    const email = prompt('Nhập email để nhận thông báo khi có hàng:');
    if (!email) return;
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('📧 Email không hợp lệ!', 'error');
        return;
    }
    
    showNotification('🔔 Đã đăng ký thông báo thành công!', 'success');
}

// Generic function to add product to cart (for related products)
function addToCart(productId) {
    fetch("../cart/add-to-cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=" + productId + "&quantity=1"
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification("✅ " + data.message, 'success');
            
            // Update cart count if element exists
            if (data.cart_count) {
                const cartBadge = document.getElementById("cartCount");
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                    cartBadge.style.animation = 'bounce 0.5s ease';
                }
            }
        } else {
            showNotification("❌ " + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification("❌ Có lỗi xảy ra!", 'error');
    });
}

// Social sharing functions
function shareToFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareToTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('🔗 Đã copy link sản phẩm!', 'success');
        }).catch(() => {
            fallbackCopyLink();
        });
    } else {
        fallbackCopyLink();
    }
}

function fallbackCopyLink() {
    const textArea = document.createElement('textarea');
    textArea.value = window.location.href;
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showNotification('🔗 Đã copy link sản phẩm!', 'success');
    } catch (err) {
        showNotification('❌ Không thể copy link!', 'error');
    }
    document.body.removeChild(textArea);
}

// Show notification function
function showNotification(message, type = 'info') {
    // Try to use global function first
    if (typeof window.showCuteNotification === 'function') {
        window.showCuteNotification(message, type);
        return;
    }
    
    // Create simple notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} alert-dismissible`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Initialize quantity input validation
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value) || 1;
            const maxQuantity = window.maxQuantity;
            if (value < 1) {
                value = 1;
            } else if (value > maxQuantity) {
                value = maxQuantity;
                showNotification(`📦 Số lượng tối đa là ${maxQuantity}!`, 'warning');
            }
            this.value = value;
        });
    }
    
    // Initialize Bootstrap tabs
    const triggerTabList = [].slice.call(document.querySelectorAll('#productTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes bounce {
        0%, 20%, 60%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        80% {
            transform: translateY(-5px);
        }
    }
`;
document.head.appendChild(style);

console.log('🧸 Product detail page loaded successfully!');
</script>

<!-- Product Review Handler -->
<script src="../js/product-review.js"></script>

<?php include '../includes/footer.php'; ?>