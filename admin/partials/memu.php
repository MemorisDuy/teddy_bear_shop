<?php
/**
 * Admin Menu Partial
 */

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel - Teddy Bear Shop'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-management.css">
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="admin-sidebar">
                <a href="../index.php" class="admin-brand">
                    <i class="fas fa-heart"></i> Teddy Shop Admin
                </a>
                
                <ul class="admin-nav">
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-bar"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="manage-products.php">
                            <i class="fas fa-box"></i> Quản lý sản phẩm
                        </a>
                    </li>
                    <li>
                        <a href="manage-categories.php">
                            <i class="fas fa-tag"></i> Quản lý danh mục
                        </a>
                    </li>
                    <li>
                        <a href="manage-orders.php">
                            <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
                        </a>
                    </li>
                    <li>
                        <a href="manage-users.php">
                            <i class="fas fa-users"></i> Quản lý khách hàng
                        </a>
                    </li>
                    <li>
                        <a href="manage-reviews.php">
                            <span>⭐</span> Đánh giá
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> Cài đặt
                        </a>
                    </li>
                    <li>
                        <a href="../index.php" target="_blank">
                            <i class="fas fa-home"></i> Xem website
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="main-content">
                <div class="admin-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $page_title ?? 'Admin Panel'; ?></h4>
                        <div>
                            <span class="text-muted">Xin chào, </span>
                            <strong><?php echo $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin'; ?></strong>
                        </div>
                    </div>
                </div>