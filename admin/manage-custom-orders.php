<?php
/**
 * Admin: Quản lý yêu cầu đặt hàng theo mẫu riêng
 */

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/flash_messages.php';

// Check if logged in as admin
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

$page_title = 'Quản lý đơn đặt mẫu riêng - Admin';

// Get action
$action = $_GET['action'] ?? '';
$custom_order_id = intval($_GET['id'] ?? 0);

// Xử lý hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($action === 'update_message' && $custom_order_id > 0) {
        $shop_message = $_POST['shop_message'] ?? '';
        $status = $_POST['status'] ?? 'viewed';
        
        $db->query(
            "UPDATE custom_orders SET status = ?, shop_message = ?, updated_at = NOW() WHERE id = ?",
            [$status, $shop_message, $custom_order_id]
        );
        
        setFlashMessage('success', 'Cập nhật thành công!');
        header('Location: manage-custom-orders.php?id=' . $custom_order_id);
        exit;
    }
}

// Lấy danh sách orders
$page = intval($_GET['page'] ?? 1);
$per_page = 10;
$offset = ($page - 1) * $per_page;
$status_filter = $_GET['status'] ?? '';

$where_clause = '';
$params = [];
if (!empty($status_filter)) {
    $where_clause = 'WHERE status = ?';
    $params[] = $status_filter;
}

// Get total count
$total_result = $db->fetch("SELECT COUNT(*) as count FROM custom_orders $where_clause", $params);
$total_count = $total_result['count'];
$total_pages = ceil($total_count / $per_page);

// Get orders
$custom_orders = $db->fetchAll(
    "SELECT * FROM custom_orders $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?",
    array_merge($params, [$per_page, $offset])
);

// Lấy chi tiết 1 order nếu có ID
$current_order = null;
if ($custom_order_id > 0) {
    $current_order = $db->fetch("SELECT * FROM custom_orders WHERE id = ?", [$custom_order_id]);
}

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; color: #ff6b9d;">
                <span style="font-size: 1.5rem; margin-right: 10px;">🎨</span> Quản lý đơn đặt mẫu riêng
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                ← Quay lại Dashboard
            </a>
        </div>
    </div>
    
    <?php echo displayFlashMessage(); ?>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $stats = [
            ['label' => 'Tất cả yêu cầu', 'icon' => '📋', 'query' => 'SELECT COUNT(*) as count FROM custom_orders', 'color' => '#ff6b9d'],
            ['label' => 'Yêu cầu mới', 'icon' => '🆕', 'query' => "SELECT COUNT(*) as count FROM custom_orders WHERE status IN ('waiting_images', 'image_selected', 'confirmed')", 'color' => '#FFC107'],
            ['label' => 'Đã xem', 'icon' => '👀', 'query' => "SELECT COUNT(*) as count FROM custom_orders WHERE status = 'viewed'", 'color' => '#2196F3'],
            ['label' => 'Đã phản hồi', 'icon' => '💬', 'query' => "SELECT COUNT(*) as count FROM custom_orders WHERE status = 'responded'", 'color' => '#4CAF50'],
        ];
        
        foreach ($stats as $stat) {
            $result = $db->fetch($stat['query']);
            $count = $result['count'];
            echo "
            <div class='col-md-3 mb-3'>
                <div class='card' style='border-left: 4px solid {$stat['color']}; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                    <div class='card-body'>
                        <p class='card-text text-muted mb-2'>{$stat['icon']} {$stat['label']}</p>
                        <h3 class='card-title' style='color: {$stat['color']}; margin: 0;'>$count</h3>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
    
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <form method="GET" class="d-flex gap-2">
                        <select name="status" class="form-select" onchange="this.form.submit();"
                                style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                            <option value="">Tất cả trạng thái</option>
                            <option value="waiting_images" <?php echo $status_filter === 'waiting_images' ? 'selected' : ''; ?>>Chờ chọn ảnh</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Mới xác nhận</option>
                            <option value="viewed" <?php echo $status_filter === 'viewed' ? 'selected' : ''; ?>>Đã xem</option>
                            <option value="responded" <?php echo $status_filter === 'responded' ? 'selected' : ''; ?>>Đã phản hồi</option>
                            <option value="ordered" <?php echo $status_filter === 'ordered' ? 'selected' : ''; ?>>Đã đặt hàng</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Danh sách Orders -->
        <div class="col-md-<?php echo $current_order ? '6' : '12'; ?>">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white;">
                    <h5 class="mb-0" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                        📋 Danh sách yêu cầu (<?php echo count($custom_orders); ?>/<?php echo $total_count; ?>)
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Mẫu tìm kiếm</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_orders as $order): ?>
                            <tr style="<?php echo $order['status'] === 'new' ? 'background-color: #fffacd;' : ''; ?>">
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                        <div style="font-size: 0.85em; color: #999;">
                                            📧 <?php echo htmlspecialchars($order['customer_email']); ?>
                                            <br>
                                            📱 <?php echo htmlspecialchars($order['customer_phone']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="max-width: 150px;">
                                    <small style="color: #ff6b9d;">🔍 <?php echo htmlspecialchars(substr($order['search_keyword'], 0, 30)); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $status_badge = match($order['status']) {
                                        'waiting_images' => '<span class="badge bg-warning text-dark">⏳ Chờ chọn ảnh</span>',
                                        'image_selected' => '<span class="badge bg-warning text-dark">🖼️ Đã chọn ảnh</span>',
                                        'confirmed' => '<span class="badge bg-warning text-dark">🆕 Mới xác nhận</span>',
                                        'new' => '<span class="badge bg-warning text-dark">🆕 Mới</span>',
                                        'viewed' => '<span class="badge bg-info">👀 Đã xem</span>',
                                        'responded' => '<span class="badge bg-success">💬 Đã phản hồi</span>',
                                        'ordered' => '<span class="badge bg-primary">✅ Đã đặt hàng</span>',
                                        'rejected' => '<span class="badge bg-danger">❌ Từ chối</span>',
                                        default => '<span class="badge bg-secondary">' . htmlspecialchars($order['status']) . '</span>'
                                    };
                                    echo $status_badge;
                                    ?>
                                </td>
                                <td>
                                    <small style="color: #999;">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <span style="font-size: 0.9em;">👁️</span> Xem
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer" style="background-color: #f9f9f9;">
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $p; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>">
                                    <?php echo $p; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chi tiết Order -->
        <?php if ($current_order): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #4CAF50, #45a049); color: white;">
                    <h5 class="mb-0" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                        📝 Chi tiết yêu cầu #<?php echo $current_order['id']; ?>
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Thông tin khách hàng -->
                    <div class="mb-4">
                        <h6 style="font-weight: bold; color: #ff6b9d; margin-bottom: 10px;">👤 Thông tin khách hàng</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td style="font-weight: bold; width: 30%;">Tên:</td>
                                <td><?php echo htmlspecialchars($current_order['customer_name']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Email:</td>
                                <td><a href="mailto:<?php echo htmlspecialchars($current_order['customer_email']); ?>">
                                    <?php echo htmlspecialchars($current_order['customer_email']); ?>
                                </a></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Điện thoại:</td>
                                <td><a href="tel:<?php echo htmlspecialchars($current_order['customer_phone']); ?>">
                                    <?php echo htmlspecialchars($current_order['customer_phone']); ?>
                                </a></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">IP:</td>
                                <td><small style="color: #999;"><?php echo htmlspecialchars($current_order['ip_address']); ?></small></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Yêu cầu -->
                    <div class="mb-4">
                        <h6 style="font-weight: bold; color: #ff6b9d; margin-bottom: 10px;">🎨 Yêu cầu chi tiết</h6>
                        <p style="font-size: 0.95em; margin-bottom: 10px;">
                            <strong>Mẫu tìm kiếm:</strong><br>
                            <span style="background-color: #fff5f7; padding: 8px; border-radius: 4px; display: inline-block;">
                                🔍 <?php echo htmlspecialchars($current_order['search_keyword']); ?>
                            </span>
                        </p>
                        <p style="font-size: 0.95em;">
                            <strong>Mô tả:</strong><br>
                            <div style="background-color: #f9f9f9; padding: 12px; border-left: 3px solid #ff6b9d; border-radius: 4px; max-height: 200px; overflow-y: auto; font-size: 0.9em;">
                                <?php echo nl2br(htmlspecialchars($current_order['description'])); ?>
                            </div>
                        </p>
                    </div>
                    
                    <!-- Ảnh tham khảo -->
                    <?php if (!empty($current_order['image_url'])): ?>
                    <div class="mb-4">
                        <h6 style="font-weight: bold; color: #ff6b9d; margin-bottom: 10px;">🖼️ Hình ảnh khách tải lên</h6>
                        <img src="<?php echo htmlspecialchars($current_order['image_url']); ?>" alt="Preview" 
                             style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($current_order['selected_image_url'])): ?>
                    <div class="mb-4">
                        <h6 style="font-weight: bold; color: #ff6b9d; margin-bottom: 10px;">✨ Mẫu ảnh gợi ý khách đã chọn</h6>
                        <img src="<?php echo htmlspecialchars($current_order['selected_image_url']); ?>" alt="Selected Image" 
                             style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Thông tin khác -->
                    <div class="mb-4">
                        <h6 style="font-weight: bold; color: #ff6b9d; margin-bottom: 10px;">📅 Thông tin</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td style="font-weight: bold; width: 30%;">Ngày gửi:</td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($current_order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Trạng thái:</td>
                                <td>
                                    <?php
                                    $status_badge = match($current_order['status']) {
                                        'waiting_images' => '<span class="badge bg-warning text-dark">⏳ Chờ chọn ảnh</span>',
                                        'image_selected' => '<span class="badge bg-warning text-dark">🖼️ Đã chọn ảnh</span>',
                                        'confirmed' => '<span class="badge bg-warning text-dark">🆕 Mới xác nhận</span>',
                                        'new' => '<span class="badge bg-warning text-dark">🆕 Mới</span>',
                                        'viewed' => '<span class="badge bg-info">👀 Đã xem</span>',
                                        'responded' => '<span class="badge bg-success">💬 Đã phản hồi</span>',
                                        'ordered' => '<span class="badge bg-primary">✅ Đã đặt hàng</span>',
                                        'rejected' => '<span class="badge bg-danger">❌ Từ chối</span>',
                                        default => '<span class="badge bg-secondary">' . htmlspecialchars($current_order['status']) . '</span>'
                                    };
                                    echo $status_badge;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Đánh giá:</td>
                                <td>
                                    <?php
                                    if ($current_order['rating'] > 0) {
                                        echo str_repeat('⭐', $current_order['rating']) . " ({$current_order['rating']}/5)";
                                    } else {
                                        echo '<span style="color: #999;">Chưa đánh giá</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Form cập nhật -->
                    <form method="POST" action="?action=update_message&id=<?php echo $current_order['id']; ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label" style="font-weight: bold; color: #ff6b9d;">
                                Cập nhật trạng thái
                            </label>
                            <select name="status" id="status" class="form-select" 
                                    style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important;">
                                <option value="waiting_images" <?php echo $current_order['status'] === 'waiting_images' ? 'selected' : ''; ?>>⏳ Chờ chọn ảnh</option>
                                <option value="confirmed" <?php echo $current_order['status'] === 'confirmed' ? 'selected' : ''; ?>>🆕 Mới xác nhận</option>
                                <option value="viewed" <?php echo $current_order['status'] === 'viewed' ? 'selected' : ''; ?>>👀 Đã xem</option>
                                <option value="responded" <?php echo $current_order['status'] === 'responded' ? 'selected' : ''; ?>>💬 Đã phản hồi</option>
                                <option value="ordered" <?php echo $current_order['status'] === 'ordered' ? 'selected' : ''; ?>>✅ Đã đặt hàng</option>
                                <option value="rejected" <?php echo $current_order['status'] === 'rejected' ? 'selected' : ''; ?>>❌ Từ chối</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shopMessage" class="form-label" style="font-weight: bold; color: #ff6b9d;">
                                💬 Phản hồi từ shop
                            </label>
                            <textarea name="shop_message" id="shopMessage" class="form-control" rows="4"
                                      placeholder="Nhập phản hồi cho khách hàng (nếu có)..."
                                      style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; resize: vertical;">
                                <?php echo htmlspecialchars($current_order['shop_message'] ?? ''); ?>
                            </textarea>
                            <small class="text-muted">Phản hồi này sẽ được gửi cho khách hàng qua email</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100" 
                                style="font-family: 'Nunito', 'Poppins', Arial, sans-serif !important; font-weight: bold;">
                            <span style="font-size: 1.1em; margin-right: 5px;">💾</span> Lưu cập nhật
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
