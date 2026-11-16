<?php
/**
 * User Orders Page - Đơn hàng của tôi 📦
 */

require_once '../config/config.php';

// Function to get orders by user
function getOrdersByUser($user_id) {
    global $db;
    return $db->fetchAll("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               SUM(oi.quantity) as total_quantity
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ", [$user_id]);
}

// Function to get order by ID
function getOrderById($order_id, $user_id) {
    global $db;
    return $db->fetch("
        SELECT * FROM orders 
        WHERE id = ? AND user_id = ?
    ", [$order_id, $user_id]);
}

if (!function_exists('getOrderItems')) {
function getOrderItems($order_id) {
    global $db;
    return $db->fetchAll("
        SELECT oi.*, p.name as product_name, p.image as product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ", [$order_id]);
}
}

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('../auth/login.php');
}

// Get user orders
$user_id = $_SESSION['user_id'];

// Handle AJAX order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_order') {
    header('Content-Type: application/json; charset=utf-8');
    $cancel_id = intval($_POST['order_id'] ?? 0);
    $order_to_cancel = getOrderById($cancel_id, $user_id);
    if (!$order_to_cancel) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại!']);
        exit;
    }
    if ($order_to_cancel['order_status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng đang chờ xác nhận!']);
        exit;
    }
    $updated = $db->execute("UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?", [$cancel_id, $user_id]);
    if ($updated) {
        // Hoàn lại tồn kho
        $items = $db->fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$cancel_id]);
        foreach ($items as $it) {
            $db->execute("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?", [$it['quantity'], $it['product_id']]);
        }
        echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng!']);
    }
    exit;
}

$orders = getOrdersByUser($user_id);

// Get order details if requested
$order_details = null;
$order_items = [];
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $order_id = intval($_GET['view']);
    $order_details = getOrderById($order_id, $user_id);
    if ($order_details) {
        $order_items = getOrderItems($order_id);
    }
}

// SEO Meta
$page_title = 'Đơn hàng của tôi';
$page_description = 'Quản lý và theo dõi tình trạng đơn hàng tại Teddy Bear Paradise.';

// Custom CSS
$custom_css = "
    .orders-container {
        padding: 50px 0;
        background: #f8f9fa;
    }
    
    .orders-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .order-item {
        background: white;
        border: 2px solid #f0f0f0;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .order-item:hover {
        border-color: #ff6b6b;
        box-shadow: 0 5px 15px rgba(255, 107, 107, 0.2);
        transform: translateY(-2px);
    }
    
    .order-status {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-confirmed {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .status-processing {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    .status-shipping {
        background: #cce5ff;
        color: #004085;
        border: 1px solid #99d3ff;
    }
    
    .status-delivered {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .order-timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline-step {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        position: relative;
    }
    
    .timeline-step::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 30px;
        width: 2px;
        height: 40px;
        background: #e9ecef;
    }
    
    .timeline-step:last-child::before {
        display: none;
    }
    
    .timeline-step.completed::before {
        background: #28a745;
    }
    
    .timeline-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 12px;
        z-index: 1;
    }
    
    .timeline-step.completed .timeline-icon {
        background: #28a745;
        color: white;
    }
    
    .timeline-step.active .timeline-icon {
        background: #ff6b6b;
        color: white;
        animation: pulse 2s infinite;
    }
    
    .order-summary {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .filter-tabs {
        margin-bottom: 30px;
    }
    
    .filter-tab {
        background: white;
        border: 2px solid #e9ecef;
        color: #666;
        padding: 10px 20px;
        border-radius: 10px;
        margin-right: 10px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .filter-tab:hover,
    .filter-tab.active {
        border-color: #ff6b6b;
        background: #ff6b6b;
        color: white;
    }
";

include '../includes/header.php';
?>

<div class="orders-container">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="orders-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold">📦 Đơn hàng của tôi</h3>
                        <a href="profile.php" class="btn btn-outline-primary">
                            <span>👤</span> Quay lại hồ sơ
                        </a>
                    </div>
                    
                    <?php if ($order_details): ?>
                    <!-- Order Details View -->
                    <div class="order-details">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4>Chi tiết đơn hàng #<?php echo $order_details['order_number']; ?></h4>
                            <a href="orders.php" class="btn btn-secondary">
                                <span>⬅️</span> Quay lại danh sách
                            </a>
                        </div>
                        
                        <div class="row">
                            <!-- Order Info -->
                            <div class="col-lg-8">
                                <div class="order-info mb-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold">Thông tin đơn hàng</h6>
                                            <p class="mb-1"><strong>Mã đơn hàng:</strong> <?php echo $order_details['order_number']; ?></p>
                                            <p class="mb-1"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?></p>
                                            <p class="mb-1"><strong>Trạng thái:</strong> 
                                                <span class="order-status status-<?php echo $order_details['order_status']; ?>">
                                                    <?php 
                                                    $status_text = [
                                                        'pending' => '⏳ Chờ xác nhận',
                                                        'confirmed' => '✅ Đã xác nhận',
                                                        'processing' => '📦 Đang xử lý',
                                                        'shipping' => '🚚 Đang giao',
                                                        'delivered' => '✅ Đã giao',
                                                        'cancelled' => '❌ Đã hủy'
                                                    ];
                                                    echo $status_text[$order_details['order_status']] ?? $order_details['order_status'];
                                                    ?>
                                                </span>
                                            </p>
                                            <p class="mb-1"><strong>Thanh toán:</strong> 
                                                <span class="order-status status-<?php echo $order_details['payment_status']; ?>">
                                                    <?php 
                                                    $payment_text = [
                                                        'pending' => '⏳ Chờ thanh toán',
                                                        'paid' => '✅ Đã thanh toán',
                                                        'failed' => '❌ Thất bại'
                                                    ];
                                                    echo $payment_text[$order_details['payment_status']] ?? $order_details['payment_status'];
                                                    ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold">Thông tin giao hàng</h6>
                                            <p class="mb-1"><strong>Người nhận:</strong> <?php echo $order_details['customer_name']; ?></p>
                                            <p class="mb-1"><strong>Điện thoại:</strong> <?php echo $order_details['customer_phone']; ?></p>
                                            <p class="mb-1"><strong>Email:</strong> <?php echo $order_details['customer_email']; ?></p>
                                            <p class="mb-1"><strong>Địa chỉ:</strong> <?php echo $order_details['shipping_address']; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Items -->
                                <div class="order-items">
                                    <h6 class="fw-bold mb-3">Sản phẩm đã đặt</h6>
                                    <?php foreach ($order_items as $item): ?>
                                    <div class="order-item-detail d-flex align-items-center mb-3 p-3 border rounded">
                                        <img src="../images/<?php echo $item['product_image']; ?>" 
                                             alt="<?php echo $item['product_name']; ?>" 
                                             width="80" class="rounded me-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo $item['product_name']; ?></h6>
                                            <p class="text-muted mb-1">Số lượng: <?php echo $item['quantity']; ?></p>
                                            <p class="text-muted mb-0">Đơn giá: <?php echo formatPrice($item['price']); ?></p>
                                        </div>
                                        <div class="text-end">
                                            <strong><?php echo formatPrice($item['total']); ?></strong>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Order Timeline -->
                                <div class="order-timeline mt-4">
                                    <h6 class="fw-bold mb-3">📍 Tiến trình đơn hàng</h6>
                                    <div class="timeline-step completed">
                                        <div class="timeline-icon">
                                            <span>✓</span>
                                        </div>
                                        <div>
                                            <strong>Đặt hàng thành công</strong>
                                            <div class="text-muted small">
                                                <?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (in_array($order_details['order_status'], ['confirmed', 'processing', 'shipping', 'delivered'])): ?>
                                    <div class="timeline-step completed">
                                        <div class="timeline-icon">
                                            <span>✓</span>
                                        </div>
                                        <div>
                                            <strong>Xác nhận đơn hàng</strong>
                                            <div class="text-muted small">Đơn hàng đã được xác nhận</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order_details['order_status'], ['processing', 'shipping', 'delivered'])): ?>
                                    <div class="timeline-step completed">
                                        <div class="timeline-icon">
                                            <span>📦</span>
                                        </div>
                                        <div>
                                            <strong>Đang chuẩn bị hàng</strong>
                                            <div class="text-muted small">Sản phẩm đang được đóng gói</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order_details['order_status'], ['shipping', 'delivered'])): ?>
                                    <div class="timeline-step <?php echo $order_details['order_status'] === 'shipping' ? 'active' : 'completed'; ?>">
                                        <div class="timeline-icon">
                                            <span>🚚</span>
                                        </div>
                                        <div>
                                            <strong>Đang giao hàng</strong>
                                            <div class="text-muted small">Đơn hàng đang trên đường giao đến bạn</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($order_details['order_status'] === 'delivered'): ?>
                                    <div class="timeline-step completed">
                                        <div class="timeline-icon">
                                            <span>🏠</span>
                                        </div>
                                        <div>
                                            <strong>Giao hàng thành công</strong>
                                            <div class="text-muted small">Đơn hàng đã được giao thành công</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($order_details['order_status'] === 'cancelled'): ?>
                                    <div class="timeline-step" style="opacity: 0.6;">
                                        <div class="timeline-icon" style="background: #dc3545;">
                                            <span>✖</span>
                                        </div>
                                        <div>
                                            <strong>Đơn hàng đã bị hủy</strong>
                                            <div class="text-muted small">Đơn hàng đã được hủy</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Order Summary -->
                            <div class="col-lg-4">
                                <div class="order-summary">
                                    <h6 class="fw-bold mb-3">💰 Tóm tắt thanh toán</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tạm tính:</span>
                                        <span><?php echo formatPrice($order_details['subtotal']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phí vận chuyển:</span>
                                        <span><?php echo formatPrice($order_details['shipping_fee']); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Tổng cộng:</strong>
                                        <strong class="text-primary"><?php echo formatPrice($order_details['total_amount']); ?></strong>
                                    </div>
                                    
                                    <div class="payment-info mb-3">
                                        <strong>Phương thức thanh toán:</strong>
                                        <div class="mt-1">
                                            <?php 
                                            $payment_methods = [
                                                'cod' => '💰 Thanh toán khi nhận hàng',
                                                'bank_transfer' => '🏦 Chuyển khoản ngân hàng',
                                                'momo' => '📱 Ví MoMo',
                                                'zalopay' => '💙 ZaloPay'
                                            ];
                                            echo $payment_methods[$order_details['payment_method']] ?? $order_details['payment_method'];
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($order_details['notes']): ?>
                                    <div class="order-notes">
                                        <strong>Ghi chú:</strong>
                                        <div class="mt-1 text-muted"><?php echo nl2br(htmlspecialchars($order_details['notes'])); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Action Buttons -->
                                    <div class="order-actions mt-4">
                                        <?php if ($order_details['order_status'] === 'pending'): ?>
                                        <button class="btn btn-danger btn-sm w-100 mb-2" onclick="cancelOrder(<?php echo $order_details['id']; ?>)">
                                            <span>❌</span> Hủy đơn hàng
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($order_details['order_status'] === 'delivered'): ?>
                                        <a href="../pages/products.php" class="btn btn-primary btn-sm w-100 mb-2">
                                            <span>🔄</span> Mua lại
                                        </a>
                                        <?php endif; ?>
                                        
                                        <a href="../pages/contact.php" class="btn btn-outline-primary btn-sm w-100">
                                            <span>🎧</span> Liên hệ hỗ trợ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- Orders List View -->
                    <div class="orders-list">
                        <!-- Filter Tabs -->
                        <div class="filter-tabs">
                            <a href="orders.php" class="filter-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
                                Tất cả (<?php echo count($orders); ?>)
                            </a>
                            <a href="orders.php?status=pending" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'pending' ? 'active' : ''; ?>">
                                Chờ xác nhận
                            </a>
                            <a href="orders.php?status=shipping" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'shipping' ? 'active' : ''; ?>">
                                Đang giao
                            </a>
                            <a href="orders.php?status=delivered" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'delivered' ? 'active' : ''; ?>">
                                Đã giao
                            </a>
                            <a href="orders.php?status=cancelled" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'active' : ''; ?>">
                                Đã hủy
                            </a>
                        </div>
                        
                        <?php if (!empty($orders)): ?>
                            <?php 
                            // Filter orders by status if specified
                            $filtered_orders = $orders;
                            if (isset($_GET['status'])) {
                                $filtered_orders = array_filter($orders, function($order) {
                                    return $order['order_status'] === $_GET['status'];
                                });
                            }
                            ?>
                            
                            <?php if (!empty($filtered_orders)): ?>
                                <?php foreach ($filtered_orders as $order): ?>
                                <div class="order-item" onclick="location.href='orders.php?view=<?php echo $order['id']; ?>'">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="order-info">
                                                <h6 class="fw-bold mb-1">#<?php echo $order['order_number']; ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="order-total">
                                                <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                                                <br><small class="text-muted">
                                                    <?php 
                                                    $item_count = $db->fetch("SELECT SUM(quantity) as total FROM order_items WHERE order_id = ?", [$order['id']]);
                                                    echo $item_count['total'] ?? 0;
                                                    ?> sản phẩm
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="order-status status-<?php echo $order['order_status']; ?>">
                                                <?php 
                                                $status_text = [
                                                    'pending' => '⏳ Chờ xác nhận',
                                                    'confirmed' => '✅ Đã xác nhận',
                                                    'processing' => '📦 Đang xử lý',
                                                    'shipping' => '🚚 Đang giao',
                                                    'delivered' => '✅ Đã giao',
                                                    'cancelled' => '❌ Đã hủy'
                                                ];
                                                echo $status_text[$order['order_status']] ?? $order['order_status'];
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button class="btn btn-outline-primary btn-sm">
                                                <span>👁️</span> Xem chi tiết
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="no-orders text-center py-5">
                                <span style="font-size: 4rem; display: block;">📦</span>
                                <h5 class="mt-3">Không có đơn hàng nào</h5>
                                <p class="text-muted">Không tìm thấy đơn hàng nào với trạng thái này.</p>
                                <a href="orders.php" class="btn btn-primary">Xem tất cả đơn hàng</a>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                        <div class="no-orders text-center py-5">
                            <span style="font-size: 4rem; display: block;">🛒</span>
                            <h5 class="mt-3">Chưa có đơn hàng nào</h5>
                            <p class="text-muted">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                            <a href="../pages/products.php" class="btn btn-primary btn-lg">
                                <span>🛍️</span> Mua sắm ngay
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>