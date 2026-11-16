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
$user_id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Delete user
if ($action === 'delete' && $user_id) {
    try {
        $result = $db->execute("UPDATE users SET status = 'inactive' WHERE id = ?", [$user_id]);
        if ($result) {
            $message = 'Đã vô hiệu hóa tài khoản khách hàng!';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Activate user
if ($action === 'activate' && $user_id) {
    try {
        $result = $db->execute("UPDATE users SET status = 'active' WHERE id = ?", [$user_id]);
        if ($result) {
            $message = 'Đã kích hoạt tài khoản khách hàng!';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    // Get total users count
    $total_result = $db->fetch("SELECT COUNT(*) as total FROM users");
    $total_users = $total_result['total'] ?? 0;
    $total_pages = ceil($total_users / $per_page);

    // Get users
    $users = $db->fetchAll(
        "SELECT id, username, email, full_name, phone, status, created_at, 
         (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders,
         (SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND payment_status = 'paid') as total_spent
         FROM users 
         ORDER BY created_at DESC 
         LIMIT ? OFFSET ?", 
        [$per_page, $offset]
    );

    // Get stats
    $stats = [
        'total_users' => $total_users,
        'active_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
        'inactive_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")['count'] ?? 0,
        'new_today' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0
    ];

} catch (Exception $e) {
    $users = [];
    $stats = ['total_users' => 0, 'active_users' => 0, 'inactive_users' => 0, 'new_today' => 0];
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
    <title>Quản lý khách hàng - Admin</title>
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
        .card-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: between; align-items: center; }
        .card-title { font-size: 20px; font-weight: 600; color: #333; margin: 0; }
        
        .table-responsive { overflow-x: auto; }
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table th { 
            background: #f8f9fa; padding: 15px; text-align: left; 
            font-weight: 600; color: #555; border-bottom: 2px solid #eee;
        }
        .users-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .users-table tr:hover { background: #f8f9fa; }
        
        .user-avatar { 
            width: 40px; height: 40px; border-radius: 50%; 
            background: linear-gradient(135deg, #667eea, #764ba2); 
            display: flex; align-items: center; justify-content: center; 
            color: white; font-weight: 600; margin-right: 12px;
        }
        .user-info { display: flex; align-items: center; }
        .user-details h6 { margin: 0; font-weight: 600; color: #333; }
        .user-details small { color: #666; }
        
        .status-badge { 
            padding: 6px 12px; border-radius: 20px; font-size: 12px; 
            font-weight: 600; text-transform: uppercase;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .action-btns { display: flex; gap: 8px; }
        .btn { 
            padding: 8px 12px; border: none; border-radius: 6px; 
            cursor: pointer; font-size: 12px; font-weight: 500; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
            transition: all 0.3s;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn:hover { transform: translateY(-1px); opacity: 0.9; }
        
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
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span>👥</span>
                Quản lý khách hàng
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
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Tổng khách hàng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['active_users']); ?></div>
                <div class="stat-label">Đang hoạt động</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['inactive_users']); ?></div>
                <div class="stat-label">Đã vô hiệu hóa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['new_today']); ?></div>
                <div class="stat-label">Mới hôm nay</div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Danh sách khách hàng</h3>
            </div>
            
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Email & Phone</th>
                            <th>Trạng thái</th>
                            <th>Đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Ngày tham gia</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                <span style="font-size: 48px; display: block; margin-bottom: 15px;">👥</span>
                                Chưa có khách hàng nào
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div class="user-details">
                                        <h6><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></h6>
                                        <small>@<?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($user['email']); ?></div>
                                <small style="color: #666;"><?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Vô hiệu hóa'; ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo number_format($user['total_orders'] ?? 0); ?></strong> đơn
                            </td>
                            <td>
                                <strong style="color: #28a745;">
                                    <?php echo function_exists('formatPrice') ? formatPrice($user['total_spent'] ?? 0) : number_format($user['total_spent'] ?? 0) . '₫'; ?>
                                </strong>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($user['status'] === 'active'): ?>
                                    <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Vô hiệu hóa tài khoản này?')">
                                        <span>🚫</span> Vô hiệu hóa
                                    </a>
                                    <?php else: ?>
                                    <a href="?action=activate&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-success"
                                       onclick="return confirm('Kích hoạt lại tài khoản này?')">
                                        <span>✅</span> Kích hoạt
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
                    <a href="?page=<?php echo $page-1; ?>">
                        <span>⬅️</span> Trước
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>">
                        Sau <span>➡️</span>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>