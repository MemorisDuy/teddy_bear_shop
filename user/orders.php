<?php
/**
 * User Orders Page - Quản lý và theo dõi đơn hàng
 * Giao diện hiện đại, sạch sẽ, icon chuẩn FontAwesome
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
        SELECT oi.*, p.name as product_name, p.main_image as product_image
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
$page_title = 'Đơn hàng của tôi - ' . SITE_NAME;
$page_description = 'Quản lý và theo dõi tình trạng đơn hàng tại Teddy Bear Paradise.';

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h3 class="fw-bold text-dark mb-0">
                <i class="fas fa-boxes text-pink me-2"></i> Đơn hàng của tôi
            </h3>
            <a href="profile.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Hồ sơ cá nhân
            </a>
        </div>
        
        <?php if ($order_details): ?>
        <!-- Chi tiết một đơn hàng -->
        <div class="order-details-view">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">
                    Chi tiết đơn hàng #<?php echo $order_details['order_number']; ?>
                </h5>
                <a href="orders.php" class="btn btn-light border btn-sm rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> Quay lại danh sách
                </a>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Thông tin đơn hàng -->
                    <div class="p-4 bg-light rounded-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark mb-2">Thông tin nhận hàng</h6>
                                <p class="mb-1 small text-secondary"><strong>Người nhận:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                                <p class="mb-1 small text-secondary"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                                <p class="mb-1 small text-secondary"><strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?></p>
                                <p class="mb-0 small text-secondary"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                            </div>
                            <div class="col-md-6 border-start-md">
                                <h6 class="fw-bold text-dark mb-2">Trạng thái đơn</h6>
                                <p class="mb-1 small text-secondary"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?></p>
                                <p class="mb-1 small text-secondary"><strong>Tình trạng:</strong> 
                                    <span class="badge bg-pink text-white rounded-pill ms-1">
                                        <?php 
                                        $statuses = [
                                            'pending' => 'Chờ xác nhận',
                                            'confirmed' => 'Đã xác nhận',
                                            'processing' => 'Đang chuẩn bị hàng',
                                            'shipping' => 'Đang giao hàng',
                                            'delivered' => 'Đã giao thành công',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        echo $statuses[$order_details['order_status']] ?? $order_details['order_status'];
                                        ?>
                                    </span>
                                </p>
                                <p class="mb-0 small text-secondary"><strong>Thanh toán:</strong> 
                                    <span class="badge bg-<?php echo $order_details['payment_status'] === 'paid' ? 'success' : 'warning text-dark'; ?> rounded-pill ms-1">
                                        <?php echo $order_details['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danh sách sản phẩm -->
                    <h6 class="fw-bold text-dark mb-3">Sản phẩm trong đơn hàng</h6>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($order_items as $item): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white">
                            <div class="d-flex align-items-center">
                                <img src="../images/<?php echo $item['product_image'] ?: 'placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover;" 
                                     class="rounded-3 border me-3 flex-shrink-0"
                                     onerror="this.src='../images/placeholder.jpg'">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark small"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <span class="text-muted small">Số lượng: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></span>
                                </div>
                            </div>
                            <div class="fw-bold text-dark">
                                <?php echo formatPrice($item['total']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Tóm tắt thanh toán -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-receipt text-pink me-2"></i> Tóm tắt thanh toán</h6>
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Tạm tính:</span>
                            <span><?php echo formatPrice($order_details['subtotal']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                            <span>Phí vận chuyển:</span>
                            <span><?php echo $order_details['shipping_fee'] > 0 ? formatPrice($order_details['shipping_fee']) : 'Miễn phí'; ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold text-dark">Tổng thanh toán:</span>
                            <span class="fw-bold text-danger fs-5"><?php echo formatPrice($order_details['total_amount']); ?></span>
                        </div>
                        
                        <div class="small text-secondary mb-4">
                            <strong>Phương thức:</strong>
                            <div class="mt-1">
                                <?php 
                                $methods = [
                                    'cod' => 'Tiền mặt khi nhận hàng (COD)',
                                    'bank_transfer' => 'Chuyển khoản VietQR',
                                    'momo' => 'Ví MoMo',
                                    'zalopay' => 'Ví ZaloPay'
                                ];
                                echo $methods[$order_details['payment_method']] ?? $order_details['payment_method'];
                                ?>
                            </div>
                        </div>
                        
                        <?php if ($order_details['order_status'] === 'pending'): ?>
                        <button class="btn btn-outline-danger btn-sm rounded-pill w-100 py-2" onclick="cancelOrder(<?php echo $order_details['id']; ?>)">
                            <i class="fas fa-times me-1"></i> Hủy đơn hàng này
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Danh sách tất cả đơn hàng -->
        <div class="orders-list-view">
            <!-- Filter Tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="orders.php" class="btn btn-sm rounded-pill px-3 <?php echo !isset($_GET['status']) ? 'btn-primary' : 'btn-light border'; ?>">
                    Tất cả (<?php echo count($orders); ?>)
                </a>
                <a href="orders.php?status=pending" class="btn btn-sm rounded-pill px-3 <?php echo ($_GET['status'] ?? '') === 'pending' ? 'btn-primary' : 'btn-light border'; ?>">
                    Chờ xác nhận
                </a>
                <a href="orders.php?status=shipping" class="btn btn-sm rounded-pill px-3 <?php echo ($_GET['status'] ?? '') === 'shipping' ? 'btn-primary' : 'btn-light border'; ?>">
                    Đang giao
                </a>
                <a href="orders.php?status=delivered" class="btn btn-sm rounded-pill px-3 <?php echo ($_GET['status'] ?? '') === 'delivered' ? 'btn-primary' : 'btn-light border'; ?>">
                    Đã giao
                </a>
                <a href="orders.php?status=cancelled" class="btn btn-sm rounded-pill px-3 <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'btn-primary' : 'btn-light border'; ?>">
                    Đã hủy
                </a>
            </div>
            
            <?php 
            $filtered_orders = $orders;
            if (isset($_GET['status'])) {
                $filtered_orders = array_filter($orders, function($o) {
                    return $o['order_status'] === $_GET['status'];
                });
            }
            ?>
            
            <?php if (!empty($filtered_orders)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($filtered_orders as $ord): ?>
                    <div class="card border rounded-4 p-3 hover-shadow transition-all bg-white cursor-pointer" onclick="location.href='orders.php?view=<?php echo $ord['id']; ?>'">
                        <div class="row align-items-center g-3">
                            <div class="col-md-3">
                                <span class="fw-bold text-dark d-block">#<?php echo $ord['order_number']; ?></span>
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($ord['created_at'])); ?></small>
                            </div>
                            <div class="col-md-3">
                                <span class="fw-bold text-danger"><?php echo formatPrice($ord['total_amount']); ?></span>
                                <small class="text-muted d-block"><?php echo $ord['total_quantity'] ?? 1; ?> sản phẩm</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-<?php 
                                    echo $ord['order_status'] === 'delivered' ? 'success' : 
                                         ($ord['order_status'] === 'cancelled' ? 'danger' : 'warning text-dark'); 
                                ?> rounded-pill px-3 py-2">
                                    <?php 
                                    $st = [
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'processing' => 'Đang xử lý',
                                        'shipping' => 'Đang giao hàng',
                                        'delivered' => 'Đã giao thành công',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $st[$ord['order_status']] ?? $ord['order_status'];
                                    ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <a href="orders.php?view=<?php echo $ord['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-box-open text-pink fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Chưa có đơn hàng nào</h5>
                    <p class="text-muted mb-4">Bạn chưa có đơn hàng nào thuộc mục này. Hãy khám phá và mua sắm ngay nhé!</p>
                    <a href="../pages/products.php" class="btn btn-primary rounded-pill px-4 py-2">
                        <i class="fas fa-shopping-bag me-2"></i> Khám phá sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function cancelOrder(orderId) {
    if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) return;
    
    fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=cancel_order&order_id=' + encodeURIComponent(orderId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = 'orders.php';
        } else {
            alert(data.message || 'Không thể hủy đơn hàng!');
        }
    })
    .catch(() => alert('Có lỗi xảy ra, vui lòng thử lại!'));
}
</script>

<?php include '../includes/footer.php'; ?>
