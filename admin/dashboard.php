<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Get comprehensive stats
try {
    $stats = [
        'total_products' => $db->fetch("SELECT COUNT(*) as count FROM products")['count'] ?? 0,
        'total_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders")['count'] ?? 0,
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
        'total_revenue' => $db->fetch("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")['total'] ?? 0,
        'pending_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'")['count'] ?? 0,
        'today_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'active_coupons' => $db->fetch("SELECT COUNT(*) as count FROM coupons WHERE status = 'active' AND start_date <= CURDATE() AND end_date >= CURDATE()")['count'] ?? 0
    ];
    
    // Recent orders
    $recent_orders = $db->fetchAll(
        "SELECT order_number, customer_name, total_amount, order_status, created_at 
         FROM orders 
         ORDER BY created_at DESC 
         LIMIT 5"
    );
    
    // Top products
    $top_products = $db->fetchAll(
        "SELECT p.name, p.price, p.stock_quantity, 
                COUNT(oi.id) as sales_count
         FROM products p
         LEFT JOIN order_items oi ON p.id = oi.product_id
         GROUP BY p.id
         ORDER BY sales_count DESC
         LIMIT 5"
    );
    
} catch (Exception $e) {
    $stats = ['total_products' => 0, 'total_orders' => 0, 'total_users' => 0, 'total_revenue' => 0, 'pending_orders' => 0, 'today_orders' => 0, 'active_coupons' => 0];
    $recent_orders = [];
    $top_products = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Quicksand', 'Nunito', sans-serif; 
            background: linear-gradient(135deg, #fff5f7 0%, #ffe4ec 100%);
            margin: 0; padding: 20px; min-height: 100vh; color: #1e293b;
        }
        .dashboard { max-width: 1200px; margin: 0 auto; }
        .header { 
            background: #ffffff; color: #1e293b; 
            padding: 28px; border-radius: 18px; margin-bottom: 24px; 
            text-align: center; position: relative;
            box-shadow: 0 4px 20px rgba(255, 107, 150, 0.08);
            border: 1px solid #ffe4ec;
        }
        .welcome { font-size: 26px; margin-bottom: 8px; font-weight: 700; color: #1e293b; }
        .subtitle { color: #64748b; font-size: 15px; }
        .logout-btn { 
            position: absolute; top: 20px; right: 20px; 
            background: #ff6b96; padding: 8px 18px; border-radius: 999px; 
            text-decoration: none; color: white; font-weight: 600; font-size: 14px;
            transition: all 0.25s ease;
        }
        .logout-btn:hover { background: #ff5284; transform: translateY(-2px); color: white; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 24px; }
        .stat-card { 
            background: #ffffff; padding: 22px; border-radius: 16px; 
            box-shadow: 0 4px 16px rgba(0,0,0,0.04); text-align: center; 
            border: 1px solid #f1f5f9;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(255, 107, 150, 0.12); }
        .stat-number { font-size: 28px; font-weight: 700; color: #ff6b96; margin-bottom: 8px; }
        .stat-label { color: #64748b; font-weight: 600; font-size: 14px; }
        
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .content-card { 
            background: #ffffff; border-radius: 16px; 
            box-shadow: 0 4px 16px rgba(0,0,0,0.04); padding: 24px;
            border: 1px solid #f1f5f9;
        }
        .card-title { font-size: 18px; font-weight: 700; margin-bottom: 18px; color: #1e293b; }
        
        .recent-item { 
            padding: 14px; margin-bottom: 10px; background: #f8fafc; 
            border-radius: 12px; display: flex; justify-content: space-between; align-items: center;
        }
        .item-info h6 { margin: 0; font-weight: 600; color: #1e293b; }
        .item-info small { color: #64748b; }
        .item-value { font-weight: bold; color: #10b981; }
        
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-top: 24px; }
        .action-card { 
            background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%); 
            color: white; padding: 20px; border-radius: 16px; 
            text-decoration: none; text-align: center; transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 4px 14px rgba(255, 107, 150, 0.25);
        }
        .action-card:hover { transform: translateY(-3px); color: white; box-shadow: 0 8px 22px rgba(255, 107, 150, 0.35); }
        .action-icon { font-size: 26px; margin-bottom: 10px; display: block; }
        
        @media (max-width: 768px) { 
            body { padding: 12px; }
            .header { padding: 20px 15px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
            .logout-btn { position: static; align-self: flex-end; padding: 8px 16px; font-size: 13px; margin-bottom: 4px; }
            .welcome { font-size: 20px; }
            .subtitle { font-size: 14px; }
            .content-grid { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr 1fr; gap: 12px; }
            .stat-card { padding: 16px 12px; }
            .stat-number { font-size: 22px; word-break: break-word; }
        }
        @media (max-width: 480px) {
            .stats { grid-template-columns: 1fr; }
            .performance-grid { grid-template-columns: 1fr !important; }
            .recent-item-enhanced { flex-wrap: wrap; gap: 8px; }
            .item-value-enhanced { width: 100%; text-align: right; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
            <div class="welcome"><i class="fas fa-star"></i> Chào mừng, <?php echo htmlspecialchars($admin_name); ?>!</div>
            <div class="subtitle">Bảng điều khiển quản trị Teddy Bear Shop</div>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-box"></i> <?php echo number_format($stats['total_products']); ?></div>
                <div class="stat-label">Sản phẩm</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-shopping-cart"></i> <?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Đơn hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-users"></i> <?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Khách hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-coins"></i> <?php echo formatPrice($stats['total_revenue']); ?></div>
                <div class="stat-label">Doanh thu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-clock"></i> <?php echo number_format($stats['pending_orders']); ?></div>
                <div class="stat-label">Chờ xử lý</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-calendar-alt"></i> <?php echo number_format($stats['today_orders']); ?></div>
                <div class="stat-label">Đơn hôm nay</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><i class="fas fa-tag"></i> <?php echo number_format($stats['active_coupons']); ?></div>
                <div class="stat-label">Mã giảm giá</div>
            </div>
        </div>

        <!-- Content Grid Enhanced -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header-enhanced">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Đơn hàng gần đây</h3>
                    <a href="manage-orders.php" class="view-all-btn">Xem tất cả</a>
                </div>
                <?php if (empty($recent_orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-box"></i></div>
                        <h4>Chưa có đơn hàng nào</h4>
                        <p>Đơn hàng mới sẽ xuất hiện ở đây</p>
                    </div>
                <?php else: ?>
                    <div class="items-list">
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="recent-item-enhanced">
                            <div class="item-avatar">
                                <span>🧾</span>
                            </div>
                            <div class="item-info">
                                <h6>#<?php echo htmlspecialchars($order['order_number']); ?></h6>
                                <small><?php echo htmlspecialchars($order['customer_name']); ?></small>
                                <div class="item-meta">
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php 
                                        $status_names = [
                                            'pending' => 'Chờ xử lý',
                                            'confirmed' => 'Đã xác nhận', 
                                            'shipping' => 'Đang giao',
                                            'delivered' => 'Đã giao'
                                        ];
                                        echo $status_names[$order['order_status']] ?? $order['order_status'];
                                        ?>
                                    </span>
                                    <span class="order-time"><?php echo date('d/m H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="item-value-enhanced">
                                <?php echo formatPrice($order['total_amount']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Analytics Chart -->
            <div class="content-card">
                <div class="card-header-enhanced">
                    <h3 class="card-title"><span>📈</span> Thống kê nhanh</h3>
                    <div class="time-filter">
                        <select class="mini-select">
                            <option>7 ngày</option>
                            <option>30 ngày</option>
                            <option>3 tháng</option>
                        </select>
                    </div>
                </div>
                
                <div class="mini-charts">
                    <div class="mini-chart">
                        <div class="chart-header">
                            <span class="chart-label">Doanh thu</span>
                            <span class="chart-trend up">+12%</span>
                        </div>
                        <div class="chart-visual">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%;"></div>
                            </div>
                        </div>
                        <div class="chart-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                    </div>
                    
                    <div class="mini-chart">
                        <div class="chart-header">
                            <span class="chart-label">Đơn hàng</span>
                            <span class="chart-trend up">+8%</span>
                        </div>
                        <div class="chart-visual">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%;"></div>
                            </div>
                        </div>
                        <div class="chart-value"><?php echo number_format($stats['total_orders']); ?></div>
                    </div>
                    
                    <div class="mini-chart">
                        <div class="chart-header">
                            <span class="chart-label">Khách hàng</span>
                            <span class="chart-trend up">+15%</span>
                        </div>
                        <div class="chart-visual">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85%;"></div>
                            </div>
                        </div>
                        <div class="chart-value"><?php echo number_format($stats['total_users']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Actions -->
        <div class="enhanced-actions">
            <div class="actions-header">
                <h3><i class="fas fa-rocket"></i> Thao tác nhanh</h3>
                <p>Quản lý shop của bạn một cách hiệu quả</p>
            </div>
            
            <div class="action-grid">
                <a href="manage-products.php" class="action-card-enhanced">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-box"></i></span>
                        <div class="action-badge"><?php echo $stats['total_products']; ?></div>
                    </div>
                    <div class="action-content">
                        <h4>Quản lý sản phẩm</h4>
                        <p>Thêm, sửa, xóa sản phẩm</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="manage-orders.php" class="action-card-enhanced">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-shopping-cart"></i></span>
                        <div class="action-badge pending"><?php echo $stats['pending_orders']; ?></div>
                    </div>
                    <div class="action-content">
                        <h4>Quản lý đơn hàng</h4>
                        <p>Xử lý đơn hàng mới</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="manage-users.php" class="action-card-enhanced">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-users"></i></span>
                        <div class="action-badge"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="action-content">
                        <h4>Quản lý khách hàng</h4>
                        <p>Xem thông tin khách hàng</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="manage-coupons.php" class="action-card-enhanced">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-tag"></i></span>
                        <div class="action-badge coupon"><?php echo $stats['active_coupons']; ?></div>
                    </div>
                    <div class="action-content">
                        <h4>Mã giảm giá</h4>
                        <p>Tạo và quản lý khuyến mãi</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="manage-custom-orders.php" class="action-card-enhanced">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-palette"></i></span>
                        <div class="action-badge"><i class="fas fa-palette"></i></div>
                    </div>
                    <div class="action-content">
                        <h4>Đơn đặt mẫu riêng</h4>
                        <p>Yêu cầu gấu bông tùy chỉnh</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="add-product.php" class="action-card-enhanced special">
                    <div class="action-icon-wrapper">
                        <span class="action-icon"><i class="fas fa-plus"></i></span>
                        <div class="action-sparkle">✨</div>
                    </div>
                    <div class="action-content">
                        <h4>Thêm sản phẩm mới</h4>
                        <p>Mở rộng cửa hàng</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
                
                <a href="../index.php" target="_blank" class="action-card-enhanced website">
                    <div class="action-icon-wrapper">
                        <span class="action-icon">🌐</span>
                        <div class="action-external">↗</div>
                    </div>
                    <div class="action-content">
                        <h4>Xem website</h4>
                        <p>Kiểm tra giao diện khách</p>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="performance-section">
            <h3><span>🏆</span> Hiệu suất shop</h3>
            <div class="performance-grid">
                <div class="performance-card">
                    <div class="performance-icon">📈</div>
                    <div class="performance-info">
                        <h4>Tăng trưởng</h4>
                        <p class="performance-value positive">+23.5%</p>
                        <small>So với tháng trước</small>
                    </div>
                </div>
                
                <div class="performance-card">
                    <div class="performance-icon"><i class="fas fa-bolt"></i></div>
                    <div class="performance-info">
                        <h4>Tỷ lệ chuyển đổi</h4>
                        <p class="performance-value">3.8%</p>
                        <small>Khách mua / Khách xem</small>
                    </div>
                </div>
                
                <div class="performance-card">
                    <div class="performance-icon">⭐</div>
                    <div class="performance-info">
                        <h4>Đánh giá TB</h4>
                        <p class="performance-value">4.7/5</p>
                        <small>Từ khách hàng</small>
                    </div>
                </div>
                
                <div class="performance-card">
                    <div class="performance-icon"><i class="fas fa-rocket"></i></div>
                    <div class="performance-info">
                        <h4>Sản phẩm HOT</h4>
                        <p class="performance-value"><?php echo $stats['total_products']; ?></p>
                        <small>Đang bán chạy</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Enhanced Styles */
        .content-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 25px; margin-bottom: 30px; }
        
        .card-header-enhanced {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;
        }
        
        .view-all-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; padding: 6px 12px; border-radius: 15px;
            text-decoration: none; font-size: 12px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .view-all-btn:hover { transform: translateY(-1px); color: white; }
        
        .empty-state {
            text-align: center; padding: 40px 20px; color: #999;
        }
        .empty-icon { font-size: 48px; margin-bottom: 15px; }
        .empty-state h4 { margin: 0 0 5px 0; color: #666; }
        .empty-state p { margin: 0; font-size: 14px; }
        
        .items-list { max-height: 350px; overflow-y: auto; }
        .recent-item-enhanced {
            display: flex; align-items: center; padding: 15px; margin-bottom: 10px;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 12px; border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .recent-item-enhanced:hover {
            transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .item-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex; align-items: center; justify-content: center;
            color: white; margin-right: 15px; font-size: 16px;
        }
        
        .item-info { flex: 1; }
        .item-info h6 { margin: 0 0 3px 0; font-weight: 600; color: #333; }
        .item-info small { color: #666; font-size: 12px; }
        .item-meta { display: flex; gap: 10px; margin-top: 5px; align-items: center; }
        
        .status-badge {
            padding: 2px 8px; border-radius: 10px; font-size: 10px;
            font-weight: 600; text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-shipping { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        
        .order-time { font-size: 10px; color: #999; }
        .item-value-enhanced {
            font-weight: bold; color: #28a745; font-size: 16px;
        }
        
        .mini-select {
            padding: 4px 8px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 12px; background: white;
        }
        
        .mini-charts { display: flex; flex-direction: column; gap: 20px; }
        .mini-chart {
            padding: 15px; background: #f8f9fa; border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .chart-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .chart-label { font-size: 12px; color: #666; font-weight: 500; }
        .chart-trend { font-size: 12px; font-weight: bold; }
        .chart-trend.up { color: #28a745; }
        .chart-trend.down { color: #dc3545; }
        
        .progress-bar {
            width: 100%; height: 6px; background: #e9ecef; border-radius: 3px;
            overflow: hidden; margin: 8px 0;
        }
        .progress-fill {
            height: 100%; background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px; transition: width 0.3s ease;
        }
        .chart-value { font-weight: bold; color: #333; font-size: 16px; }
        
        .enhanced-actions { margin: 30px 0; }
        .actions-header { text-align: center; margin-bottom: 25px; }
        .actions-header h3 { margin: 0; color: #333; font-size: 24px; }
        .actions-header p { margin: 5px 0 0 0; color: #666; }
        
        .action-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .action-card-enhanced {
            background: rgba(255,255,255,0.95); padding: 25px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); text-decoration: none;
            display: flex; align-items: center; gap: 15px;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .action-card-enhanced:hover {
            transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: inherit;
        }
        .action-card-enhanced::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 100%; height: 3px; background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .action-icon-wrapper { position: relative; }
        .action-icon { font-size: 32px; color: #667eea; }
        .action-badge {
            position: absolute; top: -8px; right: -8px;
            background: #ff4757; color: white; border-radius: 10px;
            padding: 2px 6px; font-size: 10px; font-weight: bold;
        }
        .action-badge.pending { background: #ffa502; }
        .action-badge.coupon { background: #2ed573; }
        
        .action-sparkle { position: absolute; top: -5px; right: -5px; font-size: 14px; }
        .action-external { position: absolute; top: -5px; right: -5px; font-size: 12px; color: #667eea; }
        
        .action-content { flex: 1; }
        .action-content h4 { margin: 0 0 5px 0; font-size: 16px; color: #333; }
        .action-content p { margin: 0; font-size: 12px; color: #666; }
        
        .action-arrow { font-size: 20px; color: #667eea; font-weight: bold; }
        
        .action-card-enhanced.special {
            background: linear-gradient(135deg, #2ed573, #17a2b8);
            color: white;
        }
        .action-card-enhanced.special .action-icon,
        .action-card-enhanced.special .action-content h4,
        .action-card-enhanced.special .action-arrow { color: white; }
        .action-card-enhanced.special .action-content p { color: rgba(255,255,255,0.8); }
        
        .action-card-enhanced.website {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }
        .action-card-enhanced.website .action-icon,
        .action-card-enhanced.website .action-content h4,
        .action-card-enhanced.website .action-arrow { color: white; }
        .action-card-enhanced.website .action-content p { color: rgba(255,255,255,0.8); }
        
        .performance-section {
            background: rgba(255,255,255,0.95); padding: 30px; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 30px;
        }
        .performance-section h3 { margin: 0 0 25px 0; color: #333; text-align: center; }
        
        .performance-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .performance-card {
            text-align: center; padding: 20px; background: #f8f9fa;
            border-radius: 12px; transition: transform 0.3s ease;
        }
        .performance-card:hover { transform: translateY(-3px); }
        
        .performance-icon { font-size: 32px; margin-bottom: 10px; }
        .performance-info h4 { margin: 0 0 5px 0; color: #333; font-size: 14px; }
        .performance-value { margin: 5px 0; font-size: 20px; font-weight: bold; color: #667eea; }
        .performance-value.positive { color: #28a745; }
        .performance-card small { color: #666; font-size: 11px; }
        
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .action-grid { grid-template-columns: 1fr; }
            .performance-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>

    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            setTimeout(() => {
                document.querySelectorAll('.progress-fill').forEach(bar => {
                    bar.style.transform = 'scaleX(1)';
                });
            }, 500);
            
            // Add click effects to action cards
            document.querySelectorAll('.action-card-enhanced').forEach(card => {
                card.addEventListener('click', function(e) {
                    this.style.transform = 'translateY(-5px) scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-5px) scale(1)';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>