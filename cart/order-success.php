<?php
/**
 * Order Success Page - Đặt hàng thành công
 * Giao diện hiện đại, sạch sẽ, icon chuẩn FontAwesome
 */
session_start();
require_once "../config/config.php";

$page_title = "Đặt hàng thành công - " . SITE_NAME;

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

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header Thông báo thành công -->
            <div class="text-center mb-5">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-check fs-1"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">
                    <?php echo $paid ? 'Thanh Toán Thành Công!' : 'Đặt Hàng Thành Công!'; ?>
                </h2>
                <p class="text-muted mb-3">
                    Cảm ơn bạn đã tin tưởng và mua sắm tại <strong><?php echo SITE_NAME; ?></strong>
                </p>
                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                    Mã đơn hàng: #<?php echo htmlspecialchars($order['order_number']); ?>
                </div>
            </div>
            
            <!-- Thẻ tóm tắt thông tin đơn hàng -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-pink text-white py-3 px-4">
                    <h5 class="fw-bold mb-0 text-white">
                        <i class="fas fa-receipt me-2"></i> Chi tiết đơn hàng
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end-md">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user text-pink me-2"></i> Thông tin người nhận</h6>
                            <p class="mb-1 text-secondary small"><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="mb-1 text-secondary small"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                            <p class="mb-1 text-secondary small"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p class="mb-0 text-secondary small"><strong>Địa chỉ giao:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-shopping-bag text-pink me-2"></i> Trạng thái & Thanh toán</h6>
                            <p class="mb-1 text-secondary small"><strong>Ngày tạo:</strong> <?php echo formatDateVN($order['created_at']); ?></p>
                            <p class="mb-1 text-secondary small"><strong>Trạng thái:</strong> 
                                <span class="badge bg-warning text-dark">Đang xử lý</span>
                            </p>
                            <p class="mb-1 text-secondary small"><strong>Phương thức:</strong> 
                                <?php 
                                $methods = [
                                    'cod' => 'Tiền mặt khi nhận (COD)',
                                    'bank_transfer' => 'Chuyển khoản VietQR',
                                    'momo' => 'Ví MoMo',
                                    'zalopay' => 'Ví ZaloPay'
                                ];
                                echo $methods[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </p>
                            <p class="mb-0 text-secondary small"><strong>Tổng thanh toán:</strong> 
                                <span class="fw-bold text-danger fs-5 ms-1"><?php echo formatPrice($order['total_amount']); ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['notes'])): ?>
                        <hr class="my-3">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold mb-1"><i class="fas fa-sticky-note me-1"></i> Ghi chú đơn hàng:</small>
                            <p class="mb-0 small text-secondary fst-italic"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Danh sách sản phẩm đặt mua -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-box-open text-pink me-2"></i> Sản phẩm đã mua (<?php echo $total_items; ?> sản phẩm)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted">
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../images/<?php echo $item['product_image'] ?: 'placeholder.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                                     class="rounded-3 border me-3 flex-shrink-0"
                                                     onerror="this.src='../images/placeholder.jpg'">
                                                <div>
                                                    <h6 class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <small class="text-muted">Mã SP: #<?php echo $item['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center small text-secondary"><?php echo formatPrice($item['price']); ?></td>
                                        <td class="text-center small fw-semibold"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end small fw-bold text-dark"><?php echo formatPrice($item['total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light small">
                                <tr>
                                    <td colspan="3" class="text-muted">Tạm tính:</td>
                                    <td class="text-end fw-semibold"><?php echo formatPrice($order['subtotal']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-muted">Phí vận chuyển:</td>
                                    <td class="text-end fw-semibold"><?php echo $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : '<span class="text-success">Miễn phí</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="fw-bold text-dark fs-6">TỔNG CỘNG:</td>
                                    <td class="text-end fw-bold text-danger fs-6"><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Điều hướng thao tác -->
            <div class="d-flex flex-wrap gap-3 justify-content-center mb-4">
                <a href="../pages/products.php" class="btn btn-primary rounded-pill px-4 py-2">
                    <i class="fas fa-shopping-bag me-2"></i> Tiếp tục mua sắm
                </a>
                <a href="../user/orders.php" class="btn btn-outline-primary rounded-pill px-4 py-2">
                    <i class="fas fa-list-alt me-2"></i> Lịch sử đơn hàng
                </a>
                <button class="btn btn-light border rounded-pill px-4 py-2" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> In hóa đơn
                </button>
            </div>
            
            <!-- Thông báo email -->
            <div class="alert alert-info rounded-4 p-3 text-center small mb-0">
                <i class="fas fa-envelope-open-text me-2"></i>
                Hóa đơn xác nhận chi tiết đã được tự động gửi đến <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong>.
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
