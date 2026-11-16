<?php
/**
 * Homepage - Teddy Bear Paradise 🐻
 * FINAL PERFECT VERSION - No loading issues, all images working
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple configuration
if (file_exists('config/config.php')) {
    try {
        require_once 'config/config.php';
    } catch (Exception $e) {
        define('IMG_PATH', 'images/');
        define('SITE_URL', '/teddy_bear_shop/');
    }
} else {
    define('IMG_PATH', 'images/');
    define('SITE_URL', '/teddy_bear_shop/');
}

$page_title = 'Trang chủ - Teddy Bear Paradise 🐻';
$page_description = 'Cửa hàng gấu bông đáng yêu nhất Việt Nam';

// Categories with real image files
$categories = [
    [
        'id' => 1, 
        'name' => 'Gấu Teddy Classic', 
        'slug' => 'gau-teddy-classic', 
        'description' => 'Gấu Teddy truyền thống đáng yêu', 
        'image' => 'category-teddy.jpg'
    ],
    [
        'id' => 2, 
        'name' => 'Gấu Panda', 
        'slug' => 'gau-panda', 
        'description' => 'Gấu Panda siêu cute', 
        'image' => 'category-panda.jpg'
    ],
    [
        'id' => 3, 
        'name' => 'Gấu Đặc Biệt', 
        'slug' => 'gau-dac-biet', 
        'description' => 'Gấu phiên bản giới hạn', 
        'image' => 'category-special.jpg'
    ],
    [
        'id' => 4, 
        'name' => 'Gấu Động Vật', 
        'slug' => 'gau-dong-vat', 
        'description' => 'Gấu hình động vật khác', 
        'image' => 'category-animals.jpg'
    ]
];

// Featured products with existing image files
$featured_products = [
    [
        'id' => 1, 
        'name' => 'Gấu Teddy Nâu Classic', 
        'slug' => 'gau-teddy-nau-classic',
        'short_description' => 'Gấu Teddy nâu cổ điển, mềm mại và an toàn',
        'price' => 150000, 
        'sale_price' => 120000, 
        'main_image' => 'teddy-01.jpg',
        'rating' => 5, 
        'review_count' => 24, 
        'is_limited' => false
    ],
    [
        'id' => 2, 
        'name' => 'Gấu Teddy Hồng Dễ Thương', 
        'slug' => 'gau-teddy-hong-de-thuong',
        'short_description' => 'Gấu Teddy màu hồng xinh xắn cho bé gái',
        'price' => 180000, 
        'sale_price' => null, 
        'main_image' => 'teddy-02.jpg',
        'rating' => 5, 
        'review_count' => 18, 
        'is_limited' => false
    ],
    [
        'id' => 3, 
        'name' => 'Gấu Panda Đen Trắng', 
        'slug' => 'gau-panda-den-trang',
        'short_description' => 'Gấu Panda đen trắng với biểu cảm dễ thương',
        'price' => 220000, 
        'sale_price' => 200000, 
        'main_image' => 'panda-01.jpg',
        'rating' => 4, 
        'review_count' => 32, 
        'is_limited' => false
    ],
    [
        'id' => 5, 
        'name' => 'Gấu Koala Xám Úc', 
        'slug' => 'gau-koala-xam-uc',
        'short_description' => 'Gấu Koala đáng yêu từ Úc',
        'price' => 200000, 
        'sale_price' => 180000, 
        'main_image' => 'koala-01.jpg',
        'rating' => 5, 
        'review_count' => 15, 
        'is_limited' => true
    ]
];

// Price format function
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' ₫';
    }
}

// Try to include header
$header_included = false;
if (file_exists('includes/header.php')) {
    try {
        include 'includes/header.php';
        $header_included = true;
    } catch (Exception $e) {
        $header_included = false;
    }
}

// If header not included, create inline header
if (!$header_included) {
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/teddy_bear_shop/css/style.css">
    <link rel="stylesheet" href="/teddy_bear_shop/css/cute-effects.css">
    <!-- <link rel="stylesheet" href="/teddy_bear_shop/css/category-cards.css"> -->
</head>
<body>
    <!-- Simple Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                        <span style="font-size: 1.2rem; color: #dc3545;">💖</span> Teddy Bear Paradise
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/products.php">Sản phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="pages/contact.php">Liên hệ</a></li>
                    <li class="nav-item">
                        <a class="nav-link cart-link" href="cart/">
                            <span style="font-size: 1.1rem;">🛒</span> Giỏ hàng 
                            <span id="cartCount" class="badge bg-danger">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php } ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center" style="min-height: 70vh;">
            <div class="col-lg-6">
                <div class="hero-content text-white">
                    <h1 class="hero-title mb-4">
                        <span style="color: #fff;">Thiên đường</span>
                        <span class="highlight">Gấu Bông</span>
                        <span class="bear-emoji">🧸</span>
                    </h1>
                    
                    <p class="hero-subtitle mb-4">
                        Khám phá bộ sưu tập gấu bông đáng yêu nhất Việt Nam! 
                        Từ gấu Teddy cổ điển đến những thiết kế độc đáo, 
                        chúng tôi có tất cả để làm bạn yêu! 💕
                    </p>
                    
                    <div class="hero-features mb-4">
                        <div class="feature-item">
                            <span style="font-size: 1.2rem; color: #dc3545;">💖</span> Chất lượng cao
                        </div>
                        <div class="feature-item">
                            <span style="font-size: 1.2rem; color: #28a745;">🛡️</span> An toàn 100%
                        </div>
                        <div class="feature-item">
                            <span style="font-size: 1.2rem; color: #ff69b4;">🚚</span> Giao hàng nhanh
                        </div>
                    </div>
                    
                    <div class="hero-buttons">
                        <a href="pages/products.php" class="cute-btn">
                            <span style="font-size: 1.1rem;">🛍️</span> Mua sắm ngay
                        </a>
                        <a href="pages/about.php" class="cute-btn-outline">
                            <span style="font-size: 1.1rem;">💖</span> Tìm hiểu thêm
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="<?php echo IMG_PATH; ?>bunny-bear.jpg" 
                         alt="Teddy Bear Collection" 
                         class="img-fluid rounded shadow-lg"
                         style="max-width: 100%; height: auto;"
                         onerror="this.style.background='linear-gradient(45deg, #ff6b96, #f093fb)'; this.style.color='white'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='🐻 Teddy Bear Paradise'; this.style.fontSize='2rem'; this.style.fontWeight='bold'; this.style.minHeight='400px'; this.style.borderRadius='15px';">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Elements -->
    <div class="floating-element">🧸</div>
    <div class="floating-element">🎁</div>
    <div class="floating-element">💝</div>
    <div class="floating-element">🌟</div>
    <div class="floating-element">❤️</div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">
                <span class="title-icon">🎯</span>
                Danh mục sản phẩm
            </h2>
            <p class="section-subtitle">Tìm những chú gấu bông yêu thích của bạn!</p>
        </div>
        
        <div class="row">
            <?php foreach ($categories as $category): ?>
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="category-card" style="position:relative;">
                    <div class="category-image">
                        <img src="<?php echo IMG_PATH . $category['image']; ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>"
                             onerror="this.style.background='linear-gradient(45deg, #ff6b96, #f093fb)'; this.style.color='white'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='🐻 <?php echo htmlspecialchars($category['name']); ?>'; this.style.fontSize='2rem'; this.style.fontWeight='bold'; this.style.minHeight='200px'; this.style.textAlign='center';">
                        <a href="pages/products.php?category=<?php echo urlencode($category['slug']); ?>"
                           class="btn btn-light btn-sm category-view-btn"
                           style="position:absolute;bottom:16px;left:50%;transform:translateX(-50%);z-index:2;box-shadow:0 2px 8px rgba(0,0,0,0.08);border-radius:20px;white-space:nowrap;">
                            Xem ngay <span style="font-size: 0.9rem; margin-left: 5px;">➡️</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">
                <span class="title-icon">⭐</span>
                Sản phẩm nổi bật
            </h2>
            <p class="section-subtitle">Những chú gấu bông được yêu thích nhất!</p>
        </div>
        
        <div class="row">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo IMG_PATH . $product['main_image']; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.style.background='linear-gradient(45deg, #ff6b96, #f093fb)'; this.style.color='white'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='🧸 <?php echo htmlspecialchars($product['name']); ?>'; this.style.fontSize='1rem'; this.style.fontWeight='bold'; this.style.textAlign='center'; this.style.padding='20px'; this.style.minHeight='250px';">
                        
                        <?php if (!empty($product['sale_price'])): ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-danger">SALE</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['is_limited'])): ?>
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-warning text-dark">LIMITED</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-overlay">
                            <div class="product-actions">
                                <button class="btn btn-primary add-to-cart-btn" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        type="button">
                                    <span style="font-size: 1.1rem;">➕</span>
                                </button>
                                <button class="btn btn-light rounded-circle" 
                                        onclick="quickView('<?php echo urlencode($product['slug']); ?>')"
                                        title="Xem nhanh">
                                    <span style="font-size: 1.1rem;">👁️</span>
                                </button>
                                <button class="btn btn-danger rounded-circle" 
                                        onclick="addToWishlist(<?php echo $product['id']; ?>)"
                                        title="Thêm vào yêu thích">
                                    <span style="font-size: 1.1rem;">💖</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span style="color: #ffd700; font-size: 1.2rem;">
                                    <?php echo $i <= ($product['rating'] ?? 0) ? '⭐' : '☆'; ?>
                                </span>
                            <?php endfor; ?>
                            <span class="rating-count">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                        </div>
                        
                        <h5 class="product-name">
                            <a href="pages/product-detail.php?slug=<?php echo urlencode($product['slug']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h5>
                        
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        
                        <div class="product-price mb-3">
                            <?php if (!empty($product['sale_price'])): ?>
                                <span class="product-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                <div class="small text-success">
                                    Tiết kiệm <?php echo formatPrice($product['price'] - $product['sale_price']); ?>
                                </div>
                            <?php else: ?>
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions-bottom mt-auto">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm add-to-cart-btn" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        type="button">
                                    <span style="font-size: 1rem;">🛒</span> Thêm vào giỏ
                                </button>
                                <a href="pages/product-detail.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <span style="font-size: 1rem;">ℹ️</span> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="pages/products.php" class="cute-btn">
                Xem tất cả sản phẩm <span style="font-size: 0.9rem; margin-left: 5px;">➡️</span>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">
                <span class="title-icon">🏆</span>
                Tại sao chọn chúng tôi?
            </h2>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <span style="font-size: 2.5rem;">🏆</span>
                    </div>
                    <h5 class="feature-title">Chất lượng cao</h5>
                    <p class="feature-desc">
                        Sản phẩm được chọn lọc kỹ càng, đảm bảo chất lượng tốt nhất
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <span style="font-size: 2.5rem;">🛡️</span>
                    </div>
                    <h5 class="feature-title">An toàn 100%</h5>
                    <p class="feature-desc">
                        Tất cả sản phẩm đều an toàn, không độc hại cho trẻ em
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <span style="font-size: 2.5rem;">🚚</span>
                    </div>
                    <h5 class="feature-title">Giao hàng nhanh</h5>
                    <p class="feature-desc">
                        Giao hàng toàn quốc, nhanh chóng trong 24-48h
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <span style="font-size: 2.5rem;">📞</span>
                    </div>
                    <h5 class="feature-title">Hỗ trợ 24/7</h5>
                    <p class="feature-desc">
                        Đội ngũ tư vấn nhiệt tình, hỗ trợ khách hàng mọi lúc
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<!-- (Đã xóa toàn bộ section newsletter từ dòng 434 đến 460) -->

<!-- Simple Footer -->
<?php include 'includes/footer.php'; ?>

<!-- FINAL OPTIMIZED JAVASCRIPT -->
<script>
// ===== FORCE PREVENT LOADING ISSUES =====
(function() {
    'use strict';
    
    function forceCleanup() {
        const selectors = ['.loading', '.spinner', '.preloader', '#loadingOverlay', '.loading-overlay', '[class*="loading"]', '[id*="loading"]'];
        selectors.forEach(selector => {
            try {
                document.querySelectorAll(selector).forEach(el => {
                    el.style.display = 'none';
                    el.style.opacity = '0';
                    el.style.visibility = 'hidden';
                    if (el.parentNode) el.parentNode.removeChild(el);
                });
            } catch (e) {}
        });
        
        document.body.classList.remove('loading');
        document.body.style.overflow = 'visible';
        document.body.style.pointerEvents = 'auto';
        document.documentElement.classList.remove('loading');
    }
    
    forceCleanup();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceCleanup, { once: true });
    }
    setTimeout(forceCleanup, 1000);
})();

console.log('🐻 Teddy Bear Shop loading...');

// ===== SIMPLE NOTIFICATION SYSTEM =====
function showNotification(message, type = 'info') {
    let container = document.getElementById('notifications');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notifications';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    // Remove existing notifications
    container.innerHTML = '';
    
    const colors = { success: '#28a745', error: '#dc3545', info: '#17a2b8', warning: '#ffc107' };
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        background: ${colors[type]}; color: white; padding: 15px 20px; border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 10px;
        transform: translateX(100%); opacity: 0; transition: all 0.3s ease;
        display: flex; align-items: center; gap: 10px; font-weight: 500;
    `;
    
    notification.innerHTML = `
        <span>${icons[type]}</span>
        <span style="flex: 1;">${message}</span>
        <button onclick="this.parentElement.remove()" 
                style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">×</button>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// ===== CART FUNCTIONS =====
let cartCount = 0;

function addToCart(productId, quantity = 1) {
    console.log('Adding product to cart:', productId);
    
    // Update cart count
    cartCount++;
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = cartCount;
        cartCountElement.style.animation = 'bounce 0.6s ease';
        setTimeout(() => cartCountElement.style.animation = '', 600);
    }
    
    showNotification('🛒 Đã thêm sản phẩm vào giỏ hàng!', 'success');
    showQuickCartActions();
}

function addToWishlist(productId) {
    showNotification('💝 Đã thêm vào danh sách yêu thích!', 'success');
    
    // Find the button that was clicked
    const buttons = document.querySelectorAll(`button[onclick*="addToWishlist(${productId})"]`);
    buttons.forEach(button => {
        const heart = button.querySelector('i');
        if (heart) {
            heart.style.color = '#ff6b96';
            heart.style.transform = 'scale(1.2)';
            setTimeout(() => heart.style.transform = 'scale(1)', 300);
        }
    });
}

function quickView(productSlug) {
    window.location.href = `pages/product-detail.php?slug=${encodeURIComponent(productSlug)}`;
}

function subscribeNewsletter(event) {
    event.preventDefault();
    const form = event.target;
    const email = form.querySelector('input[type="email"]').value;
    
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification('📧 Vui lòng nhập email hợp lệ!', 'warning');
        return;
    }
    
    showNotification('⏳ Đang đăng ký...', 'info');
    
    setTimeout(() => {
        showNotification('💌 Cảm ơn bạn đã đăng ký nhận tin!', 'success');
        form.reset();
    }, 1000);
}

function showQuickCartActions() {
    const existing = document.querySelector('.quick-cart-actions');
    if (existing) existing.remove();
    
    const quickActions = document.createElement('div');
    quickActions.className = 'quick-cart-actions';
    quickActions.style.cssText = `
        position: fixed; bottom: 20px; right: 20px; z-index: 1000;
        background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 20px; min-width: 300px; transform: translateY(100%); opacity: 0;
        transition: all 0.3s ease;
    `;
    
    quickActions.innerHTML = `
        <div style="text-align: center;">
            <h6 style="margin-bottom: 15px; color: #2c3e50;">🛒 Sản phẩm đã được thêm!</h6>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="cart/" class="btn btn-primary btn-sm">
                    <span style="font-size: 1.1rem; margin-right: 5px;">🛒</span> Xem giỏ hàng
                </a>
                <button class="btn btn-success btn-sm" onclick="this.closest('.quick-cart-actions').remove()">
                    <span style="font-size: 1rem; margin-right: 5px;">🛍️</span> Tiếp tục mua
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(quickActions);
    
    setTimeout(() => {
        quickActions.style.transform = 'translateY(0)';
        quickActions.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        if (quickActions.parentNode) {
            quickActions.style.transform = 'translateY(100%)';
            quickActions.style.opacity = '0';
            setTimeout(() => quickActions.remove(), 300);
        }
    }, 5000);
}

// ===== SCROLL TO TOP BUTTON =====
function createScrollToTopButton() {
    if (document.getElementById('scrollToTop')) return;
    
    const scrollBtn = document.createElement('button');
    scrollBtn.id = 'scrollToTop';
    scrollBtn.innerHTML = '<span style="font-size: 1.2rem;">⬆️</span>';
    scrollBtn.style.cssText = `
        position: fixed; bottom: 30px; left: 30px; width: 50px; height: 50px;
        background: linear-gradient(45deg, #ff6b96, #f093fb); color: white;
        border: none; border-radius: 50%; cursor: pointer; opacity: 0;
        visibility: hidden; transform: translateY(20px); transition: all 0.3s ease;
        z-index: 1000; box-shadow: 0 4px 15px rgba(255, 107, 150, 0.3);
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    `;

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.body.appendChild(scrollBtn);
}

// ===== SCROLL HANDLER =====
function handleScroll() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollBtn = document.getElementById('scrollToTop');
    
    if (scrollBtn) {
        if (scrollTop > 300) {
            scrollBtn.style.opacity = '1';
            scrollBtn.style.visibility = 'visible';
            scrollBtn.style.transform = 'translateY(0)';
        } else {
            scrollBtn.style.opacity = '0';
            scrollBtn.style.visibility = 'hidden';
            scrollBtn.style.transform = 'translateY(20px)';
        }
    }
}

// ===== INITIALIZE EVERYTHING =====
function initializeTeddyShop() {
    try {
        console.log('🐻 Initializing Teddy Bear Shop...');
        
        createScrollToTopButton();
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        console.log('✅ Teddy Bear Shop initialized successfully!');
        
    } catch (error) {
        console.error('❌ Initialization error:', error);
    }
}

// ===== AUTO INITIALIZATION =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTeddyShop, { once: true });
} else {
    initializeTeddyShop();
}

// Add CSS animations
const animationStyles = document.createElement('style');
animationStyles.textContent = `
@keyframes bounce {
    0%, 100% { transform: scale(1); }
    25% { transform: scale(1.1); }
    50% { transform: scale(1.2); }
    75% { transform: scale(1.1); }
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}
`;

if (!document.getElementById('teddy-animations')) {
    animationStyles.id = 'teddy-animations';
    document.head.appendChild(animationStyles);
}

console.log('🐻 Teddy Bear Shop JavaScript loaded and ready! 🚀');
</script>
<script src="js/cart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 Homepage: Initializing cart...');
    if (typeof TeddyBearCart !== 'undefined') {
        window.teddyCart = new TeddyBearCart();
        console.log('✅ Homepage: Cart initialized');
        window.teddyCart.loadCartCount();
        const buttons = document.querySelectorAll('.add-to-cart-btn');
        console.log('🔍 Homepage: Found buttons:', buttons.length);
        buttons.forEach((btn, i) => {
            console.log(`Homepage Button ${i}:`, {
                productId: btn.dataset.productId,
                classes: btn.className
            });
        });
    } else {
        console.error('❌ Homepage: TeddyBearCart not found');
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Toggle overlay khi click vào card
  document.querySelectorAll('.category-card').forEach(function(card) {
    card.addEventListener('click', function(e) {
      // Ẩn overlay của các card khác
      document.querySelectorAll('.category-card.show-overlay').forEach(function(other) {
        if (other !== card) other.classList.remove('show-overlay');
      });
      // Toggle overlay cho card này
      card.classList.toggle('show-overlay');
      e.stopPropagation();
    });
  });
  // Click ra ngoài thì ẩn overlay
  document.addEventListener('click', function() {
    document.querySelectorAll('.category-card.show-overlay').forEach(function(card) {
      card.classList.remove('show-overlay');
    });
  });
});
</script>
</body>
</html>