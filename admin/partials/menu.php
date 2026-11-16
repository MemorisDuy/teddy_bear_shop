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

<nav class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <span class="logo-icon">🧸</span>
            <span class="logo-text">Teddy Admin</span>
        </div>
        <div class="admin-info">
            <div class="admin-avatar">
                <span>🛡️</span>
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
                <span style="width: 24px; margin-right: 10px; text-align: center;">📊</span>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-item <?php echo (in_array($current_page, ['manage-products.php', 'add-product.php', 'edit-product.php'])) ? 'active' : ''; ?>">
            <a href="manage-products.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;">📦</span>
                <span>Quản lý sản phẩm</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-orders.php') ? 'active' : ''; ?>">
            <a href="manage-orders.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;">🛒</span>
                <span>Quản lý đơn hàng</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-users.php') ? 'active' : ''; ?>">
            <a href="manage-users.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;">👥</span>
                <span>Quản lý khách hàng</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-coupons.php') ? 'active' : ''; ?>">
            <a href="manage-coupons.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;">🏷️</span>
                <span>Mã giảm giá</span>
            </a>
        </li>

        <li class="menu-item <?php echo ($current_page == 'manage-custom-orders.php') ? 'active' : ''; ?>">
            <a href="manage-custom-orders.php">
                <span style="width: 24px; margin-right: 10px; text-align: center;">🎨</span>
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
                <span style="width: 24px; margin-right: 10px; text-align: center;">🚪</span>
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

@media (max-width: 768px) {
    .admin-sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
}
</style>