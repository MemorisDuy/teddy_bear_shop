<?php
/**
 * Admin Menu - Fixed Links
 */

$config_path = dirname(dirname(__DIR__)) . '/config/config.php';
if (!file_exists($config_path)) {
    die('Config file not found at: ' . $config_path);
}
require_once $config_path;

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin-login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-header-top">
            <div class="logo">
                <span class="logo-icon"><i class="fas fa-heart"></i></span>
                <span class="logo-text">Teddy Admin</span>
            </div>
            <button type="button" class="mobile-menu-toggle" onclick="document.getElementById('adminSidebar').classList.toggle('menu-open')" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i> Menu
            </button>
        </div>
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="admin-details">
                <div class="admin-name"><?php echo htmlspecialchars($admin_name); ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-chart-bar"></i></span>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-item <?php echo (in_array($current_page, ['manage-products.php', 'add-product.php', 'edit-product.php'])) ? 'active' : ''; ?>">
            <a href="manage-products.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-box"></i></span>
                <span>Quản lý sản phẩm</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-orders.php') ? 'active' : ''; ?>">
            <a href="manage-orders.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-shopping-cart"></i></span>
                <span>Quản lý đơn hàng</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-users.php') ? 'active' : ''; ?>">
            <a href="manage-users.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-users"></i></span>
                <span>Quản lý khách hàng</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-coupons.php') ? 'active' : ''; ?>">
            <a href="manage-coupons.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-tag"></i></span>
                <span>Mã giảm giá</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-custom-orders.php') ? 'active' : ''; ?>">
            <a href="manage-custom-orders.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-palette"></i></span>
                <span>Đơn đặt mẫu riêng</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="../index.php" target="_blank">
                <span style="width: 24px; margin-right: 10px; text-align: center;">🌐</span>
                <span>Xem website</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="logout.php" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                <span style="width: 24px; margin-right: 10px; text-align: center;"><i class="fas fa-sign-out-alt"></i></span>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
</nav>

<style>
.admin-sidebar {
    width: 260px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.logo-icon {
    font-size: 28px;
    margin-right: 10px;
}

.logo-text {
    font-size: 20px;
    font-weight: 600;
}

.admin-info {
    display: flex;
    align-items: center;
}

.admin-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

.admin-name {
    font-weight: 600;
    margin-bottom: 2px;
}

.admin-role {
    font-size: 12px;
    opacity: 0.8;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-item {
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.menu-item a:hover,
.menu-item.active a {
    background: rgba(255,255,255,0.1);
    color: white;
}

.menu-item a i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
}

.sidebar-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mobile-menu-toggle {
    display: none;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.4);
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .admin-sidebar {
        width: 100%;
        height: auto;
        position: sticky;
        top: 0;
    }

    .sidebar-header {
        padding: 14px 16px;
    }

    .logo {
        margin-bottom: 0;
    }

    .mobile-menu-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .admin-info {
        display: none;
    }

    .sidebar-menu {
        display: none;
        max-height: 70vh;
        overflow-y: auto;
    }

    .admin-sidebar.menu-open .sidebar-menu {
        display: block;
    }

    .admin-sidebar.menu-open .admin-info {
        display: flex;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(255,255,255,0.15);
    }

    .page-header {
        padding: 18px 16px !important;
    }

    .page-title {
        font-size: 20px !important;
    }

    .card-header {
        flex-wrap: wrap;
        gap: 10px;
    }

    .stat-card {
        padding: 16px 12px !important;
    }

    .stat-number {
        font-size: 24px !important;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>