<?php
/**
 * Navigation Bar Component 🧭
 */

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Get cart count
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
$cart_count = getCartCount($user_id);

// Get categories for dropdown
$navbar_categories = getCategories();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
            <img src="<?php echo IMG_PATH; ?>logo.png" alt="Logo" width="40" height="40" class="me-2">
            <div>
                <div class="fw-bold text-primary">Teddy Bear</div>
                <small class="text-muted">Paradise 🐻</small>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>">
                        <span style="font-size: 1.1rem; margin-right: 5px;">🏠</span> Trang chủ
                    </a>
                </li>

                <!-- Products Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['products.php', 'product-detail.php']) ? 'active' : ''; ?>" 
                       href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown">
                        <span style="font-size: 1.1rem; margin-right: 5px;">💖</span> Sản phẩm
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php">
                            <span style="font-size: 1rem; margin-right: 8px;">📦</span> Tất cả sản phẩm
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($navbar_categories as $category): ?>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/products.php?category=<?php echo $category['slug']; ?>">
                            <span style="font-size: 1rem; margin-right: 8px;">🏷️</span> <?php echo $category['name']; ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- About -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/pages/about.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">💖</span> Giới thiệu
                    </a>
                </li>

                <!-- Contact -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/pages/contact.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">📞</span> Liên hệ
                    </a>
                </li>
            </ul>

            <!-- Right Side Actions -->
            <div class="navbar-nav">
                <!-- Search -->
                <form class="d-flex me-3 search-form-modal" action="/teddy_bear_shop/pages/search.php" method="GET">
                    <div class="input-group">
                        <label for="navbarSearchInput" class="visually-hidden">Tìm kiếm sản phẩm</label>
                        <input class="form-control search-input-modal" type="search" id="navbarSearchInput" name="q" placeholder="Tìm sản phẩm..." autocomplete="search" aria-label="Tìm kiếm">
                        <button class="btn btn-outline-primary" type="submit" style="border-radius: 0 20px 20px 0;">
                            <span style="font-size: 1rem;">🔍</span>
                        </button>
                    </div>
                </form>

                <!-- Cart -->
                <div class="nav-item me-3">
                    <a href="/cart/cart.php" class="cart-link">
                        <span style="font-size: 1.1rem; margin-right: 5px;">🛒</span> Giỏ hàng (<span id="cartCount">0</span>)
                    </a>
                </div>

                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="font-size: 1.1rem; margin-right: 5px;">👤</span>
                        <span class="d-none d-md-inline"><?php echo $_SESSION['user_name']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/profile.php">
                            <span style="font-size: 1rem; margin-right: 8px;">👤</span> Thông tin cá nhân
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/orders.php">
                            <span style="font-size: 1rem; margin-right: 8px;">📦</span> Đơn hàng của tôi
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
                    <a class="nav-link btn btn-primary text-white px-3 ms-2" 
                       href="<?php echo SITE_URL; ?>/auth/register.php">
                        <span style="font-size: 1.1rem; margin-right: 5px;">➕</span> Đăng ký
                    </a>
                </li>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<link rel="stylesheet" href="../css/style.css">