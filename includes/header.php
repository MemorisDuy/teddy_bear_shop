<?php
/**
 * Header Template - Teddy Bear Shop 🐻
 * FIXED: Proper CSS paths và search integration
 */

// Load config if not already loaded
if (!defined('SITE_NAME')) {
    require_once dirname(__DIR__) . '/config/config.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? SITE_NAME); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description ?? 'Cửa hàng gấu bông đáng yêu nhất Việt Nam'); ?>">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#ff6b96">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars(SITE_NAME); ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title ?? SITE_NAME); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description ?? 'Cửa hàng gấu bông đáng yêu nhất Việt Nam'); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/images/og-image.jpg">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(SITE_NAME); ?>">
    <meta property="og:locale" content="vi_VN">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title ?? SITE_NAME); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description ?? 'Cửa hàng gấu bông đáng yêu nhất Việt Nam'); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/images/twitter-card.jpg">
    
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Favicon and PWA Icons -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/images/icons/icon-192x192.png">
    <link rel="manifest" href="<?php echo SITE_URL; ?>/manifest.json">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Fredoka+One&family=Comfortaa:wght@700&family=Nunito:wght@400;700&family=Poppins:wght@400;700&family=Quicksand:wght@700&display=swap" rel="stylesheet">
    
    <!-- FIXED: Custom CSS Files với proper paths -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/cute-effects.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/modern-enhancements.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/search.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/order-management.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/mobile-responsive.css">
    
    <!-- Google Fonts: Quicksand, Nunito, Poppins, Comfortaa -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700&family=Comfortaa:wght@400;600;700&display=swap" rel="stylesheet">
    
    <?php if (isset($custom_css)): ?>
    <!-- Custom page CSS -->
    <style><?php echo $custom_css; ?></style>
    <?php endif; ?>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
            🐻 <?php echo htmlspecialchars(SITE_NAME); ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>">
                        <span style="font-size: 1.1rem; margin-right: 5px;">🏠</span> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/products.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">💖</span> Sản phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/about.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">ℹ️</span> Giới thiệu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/contact.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">✉️</span> Liên hệ
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <!-- Search button -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="TeddySearch.openSearchOverlay(); return false;" title="Tìm kiếm (Ctrl+K)">
                        <span style="font-size: 1.2rem;">🔍</span>
                    </a>
                </li>
                <!-- Cart link - STANDARDIZED -->
                <li class="nav-item">
                    <a class="nav-link cart-link" href="<?php echo SITE_URL; ?>/cart/" title="Giỏ hàng" style="position: relative;">
                        <span style="font-size: 1.3rem; color: #333;">🛒</span>
                        <?php
                        $cart_count = 0;
                        if (function_exists('getCartCount')) {
                            $user_id = $_SESSION['user_id'] ?? null;
                            $cart_count = getCartCount($user_id);
                        }
                        ?>
                        <span id="cartCount" style="
    position: absolute; top: -8px; right: -8px;
    min-width: 18px; height: 18px;
    display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>;
    align-items: center; justify-content: center;
    font-size: 11px; font-weight: bold; border-radius: 50%;
    background: #dc3545; color: white;
">
                            <?php echo $cart_count; ?>
                        </span>
                    </a>
                </li>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.1rem; margin-right: 5px;">👤</span>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Tài khoản'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/profile.php">
                            <span style="font-size: 1rem; margin-right: 8px;">👤</span> Hồ sơ
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/orders.php">
                            <span style="font-size: 1rem; margin-right: 8px;">🛍️</span> Đơn hàng
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/auth/logout.php">
                            <span style="font-size: 1rem; margin-right: 8px;">🚪</span> Đăng xuất
                        </a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">🔐</span> Đăng nhập
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/register.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">➕</span> Đăng ký
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Scroll Progress Bar -->
<div class="scroll-progress" style="position: fixed; top: 0; left: 0; width: 0%; height: 3px; background: linear-gradient(45deg, #ff6b96, #f093fb); z-index: 10000; transition: width 0.1s ease;"></div>

<!-- Back to Top Button -->
<button id="scrollToTop" class="scroll-to-top" style="position: fixed; bottom: 105px; right: 22px; width: 46px; height: 46px; background: linear-gradient(135deg, #ff6b96, #f093fb); color: white; border: none; border-radius: 50%; cursor: pointer; opacity: 0; visibility: hidden; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55); z-index: 1000; box-shadow: 0 4px 15px rgba(255, 107, 150, 0.3); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;" title="Lên đầu trang">
    <span style="font-size: 1.1rem;">⬆️</span>
</button>

<!-- Notification Container -->
<div id="notificationContainer" style="position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px;"></div>

<!-- FIXED: Search Overlay với proper structure -->
<div id="searchOverlay">
    <div class="search-modal">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="color: #333; font-weight: 700; margin-bottom: 10px; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">🔍 Tìm kiếm sản phẩm</h3>
            <p style="color: #666; margin: 0; font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Nhập tên sản phẩm bạn muốn tìm</p>
        </div>
        
        <form class="search-form-modal">
            <div class="input-group">
                <input type="text" class="form-control search-input-modal" name="q" placeholder="VD: Gấu Teddy, Gấu Panda..." autocomplete="off">
                <button class="btn" type="submit">
                    <span style="font-size: 1rem; margin-right: 5px;">🔍</span> Tìm kiếm
                </button>
            </div>
        </form>
        
        <div class="search-suggestions">
            <h6 style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Gợi ý tìm kiếm:</h6>
            <div class="suggestion-tags">
                <span class="suggestion-tag" onclick="TeddySearch.searchSuggestion('Gấu Teddy')">Gấu Teddy</span>
                <span class="suggestion-tag" onclick="TeddySearch.searchSuggestion('Gấu Panda')">Gấu Panda</span>
                <span class="suggestion-tag" onclick="TeddySearch.searchSuggestion('Gấu khổng lồ')">Gấu khổng lồ</span>
                <span class="suggestion-tag" onclick="TeddySearch.searchSuggestion('Quà tặng')">Quà tặng</span>
            </div>
        </div>
        
        <button onclick="TeddySearch.closeSearchOverlay()" class="btn btn-outline-secondary btn-sm" style="width: 100%; font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
            <span style="font-size: 1rem; margin-right: 5px;">❌</span> Đóng
        </button>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Font Awesome Fallback Check -->
<script>
(function() {
    'use strict';
    // Check if Font Awesome is loaded
    function checkFontAwesome() {
        // Create a test element
        const testEl = document.createElement('i');
        testEl.className = 'fas fa-home';
        testEl.style.position = 'absolute';
        testEl.style.visibility = 'hidden';
        testEl.style.fontSize = '1px';
        document.body.appendChild(testEl);
        
        // Check computed style
        const computedStyle = window.getComputedStyle(testEl, ':before');
        const content = computedStyle.getPropertyValue('content');
        
        // If Font Awesome is not loaded, try fallback CDN
        if (!content || content === 'none' || content === '""') {
            console.warn('Font Awesome not detected, loading fallback...');
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            link.integrity = 'sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        }
        
        document.body.removeChild(testEl);
    }
    
    // Check after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkFontAwesome);
    } else {
        checkFontAwesome();
    }
    
    // Also check after a delay to catch late-loading CDN
    setTimeout(checkFontAwesome, 2000);
})();
</script>

<!-- Structured Data for SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Store",
  "name": "<?php echo htmlspecialchars(SITE_NAME); ?>",
  "description": "Cửa hàng gấu bông đáng yêu nhất Việt Nam",
  "url": "<?php echo SITE_URL; ?>",
  "logo": "<?php echo SITE_URL; ?>/images/logo.png",
  "image": "<?php echo SITE_URL; ?>/images/banner.jpg",
  "telephone": "+84-123-456-789",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "123 Đường Cute",
    "addressLocality": "Quận 1",
    "addressRegion": "TP.HCM",
    "addressCountry": "VN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "10.7769",
    "longitude": "106.7009"
  },
  "openingHours": "Mo-Su 08:00-22:00",
  "priceRange": "₫₫",
  "currenciesAccepted": "VND",
  "paymentAccepted": "Cash, Credit Card, Bank Transfer"
}
</script>