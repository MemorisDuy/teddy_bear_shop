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
$coupon_id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// Add new coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $value = floatval($_POST['value']);
    $minimum_amount = floatval($_POST['minimum_amount'] ?? 0);
    $usage_limit = $_POST['usage_limit'] ? intval($_POST['usage_limit']) : null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    try {
        // Check if code already exists
        $existing = $db->fetch("SELECT id FROM coupons WHERE code = ?", [$code]);
        if ($existing) {
            $message = 'Mã giảm giá đã tồn tại!';
            $message_type = 'error';
        } else {
            $result = $db->execute(
                "INSERT INTO coupons (code, name, type, value, minimum_amount, usage_limit, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
                [$code, $name, $type, $value, $minimum_amount, $usage_limit, $start_date, $end_date]
            );
            if ($result) {
                $message = "Đã tạo mã giảm giá '$code' thành công!";
                $message_type = 'success';
            }
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Toggle status
if ($action === 'toggle_status' && $coupon_id) {
    try {
        $current = $db->fetch("SELECT status FROM coupons WHERE id = ?", [$coupon_id]);
        $new_status = $current['status'] === 'active' ? 'inactive' : 'active';
        $result = $db->execute("UPDATE coupons SET status = ? WHERE id = ?", [$new_status, $coupon_id]);
        if ($result) {
            $message = $new_status === 'active' ? 'Đã kích hoạt mã giảm giá!' : 'Đã vô hiệu hóa mã giảm giá!';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Delete coupon
if ($action === 'delete' && $coupon_id) {
    try {
        $coupon = $db->fetch("SELECT code FROM coupons WHERE id = ?", [$coupon_id]);
        $result = $db->execute("DELETE FROM coupons WHERE id = ?", [$coupon_id]);
        if ($result) {
            $message = "Đã xóa mã giảm giá '{$coupon['code']}'!";
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get coupons
try {
    $coupons = $db->fetchAll(
        "SELECT *, 
         CASE 
            WHEN end_date < CURDATE() THEN 'expired'
            WHEN start_date > CURDATE() THEN 'upcoming'
            ELSE 'current'
         END as time_status
         FROM coupons 
         ORDER BY created_at DESC"
    );
    
    // Get stats
    $stats = [
        'total_coupons' => count($coupons),
        'active_coupons' => count(array_filter($coupons, fn($c) => $c['status'] === 'active' && $c['time_status'] === 'current')),
        'expired_coupons' => count(array_filter($coupons, fn($c) => $c['time_status'] === 'expired')),
        'total_usage' => array_sum(array_column($coupons, 'usage_count'))
    ];

} catch (Exception $e) {
    $coupons = [];
    $stats = ['total_coupons' => 0, 'active_coupons' => 0, 'expired_coupons' => 0, 'total_usage' => 0];
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
    <title>Quản lý mã giảm giá - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Quicksand', 'Nunito', sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
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
        
        .content-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 25px; }
        .card-header { 
            padding: 20px; border-bottom: 1px solid #eee; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .card-title { font-size: 20px; font-weight: 600; color: #333; margin: 0; }
        
        .demo-section { 
            background: linear-gradient(135deg, #ff6b6b, #feca57); 
            color: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; 
        }
        .demo-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .demo-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .demo-btn { 
            background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); 
            color: white; padding: 8px 15px; border-radius: 20px; cursor: pointer; 
            font-size: 12px; transition: all 0.3s;
        }
        .demo-btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #666; }
        .form-group input, .form-group select { 
            padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; 
            font-size: 14px; font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        
        .quick-values { display: flex; gap: 8px; margin-top: 5px; flex-wrap: wrap; }
        .quick-value { 
            background: #f8f9fa; border: 1px solid #ddd; padding: 4px 8px; 
            border-radius: 4px; cursor: pointer; font-size: 12px; transition: all 0.3s;
        }
        .quick-value:hover { background: #667eea; color: white; }
        
        .btn { 
            padding: 8px 12px; border: none; border-radius: 6px; 
            cursor: pointer; font-size: 12px; font-weight: 500; 
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
            transition: all 0.3s; margin: 2px;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { transform: translateY(-1px); opacity: 0.9; }
        
        .alert { 
            padding: 15px; border-radius: 8px; margin-bottom: 20px; 
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        @media (max-width: 768px) { 
            .main-content { margin-left: 0; padding: 15px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tag"></i>
                Quản lý mã giảm giá
            </h1>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <span><?php echo $message_type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Quick Create Demo -->
        <div class="demo-section">
            <div class="demo-title"><i class="fas fa-bolt"></i> Tạo nhanh mã giảm giá mẫu (Click để thử):</div>
            <div class="demo-buttons">
                <div class="demo-btn" onclick="fillDemo('SAVE20', 'Giảm giá 20%', 'percentage', 20, 200000)">
                    SAVE20 - Giảm 20%
                </div>
                <div class="demo-btn" onclick="fillDemo('BEAR50K', 'Gấu bông giảm 50K', 'fixed', 50000, 300000)">
                    BEAR50K - Giảm 50.000đ
                </div>
                <div class="demo-btn" onclick="fillDemo('NEWBIE15', 'Khách hàng mới', 'percentage', 15, 0)">
                    NEWBIE15 - Khách mới 15%
                </div>
                <div class="demo-btn" onclick="fillDemo('FREESHIP', 'Miễn phí ship', 'fixed', 30000, 100000)">
                    FREESHIP - Free ship
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_coupons']); ?></div>
                <div class="stat-label">Tổng mã giảm giá</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['active_coupons']); ?></div>
                <div class="stat-label">Đang hoạt động</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['expired_coupons']); ?></div>
                <div class="stat-label">Đã hết hạn</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_usage']); ?></div>
                <div class="stat-label">Lượt sử dụng</div>
            </div>
        </div>

        <!-- Add Coupon Form -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i> Tạo mã giảm giá mới
                </h3>
            </div>
            
            <form method="POST" id="couponForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mã giảm giá *</label>
                        <input type="text" name="code" id="couponCode" required placeholder="VD: SUMMER2025" 
                               style="text-transform: uppercase;" maxlength="20">
                    </div>
                    
                    <div class="form-group">
                        <label>Tên mã giảm giá *</label>
                        <input type="text" name="name" id="couponName" required placeholder="VD: Giảm giá mùa hè">
                    </div>
                    
                    <div class="form-group">
                        <label>Loại giảm giá *</label>
                        <select name="type" id="discountType" required>
                            <option value="percentage">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (VNĐ)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Giá trị giảm *</label>
                        <input type="number" name="value" id="discountValue" required min="1" step="0.01" 
                               placeholder="VD: 10 (cho 10% hoặc 10,000đ)">
                        <div class="quick-values">
                            <div class="quick-value" onclick="setValue(5)">5%</div>
                            <div class="quick-value" onclick="setValue(10)">10%</div>
                            <div class="quick-value" onclick="setValue(15)">15%</div>
                            <div class="quick-value" onclick="setValue(20)">20%</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Đơn hàng tối thiểu</label>
                        <input type="number" name="minimum_amount" id="minimumAmount" min="0" step="1000" 
                               placeholder="0 = không giới hạn">
                        <div class="quick-values">
                            <div class="quick-value" onclick="setMinimum(0)">Không giới hạn</div>
                            <div class="quick-value" onclick="setMinimum(100000)">100K</div>
                            <div class="quick-value" onclick="setMinimum(200000)">200K</div>
                            <div class="quick-value" onclick="setMinimum(500000)">500K</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Giới hạn sử dụng</label>
                        <input type="number" name="usage_limit" id="usageLimit" min="1" 
                               placeholder="Để trống = không giới hạn">
                        <div class="quick-values">
                            <div class="quick-value" onclick="setUsage('')">Không giới hạn</div>
                            <div class="quick-value" onclick="setUsage(10)">10 lần</div>
                            <div class="quick-value" onclick="setUsage(50)">50 lần</div>
                            <div class="quick-value" onclick="setUsage(100)">100 lần</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ngày bắt đầu *</label>
                        <input type="date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Ngày kết thúc *</label>
                        <input type="date" name="end_date" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                </div>
                
                <div style="padding: 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="clearForm()" class="btn btn-warning">
                        <i class="fas fa-eraser"></i> Xóa form
                    </button>
                    <button type="submit" name="add_coupon" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tạo mã giảm giá
                    </button>
                </div>
            </form>
        </div>

        <!-- Coupons List -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Danh sách mã giảm giá (<?php echo count($coupons); ?>)</h3>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Mã & Tên</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Giá trị</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Điều kiện</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Thời gian</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Sử dụng</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Trạng thái</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                <span style="font-size: 48px; display: block; margin-bottom: 15px;"><i class="fas fa-tag"></i></span>
                                Chưa có mã giảm giá nào. Hãy tạo mã đầu tiên!
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($coupons as $coupon): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px; vertical-align: middle;">
                                <div style="font-family: 'Courier New', monospace; font-weight: bold; color: #667eea; background: #f8f9fa; padding: 5px 8px; border-radius: 4px; display: inline-block; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($coupon['code']); ?>
                                </div>
                                <br>
                                <small style="color: #666;"><?php echo htmlspecialchars($coupon['name']); ?></small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <strong style="font-size: 16px; color: #28a745;">
                                    <?php if ($coupon['type'] === 'percentage'): ?>
                                        <?php echo number_format($coupon['value'], 1); ?>%
                                    <?php else: ?>
                                        <?php echo formatPrice($coupon['value']); ?>
                                    <?php endif; ?>
                                </strong>
                                <br>
                                <small style="color: #666;">
                                    <?php echo $coupon['type'] === 'percentage' ? 'Phần trăm' : 'Cố định'; ?>
                                </small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; font-size: 12px;">
                                <?php if ($coupon['minimum_amount'] > 0): ?>
                                    Tối thiểu: <?php echo formatPrice($coupon['minimum_amount']); ?><br>
                                <?php else: ?>
                                    <span style="color: #28a745;">Không giới hạn</span><br>
                                <?php endif; ?>
                                
                                <?php if ($coupon['usage_limit']): ?>
                                    Giới hạn: <?php echo number_format($coupon['usage_limit']); ?> lần
                                <?php else: ?>
                                    <span style="color: #28a745;">Không giới hạn</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; font-size: 12px;">
                                <strong>Từ:</strong> <?php echo date('d/m/Y', strtotime($coupon['start_date'])); ?><br>
                                <strong>Đến:</strong> <?php echo date('d/m/Y', strtotime($coupon['end_date'])); ?>
                            </td>
                            <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                <strong style="color: #667eea; font-size: 18px;"><?php echo number_format($coupon['usage_count']); ?></strong>
                                <?php if ($coupon['usage_limit']): ?>
                                    <br><small>/ <?php echo number_format($coupon['usage_limit']); ?></small>
                                <?php endif; ?>
                                <br><small style="color: #666;">lần</small>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <?php
                                $status_class = $coupon['status'];
                                $status_text = $coupon['status'] === 'active' ? 'Hoạt động' : 'Vô hiệu hóa';
                                
                                if ($coupon['time_status'] === 'expired') {
                                    $status_class = 'expired';
                                    $status_text = 'Hết hạn';
                                } elseif ($coupon['time_status'] === 'upcoming') {
                                    $status_class = 'upcoming';
                                    $status_text = 'Sắp diễn ra';
                                }
                                
                                $badge_colors = [
                                    'active' => 'background: #d4edda; color: #155724;',
                                    'inactive' => 'background: #f8d7da; color: #721c24;',
                                    'expired' => 'background: #6c757d; color: white;',
                                    'upcoming' => 'background: #cce5ff; color: #004085;'
                                ];
                                ?>
                                <span style="padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; <?php echo $badge_colors[$status_class]; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td style="padding: 15px; vertical-align: middle;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <?php if ($coupon['time_status'] !== 'expired'): ?>
                                    <a href="?action=toggle_status&id=<?php echo $coupon['id']; ?>" 
                                       class="btn <?php echo $coupon['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>"
                                       onclick="return confirm('<?php echo $coupon['status'] === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?> mã này?')">
                                        <span><?php echo $coupon['status'] === 'active' ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>'; ?></span>
                                        <?php echo $coupon['status'] === 'active' ? 'Tạm dừng' : 'Kích hoạt'; ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete&id=<?php echo $coupon['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Xóa vĩnh viễn mã giảm giá này?')">
                                        <span>🗑️</span> Xóa
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function fillDemo(code, name, type, value, minimum) {
            document.getElementById('couponCode').value = code;
            document.getElementById('couponName').value = name;
            document.getElementById('discountType').value = type;
            document.getElementById('discountValue').value = value;
            document.getElementById('minimumAmount').value = minimum;
            
            // Scroll to form
            document.getElementById('couponForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        function setValue(value) {
            document.getElementById('discountValue').value = value;
        }
        
        function setMinimum(value) {
            document.getElementById('minimumAmount').value = value;
        }
        
        function setUsage(value) {
            document.getElementById('usageLimit').value = value;
        }
        
        function clearForm() {
            if (confirm('Xóa tất cả dữ liệu trong form?')) {
                document.getElementById('couponForm').reset();
            }
        }
        
        // Auto uppercase coupon code
        document.getElementById('couponCode').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html> 