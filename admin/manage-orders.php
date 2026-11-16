<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

// Xử lý actions
$action = $_GET['action'] ?? '';
$order_id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Update order status
if ($action === 'update_status' && $order_id && isset($_GET['status'])) {
    $new_status = $_GET['status'];
    $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        try {
            if ($new_status === 'delivered') {
                $result = $db->execute("UPDATE orders SET order_status = ?, payment_status = 'paid' WHERE id = ?", [$new_status, $order_id]);
            } else {
                $result = $db->execute("UPDATE orders SET order_status = ? WHERE id = ?", [$new_status, $order_id]);
            }
            if ($result) {
                $status_names = [
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'processing' => 'Đang xử lý',
                    'shipping' => 'Đang giao',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy'
                ];
                $message = "Đã cập nhật trạng thái đơn hàng thành: {$status_names[$new_status]}";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;
$search = $_GET['search'] ?? '';
$status_filter = ($action === 'update_status') ? '' : ($_GET['status'] ?? '');
$date_filter = $_GET['date'] ?? '';

try {
    // Build query
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($status_filter) {
        $where_conditions[] = "order_status = ?";
        $params[] = $status_filter;
    }
    
    if ($date_filter) {
        $where_conditions[] = "DATE(created_at) = ?";
        $params[] = $date_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total orders count
    $count_query = "SELECT COUNT(*) as total FROM orders $where_clause";
    $total_result = $db->fetch($count_query, $params);
    $total_orders = $total_result['total'] ?? 0;
    $total_pages = ceil($total_orders / $per_page);
    
    // Get orders
    $orders_query = "SELECT o.*, 
                            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                     FROM orders o 
                     $where_clause 
                     ORDER BY o.created_at DESC 
                     LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $orders = $db->fetchAll($orders_query, $params);
    
    // Get stats
    $stats = [
        'total_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders")['count'] ?? 0,
        'pending_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'")['count'] ?? 0,
        'confirmed_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('confirmed', 'processing', 'shipping')")['count'] ?? 0,
        'delivered_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status = 'delivered'")['count'] ?? 0,
        'today_revenue' => $db->fetch("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'")['total'] ?? 0
    ];

} catch (Exception $e) {
    $orders = [];
    $stats = ['total_orders' => 0, 'pending_orders' => 0, 'confirmed_orders' => 0, 'delivered_orders' => 0, 'today_revenue' => 0];
    $message = 'Lỗi load dữ liệu: ' . $e->getMessage();
    $message_type = 'error';
}

include 'partials/menu.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .main-content { margin-left: 260px; padding: 30px; }
        .page-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; 
        }
        .page-title { font-size: 28px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 12px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 32px; font-weight: bold; color: #667eea; margin-bottom: 8px; }
        .stat-label { color: #666; font-weight: 500; }
        
        .content-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { 
            padding: 20px; border-bottom: 1px solid #eee; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .card-title { font-size: 20px; font-weight: 600; color: #333; margin: 0; }
        
        .filters { 
            display: flex; gap: 15px; margin-bottom: 20px; 
            flex-wrap: wrap; align-items: center; padding: 20px; background: #f8f9fa; 
        }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 12px; font-weight: 600; color: #666; }
        .filter-group input, .filter-group select { 
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; 
            font-size: 14px; min-width: 150px;
        }
        
        .table-responsive { overflow-x: auto; }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th { 
            background: #f8f9fa; padding: 15px; text-align: left; 
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .orders-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .orders-table tr:hover { background: #f8f9fa; }
        
        .order-number { font-weight: 600; color: #667eea; }
        .customer-info h6 { margin: 0; font-weight: 600; color: #333; font-size: 14px; }
        .customer-info small { color: #666; font-size: 12px; }
        
        .status-badge { 
            padding: 6px 12px; border-radius: 20px; font-size: 11px; 
            font-weight: 600; text-transform: uppercase; display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #d4edda; color: #155724; }
        .status-shipping { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .payment-badge { 
            padding: 4px 8px; border-radius: 12px; font-size: 10px; 
            font-weight: 600; text-transform: uppercase;
        }
        .payment-paid { background: #d4edda; color: #155724; }
        .payment-pending { background: #fff3cd; color: #856404; }
        .payment-failed { background: #f8d7da; color: #721c24; }
        
        .btn { 
            padding: 6px 10px; border: none; border-radius: 6px; 
            cursor: pointer; font-size: 11px; font-weight: 500; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
            transition: all 0.3s; margin: 2px;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { transform: translateY(-1px); opacity: 0.9; }
        
        .order-actions { display: flex; flex-wrap: wrap; gap: 4px; }
        
        .pagination { 
            display: flex; justify-content: center; align-items: center; 
            gap: 10px; margin-top: 20px; padding: 20px;
        }
        .pagination a, .pagination span { 
            padding: 8px 12px; border-radius: 6px; text-decoration: none; 
            background: white; border: 1px solid #ddd; color: #333;
        }
        .pagination .current { background: #667eea; color: white; border-color: #667eea; }
        
        .alert { 
            padding: 15px; border-radius: 8px; margin-bottom: 20px; 
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        @media (max-width: 768px) { 
            .main-content { margin-left: 0; padding: 15px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .filters { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span>🛒</span>
                Quản lý đơn hàng
            </h1>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <span><?php echo $message_type === 'success' ? '✅' : '⚠️'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Tổng đơn hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="stat-label">Chờ xử lý</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['confirmed_orders']); ?></div>
                <div class="stat-label">Đang xử lý</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['delivered_orders']); ?></div>
                <div class="stat-label">Đã giao</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatPrice($stats['today_revenue']); ?></div>
                <div class="stat-label">Doanh thu hôm nay</div>
            </div>
        </div>

        <!-- Orders -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Danh sách đơn hàng</h3>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="filters">
                <div class="filter-group">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" placeholder="Mã đơn, tên khách hàng..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                        <option value="shipping" <?php echo $status_filter === 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Ngày đặt</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <span>🔍</span> Lọc
                    </button>
                </div>
            </form>
            
            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                <span style="font-size: 48px; display: block; margin-bottom: 15px;">🛒</span>
                                Chưa có đơn hàng nào
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h6><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                                    <small><?php echo htmlspecialchars($order['customer_email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo number_format($order['items_count']); ?></strong> sản phẩm
                            </td>
                            <td>
                                <strong style="color: #28a745;">
                                    <?php echo formatPrice($order['total_amount']); ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                $status_names = [
                                    'pending' => 'Chờ xử lý',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipping' => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy'
                                ];
                                ?>
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo $status_names[$order['order_status']] ?? $order['order_status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $payment_names = [
                                    'pending' => 'Chờ thanh toán',
                                    'paid' => 'Đã thanh toán',
                                    'failed' => 'Thất bại'
                                ];
                                ?>
                                <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                    <?php echo $payment_names[$order['payment_status']] ?? $order['payment_status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <?php if ($order['order_status'] === 'pending'): ?>
                                    <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=confirmed" 
                                       class="btn btn-success" onclick="return confirm('Xác nhận đơn hàng này?')">
                                        <span>✅</span> Xác nhận
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['order_status'] === 'confirmed'): ?>
                                    <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=processing" 
                                       class="btn btn-info" onclick="return confirm('Chuyển sang đang xử lý?')">
                                        <span>⚙️</span> Xử lý
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['order_status'] === 'processing'): ?>
                                    <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=shipping" 
                                       class="btn btn-warning" onclick="return confirm('Chuyển sang đang giao?')">
                                        <span>🚚</span> Giao hàng
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['order_status'] === 'shipping'): ?>
                                    <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=delivered" 
                                       class="btn btn-success" onclick="return confirm('Đánh dấu đã giao?')">
                                        <span>🎉</span> Đã giao
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                    <a href="?action=update_status&id=<?php echo $order['id']; ?>&status=cancelled" 
                                       class="btn btn-danger" onclick="return confirm('Hủy đơn hàng này?')">
                                        <span>❌</span> Hủy
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                        <span>⬅️</span> Trước
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                        Sau <span>➡️</span>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>