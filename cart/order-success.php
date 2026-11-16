<?php
session_start();
require_once "../config/config.php";

$page_title = "Đặt hàng thành công - Teddy Bear Shop";

// Lấy mã đơn hàng
$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    setFlashMessage('error', 'Không tìm thấy thông tin đơn hàng!');
    header('Location: ../pages/products.php');
    exit();
}

// Lấy thông tin đơn hàng
$order = $db->fetch(
    "SELECT * FROM orders WHERE order_number = ?", 
    [$order_number]
);

if (!$order) {
    setFlashMessage('error', 'Đơn hàng không tồn tại!');
    header('Location: ../pages/products.php');
    exit();
}

// Lấy chi tiết đơn hàng
$order_items = $db->fetchAll(
    "SELECT * FROM order_items WHERE order_id = ? ORDER BY id",
    [$order['id']]
);

// Tính toán thống kê đơn hàng
$total_items = array_sum(array_column($order_items, 'quantity'));
$estimated_delivery = date('d/m/Y', strtotime($order['created_at'] . ' +3 days'));

// Kiểm tra nếu vừa thanh toán thành công
$paid = isset($_GET['paid']) && $_GET['paid'] == '1';

include "../includes/header.php";
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Success Header -->
            <div class="text-center mb-5">
                <div class="success-animation mb-4">
                    <span style="font-size: 5rem;">✅</span>
                </div>
                <h1 class="text-success mb-3">🎉 <?php echo $paid ? 'Thanh toán thành công!' : 'Đặt hàng thành công!'; ?></h1>
                <p class="lead text-muted">
                    Cảm ơn bạn đã tin tưởng và mua sắm tại <strong>Teddy Bear Paradise</strong>
                </p>
                <?php if ($paid): ?>
                <div class="alert alert-success d-inline-block px-4 py-2 mb-3">
                    <span style="font-size: 1.2rem; margin-right: 8px;">💳</span>
                    <strong>Thanh toán đã được xác nhận!</strong>
                </div>
                <?php endif; ?>
                <div class="badge bg-success fs-6 px-4 py-2">
                    Đơn hàng #<?php echo $order['order_number']; ?> đã được <?php echo $paid ? 'thanh toán và ' : ''; ?>tiếp nhận
                </div>
            </div>
            
            <!-- Order Information Card -->
            <div class="order-card card mb-4">
                <div class="card-body">
                    <div class="row text-white">
                        <div class="col-md-6">
                            <h5><span style="font-size: 1.2rem; margin-right: 8px;">👤</span> Thông tin khách hàng</h5>
                            <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <p class="mb-1"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5><span style="font-size: 1.2rem; margin-right: 8px;">🛍️</span> Thông tin đơn hàng</h5>
                            <p class="mb-1"><strong>Mã đơn hàng:</strong> #<?php echo $order['order_number']; ?></p>
                            <p class="mb-1"><strong>Ngày đặt:</strong> <?php echo formatDateVN($order['created_at']); ?></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> 
                                <span class="badge bg-warning text-dark">
                                    <span style="font-size: 1rem; margin-right: 5px;">⏰</span> Chờ xác nhận
                                </span>
                            </p>
                            <p class="mb-1"><strong>Thanh toán:</strong> 
                                <?php 
                                $payment_methods = [
                                    'cod' => '💵 COD',
                                    'bank_transfer' => '🏦 Chuyển khoản',
                                    'momo' => '📱 MoMo',
                                    'zalopay' => '💳 ZaloPay'
                                ];
                                $payment_text = $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                if ($order['payment_status'] === 'paid') {
                                    $payment_text .= ' <span class="badge bg-success">Đã thanh toán</span>';
                                } else {
                                    $payment_text .= ' <span class="badge bg-warning text-dark">Chưa thanh toán</span>';
                                }
                                echo $payment_text;
                                ?>
                            </p>
                            <p class="mb-0"><strong>Tổng tiền:</strong> 
                                <span class="h5 text-warning"><?php echo formatPrice($order['total_amount']); ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['notes'])): ?>
                        <hr class="border-light">
                        <div class="text-white">
                            <h6><span style="font-size: 1rem; margin-right: 5px;">📝</span> Ghi chú:</h6>
                            <p class="mb-0 fst-italic"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><span style="font-size: 1.2rem; margin-right: 8px;">🛣️</span> Trạng thái đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 text-success">✅ Đặt hàng thành công</h6>
                                    <small class="text-muted"><?php echo formatDateVN($order['created_at']); ?></small>
                                </div>
                                <span class="badge bg-success">Hoàn thành</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item pending">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 text-warning">⏳ Xác nhận đơn hàng</h6>
                                    <small class="text-muted">Đang chờ shop xác nhận</small>
                                </div>
                                <span class="badge bg-warning text-dark">Đang xử lý</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item future">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 text-muted">📦 Chuẩn bị hàng</h6>
                                    <small class="text-muted">Đóng gói và chuẩn bị giao hàng</small>
                                </div>
                                <span class="badge bg-secondary">Chưa bắt đầu</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item future">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 text-muted">🚚 Đang giao hàng</h6>
                                    <small class="text-muted">Shipper đang giao đến bạn</small>
                                </div>
                                <span class="badge bg-secondary">Chưa bắt đầu</span>
                            </div>
                        </div>
                        
                        <div class="timeline-item future">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 text-muted">🎯 Giao hàng thành công</h6>
                                    <small class="text-muted">Dự kiến: <?php echo $estimated_delivery; ?></small>
                                </div>
                                <span class="badge bg-secondary">Chưa bắt đầu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><span style="font-size: 1.2rem; margin-right: 8px;">📋</span> Chi tiết sản phẩm (<?php echo $total_items; ?> sản phẩm)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr class="product-item">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="product-image me-3">
                                                    <img src="../images/<?php echo $item['product_image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         style="width: 60px; height: 60px; object-fit: cover;" 
                                                         class="rounded border">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <small class="text-muted">Mã SP: #<?php echo $item['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <strong><?php echo formatPrice($item['price']); ?></strong>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-primary"><?php echo $item['quantity']; ?></span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <strong class="text-primary"><?php echo formatPrice($item['total']); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3"><strong>Tạm tính:</strong></td>
                                    <td class="text-end"><strong><?php echo formatPrice($order['subtotal']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3"><strong>Phí vận chuyển:</strong></td>
                                    <td class="text-end">
                                        <strong>
                                            <?php echo $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : '<span class="text-success">Miễn phí</span>'; ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="3"><strong class="text-success">TỔNG CỘNG:</strong></td>
                                    <td class="text-end">
                                        <strong class="text-success h5"><?php echo formatPrice($order['total_amount']); ?></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Payment Instructions -->
            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5><span style="font-size: 1.2rem; margin-right: 8px;">🏦</span> Hướng dẫn chuyển khoản</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">💳 Thông tin tài khoản nhận:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Ngân hàng:</strong> Vietcombank Chi nhánh TP.HCM</p>
                                    <p class="mb-2"><strong>Số tài khoản:</strong> 
                                        <code class="bg-light text-dark p-1 rounded">1234567890</code>
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('1234567890')">
                                            <span style="font-size: 1rem; margin-right: 5px;">📋</span>
                                        </button>
                                    </p>
                                    <p class="mb-2"><strong>Chủ tài khoản:</strong> TEDDY BEAR PARADISE</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Số tiền:</strong> 
                                        <code class="bg-light text-dark p-1 rounded"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?></code>
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo $order['total_amount']; ?>')">
                                            <span style="font-size: 1rem; margin-right: 5px;">📋</span>
                                        </button>
                                    </p>
                                    <p class="mb-2"><strong>Nội dung:</strong> 
                                        <code class="bg-light text-dark p-1 rounded"><?php echo $order['order_number']; ?></code>
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo $order['order_number']; ?>')">
                                            <span style="font-size: 1rem; margin-right: 5px;">📋</span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-0">
                                <span style="font-size: 1rem; margin-right: 5px;">ℹ️</span>
                                <strong>Lưu ý:</strong> Đơn hàng sẽ được xử lý ngay sau khi chúng tôi nhận được thanh toán.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Delivery Information -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5><span style="font-size: 1.2rem; margin-right: 8px;">🚚</span> Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📍 Địa chỉ giao hàng:</h6>
                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            
                            <h6>⏰ Thời gian giao hàng dự kiến:</h6>
                            <ul class="list-unstyled">
                                <li><span style="font-size: 1rem; margin-right: 5px;">⏰</span> Nội thành TP.HCM: <strong>1-2 ngày</strong></li>
                                <li><span style="font-size: 1rem; margin-right: 5px;">⏰</span> Các tỉnh thành khác: <strong>2-5 ngày</strong></li>
                                <li><span style="font-size: 1rem; margin-right: 5px;">📅</span> Dự kiến giao: <strong><?php echo $estimated_delivery; ?></strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>📞 Hotline hỗ trợ:</h6>
                            <p class="mb-2">
                                <span style="font-size: 1rem; margin-right: 5px;">📞</span>
                                <strong>0123-456-789</strong> (8:00 - 22:00)
                            </p>
                            <p class="mb-2">
                                <span style="font-size: 1rem; margin-right: 5px;">✉️</span>
                                <strong>support@teddyparadise.com</strong>
                            </p>
                            <p class="mb-3">
                                <span style="font-size: 1rem; margin-right: 5px;">📍</span>
                                <strong>123 Đường Cute, Quận 1, TP.HCM</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <a href="../pages/products.php" class="btn btn-primary btn-lg w-100">
                        <span style="font-size: 1rem; margin-right: 5px;">🛒</span> Tiếp tục mua sắm
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../user/orders.php" class="btn btn-outline-primary btn-lg w-100">
                        <span style="font-size: 1rem; margin-right: 5px;">📋</span> Đơn hàng của tôi
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <button class="btn btn-outline-success btn-lg w-100" onclick="printOrder()">
                        <span style="font-size: 1rem; margin-right: 5px;">🖨️</span> In đơn hàng
                    </button>
                </div>
            </div>
            
            <!-- Email Confirmation Notice -->
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <span style="font-size: 2rem; margin-right: 12px;">📧</span>
                    <div>
                        <h6 class="alert-heading mb-1">📧 Email xác nhận đã được gửi!</h6>
                        <p class="mb-0">
                            Chúng tôi đã gửi email xác nhận đến <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong>. 
                            Vui lòng kiểm tra hộp thư (kể cả thư mục spam).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>