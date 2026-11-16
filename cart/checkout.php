<?php
session_start();
require_once "../config/config.php";
require_once "../config/cart_functions.php";
require_once "../includes/email_functions.php";

$page_title = "Thanh toán - Teddy Bear Shop";

// Lấy user_id nếu đã đăng nhập
$user_id = $_SESSION['user_id'] ?? null;

// Lấy thông tin giỏ hàng
$cart_items = getCartItems($user_id);
$cart_summary = getCartSummary($user_id);

// Lấy thông tin giảm giá từ session nếu có (nhưng không áp dụng ngay, chỉ khi user nhấn áp dụng)
$applied_coupon = $_SESSION['applied_coupon'] ?? null;
$discount = 0; // Ban đầu không có discount
$cart_summary['discount'] = 0;
// Tổng cộng ban đầu = subtotal + shipping_fee (chưa trừ discount)
$cart_summary['total_after_coupon'] = $cart_summary['subtotal'] + $cart_summary['shipping_fee'];

// Kiểm tra giỏ hàng có trống không
if (empty($cart_items)) {
    setFlashMessage('error', 'Giỏ hàng của bạn đang trống!');
    header('Location: ../cart/index.php');
    exit();
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $errors = [];
    
    // Validate dữ liệu
    $customer_name = sanitizeInput($_POST['customer_name'] ?? '');
    $customer_email = sanitizeInput($_POST['customer_email'] ?? '');
    $customer_phone = sanitizeInput($_POST['customer_phone'] ?? '');
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? 'cod');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validation chi tiết
    if (empty($customer_name)) {
        $errors[] = "Vui lòng nhập họ tên";
    } elseif (strlen($customer_name) < 2) {
        $errors[] = "Họ tên phải có ít nhất 2 ký tự";
    }
    
    if (empty($customer_email)) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!validateEmail($customer_email)) {
        $errors[] = "Email không đúng định dạng";
    }
    
    if (empty($customer_phone)) {
        $errors[] = "Vui lòng nhập số điện thoại";
    } elseif (!validatePhone($customer_phone)) {
        $errors[] = "Số điện thoại không đúng định dạng (10-11 số)";
    }
    
    if (empty($shipping_address)) {
        $errors[] = "Vui lòng nhập địa chỉ giao hàng";
    } elseif (strlen($shipping_address) < 10) {
        $errors[] = "Địa chỉ giao hàng quá ngắn";
    }
    
    if (!in_array($payment_method, ['cod', 'bank_transfer', 'momo', 'zalopay'])) {
        $errors[] = "Phương thức thanh toán không hợp lệ";
    }
    
    // Validate cart items
    $cart_errors = validateCartItems($user_id);
    if (!empty($cart_errors)) {
        foreach ($cart_errors as $cart_error) {
            $errors[] = $cart_error['message'];
        }
    }
    
    // Kiểm tra email đã tồn tại chưa (nếu chưa đăng nhập)
    if (!$user_id) {
        $existing_user = $db->fetch("SELECT id FROM users WHERE email = ?", [$customer_email]);
        if ($existing_user) {
            $errors[] = "Email này đã được đăng ký. Vui lòng đăng nhập hoặc sử dụng email khác.";
        }
    }
    
    if (empty($errors)) {
        try {
            // Bắt đầu transaction để đảm bảo dữ liệu đồng bộ
            $db->beginTransaction();
            
            // Tạo mã đơn hàng unique
            do {
                $order_number = 'TB' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $exists = $db->exists('orders', 'order_number = ?', [$order_number]);
            } while ($exists);
            
            // Tính toán chi tiết
            $subtotal = $cart_summary['subtotal'];
            $shipping_fee = $cart_summary['shipping_fee'];
            
            // Lấy discount từ session nếu có mã giảm giá đã áp dụng
            $applied_coupon = $_SESSION['applied_coupon'] ?? null;
            $discount_amount = $applied_coupon['discount'] ?? 0;
            
            // Tính total_amount sau khi trừ discount: (subtotal - discount) + shipping_fee
            $total_amount = max(0, ($subtotal - $discount_amount) + $shipping_fee);
            
            // Thêm thông tin mã giảm giá vào notes nếu có
            $order_notes = $notes;
            if ($applied_coupon && $discount_amount > 0) {
                $coupon_info = "Mã giảm giá: {$applied_coupon['code']} (-" . number_format($discount_amount) . "đ)";
                $order_notes = $order_notes ? $order_notes . "\n" . $coupon_info : $coupon_info;
            }
            
            // Tạo đơn hàng
            $order_id = $db->insert(
                "INSERT INTO orders (
                    order_number, user_id, customer_name, customer_email, customer_phone, 
                    shipping_address, subtotal, shipping_fee, total_amount, payment_method, 
                    notes, order_status, payment_status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', NOW())",
                [
                    $order_number, $user_id, $customer_name, $customer_email, $customer_phone,
                    $shipping_address, $subtotal, $shipping_fee, $total_amount, $payment_method, $order_notes
                ]
            );
            
            if (!$order_id) {
                throw new Exception("Không thể tạo đơn hàng. Vui lòng thử lại.");
            }
            
            // Thêm chi tiết đơn hàng và cập nhật inventory
            $total_items = 0;
            $order_weight = 0;
            
            // Map product_id với ảnh tương ứng (giống cart.php)
            $product_images = [
                1 => 'teddy-01.jpg',
                2 => 'teddy-02.jpg',
                3 => 'category-panda.jpg',
                4 => 'polar-01.jpg',
                5 => 'koala-01.jpg',
                6 => 'teddy-blue.jpg',
                7 => 'teddy-01.jpg',
                8 => 'bunny-bear.jpg',
                9 => 'unicorn-bear.jpg',
                10 => 'couple-bears.jpg',
                11 => 'christmas-bear.jpg',
                12 => 'teddy-01.jpg'
            ];
            
            foreach ($cart_items as $item) {
                $product_id = $item['product_id'] ?? $item['id'];
                $image_name = !empty($item['main_image']) ? $item['main_image'] : 'teddy-01.jpg';
                $price = $item['sale_price'] ?: $item['price'];
                $quantity = $item['quantity'];
                $total_item = $price * $quantity;
                
                // Kiểm tra lại stock trước khi tạo order item
                $current_stock = $db->fetch("SELECT stock_quantity FROM products WHERE id = ?", [$product_id]);
                if (!$current_stock || $current_stock['stock_quantity'] < $quantity) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng trong kho.");
                }
                
                // Thêm order item
                $item_inserted = $db->insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$order_id, $product_id, $item['name'], $image_name, $quantity, $price, $total_item]
                );
                
                if (!$item_inserted) {
                    throw new Exception("Không thể thêm sản phẩm '{$item['name']}' vào đơn hàng.");
                }
                
                // Cập nhật stock quantity
                $stock_updated = $db->update(
                    "UPDATE products SET stock_quantity = stock_quantity - ?, updated_at = NOW() WHERE id = ?",
                    [$quantity, $product_id]
                );
                
                if ($stock_updated === false) {
                    throw new Exception("Không thể cập nhật số lượng sản phẩm '{$item['name']}'.");
                }
                
                $total_items += $quantity;
                $order_weight += ($item['weight'] ?? 0) * $quantity;
            }
            
            // Cập nhật usage_count của coupon nếu có
            if ($applied_coupon && isset($applied_coupon['id'])) {
                try {
                    $db->execute(
                        "UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?",
                        [$applied_coupon['id']]
                    );
                    error_log("Coupon usage updated: {$applied_coupon['code']}");
                } catch (Exception $e) {
                    error_log("Failed to update coupon usage: " . $e->getMessage());
                }
            }
            
            // Commit transaction
            $db->commit();
            
            // Xóa giỏ hàng
            clearCart($user_id);
            
            // ===== GỬI EMAIL THEO PHƯƠNG THỨC THANH TOÁN =====
            try {
                // Lấy thông tin đơn hàng vừa tạo
                $order = $db->fetch("SELECT * FROM orders WHERE order_number = ?", [$order_number]);
                
                // Lấy chi tiết các sản phẩm trong đơn hàng
                $order_items_result = $db->fetchAll("
                    SELECT oi.product_id, p.name as product_name, oi.price, oi.quantity 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ", [$order['id']]);
                
                // Kiểm tra phương thức thanh toán
                if ($payment_method === 'cod') {
                    // COD: Gửi email yêu cầu thanh toán khi giao
                    sendCODConfirmationEmail(
                        $order,
                        $customer_email,
                        $customer_name,
                        $shipping_address,
                        $total_amount,
                        $order_items_result
                    );
                } else {
                    // QR/Momo/ZaloPay: Gửi email thanh toán thành công
                    sendPaidConfirmationEmail(
                        $order,
                        $customer_email,
                        $customer_name,
                        $shipping_address,
                        $total_amount,
                        $order_items_result,
                        $payment_method
                    );
                }
                
            } catch (Exception $e) {
                error_log("Email sending exception: " . $e->getMessage());
            }
            
            // Log thành công
            $discount_info = $discount_amount > 0 ? " (Discount: -" . number_format($discount_amount) . "đ)" : "";
            error_log("Order created successfully: Order #$order_number, Subtotal: " . number_format($subtotal) . "đ, Discount: -" . number_format($discount_amount) . "đ, Total: " . number_format($total_amount) . "đ, Customer: $customer_email");
            
            // Set success message
            setFlashMessage('success', "Đơn hàng #$order_number đã được tạo thành công!");
            
            // Sau khi đặt hàng thành công (sau commit, trước redirect):
            unset($_SESSION['applied_coupon']);
            
            // Lưu order_number vào session để trang thanh toán có thể truy cập
            $_SESSION['pending_order_number'] = $order_number;
            
            // Redirect dựa trên phương thức thanh toán
            if ($payment_method === 'cod') {
                // COD: Redirect trực tiếp đến trang thành công
                header("Location: order-success.php?order_number=" . $order_number);
            } else {
                // Các phương thức khác: Redirect đến trang thanh toán giả lập
                header("Location: mock-payment.php?order_number=" . $order_number);
            }
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            
            $error_message = $e->getMessage();
            error_log("Order creation failed: " . $error_message);
            
            // Thông báo lỗi thân thiện
            if (strpos($error_message, 'stock_quantity') !== false) {
                $errors[] = "Một số sản phẩm trong giỏ hàng không đủ số lượng. Vui lòng kiểm tra lại.";
            } elseif (strpos($error_message, 'Duplicate entry') !== false) {
                $errors[] = "Mã đơn hàng bị trùng. Vui lòng thử lại.";
            } else {
                $errors[] = "Có lỗi xảy ra khi tạo đơn hàng: " . $error_message;
            }
            // Hiển thị lỗi debug nếu bật APP_DEBUG
            if (defined('APP_DEBUG') && APP_DEBUG) {
                echo '<div class="alert alert-danger">DEBUG: ' . htmlspecialchars($error_message) . '</div>';
            }
        }
    }
}

include "../includes/header.php";
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- Form thanh toán -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h4 class="fw-bold text-dark mb-0">
                        <i class="fas fa-file-invoice text-pink me-2"></i> Thông tin thanh toán
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger rounded-3 mb-4">
                            <h6 class="fw-bold"><i class="fas fa-exclamation-circle me-1"></i> Vui lòng sửa các thông tin sau:</h6>
                            <ul class="mb-0 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="checkoutForm" novalidate>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Họ và tên *</label>
                                <input type="text" class="form-control rounded-3" name="customer_name" 
                                       value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ($_SESSION['user_name'] ?? '')); ?>" 
                                       required minlength="2" maxlength="100" placeholder="Nguyễn Văn A">
                                <div class="invalid-feedback">Vui lòng nhập họ tên (ít nhất 2 ký tự)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Địa chỉ Email *</label>
                                <input type="email" class="form-control rounded-3" name="customer_email" 
                                       value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ($_SESSION['user_email'] ?? '')); ?>" 
                                       required maxlength="100" placeholder="email@example.com">
                                <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Số điện thoại liên hệ *</label>
                            <input type="tel" class="form-control rounded-3" name="customer_phone" 
                                   value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>" 
                                   required pattern="[0-9]{10,11}" maxlength="20" placeholder="0912345678">
                            <div class="invalid-feedback">Vui lòng nhập số điện thoại (10-11 số)</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Địa chỉ giao hàng chi tiết *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control rounded-3" id="shippingAddressInput" name="shipping_address" 
                                       value="<?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?>"
                                       placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành..." 
                                       required minlength="10" maxlength="500" autocomplete="off">
                                <div class="invalid-feedback">Vui lòng nhập địa chỉ giao hàng chi tiết</div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-map-marker-alt text-pink me-1"></i> Nhập để tự động tìm kiếm địa chỉ chính xác
                                </small>
                                <input type="hidden" id="addressLat" name="address_lat" value="">
                                <input type="hidden" id="addressLng" name="address_lng" value="">
                                <input type="hidden" id="addressFormat" name="address_format" value="">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small mb-2">Phương thức thanh toán</label>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="card p-3 border rounded-3 h-100 cursor-pointer payment-option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" value="cod" 
                                                   id="cod" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'cod') ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="cod">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="fas fa-hand-holding-usd text-success fs-4 me-2"></i>
                                                    <strong class="text-dark">Thanh toán khi nhận (COD)</strong>
                                                </div>
                                                <small class="text-muted d-block">Kiểm tra và trả tiền mặt cho shipper</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card p-3 border rounded-3 h-100 cursor-pointer payment-option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" 
                                                   id="bank" <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="bank">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="fas fa-university text-primary fs-4 me-2"></i>
                                                    <strong class="text-dark">Chuyển khoản VietQR</strong>
                                                </div>
                                                <small class="text-muted d-block">Quét mã QR tự động xác nhận 24/7</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card p-3 border rounded-3 h-100 cursor-pointer payment-option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" value="momo" 
                                                   id="momo" <?php echo ($_POST['payment_method'] ?? '') === 'momo' ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="momo">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="fas fa-wallet text-danger fs-4 me-2"></i>
                                                    <strong class="text-dark">Ví MoMo</strong>
                                                </div>
                                                <small class="text-muted d-block">Thanh toán tức thì qua ví MoMo</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card p-3 border rounded-3 h-100 cursor-pointer payment-option-card">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" value="zalopay" 
                                                   id="zalopay" <?php echo ($_POST['payment_method'] ?? '') === 'zalopay' ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="zalopay">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="fas fa-mobile-alt text-info fs-4 me-2"></i>
                                                    <strong class="text-dark">Ví ZaloPay</strong>
                                                </div>
                                                <small class="text-muted d-block">Thanh toán bảo mật qua ZaloPay</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Ghi chú cho đơn hàng <small class="text-muted">(tùy chọn)</small></label>
                            <textarea class="form-control rounded-3" name="notes" rows="2" maxlength="1000"
                                      placeholder="Ví dụ: Giao vào giờ hành chính, đóng gói quà sinh nhật..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree_terms" required checked>
                                <label class="form-check-label small text-muted" for="agree_terms">
                                    Tôi đồng ý với chính sách bán hàng và bảo mật của Teddy Bear Paradise *
                                </label>
                                <div class="invalid-feedback">Vui lòng tích đồng ý điều khoản</div>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" name="place_order" class="btn btn-primary btn-lg rounded-pill fw-semibold py-3" id="placeOrderBtn">
                                <i class="fas fa-check-circle me-2"></i>
                                <span class="btn-text">Xác nhận đặt hàng</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...
                                </span>
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                Giao dịch được bảo mật và mã hóa an toàn 100%
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tóm tắt đơn hàng -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-box text-pink me-2"></i> Đơn hàng của bạn
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Danh sách sản phẩm -->
                    <div class="order-items mb-3">
                        <?php foreach ($cart_items as $item): ?>
                            <?php 
                            $product_id = $item['product_id'] ?? $item['id'];
                            $image_name = !empty($item['main_image']) ? $item['main_image'] : 'teddy-01.jpg';
                            $price = $item['sale_price'] ?: $item['price'];
                            $total_item = $price * $item['quantity'];
                            ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <img src="../images/<?php echo htmlspecialchars($image_name); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         style="width: 55px; height: 55px; object-fit: cover;" 
                                         class="rounded-3 border me-3 flex-shrink-0"
                                         onerror="this.src='../images/placeholder.jpg'">
                                    <div class="flex-grow-1 pe-2">
                                        <h6 class="mb-1 fw-bold text-dark small"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <div class="text-muted small">
                                            <?php if (!empty($item['size'])): ?>
                                                <span class="badge bg-light text-secondary border me-1">Size: <?php echo $item['size']; ?></span>
                                            <?php endif; ?>
                                            <span><?php echo formatPrice($price); ?> × <?php echo $item['quantity']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end fw-bold text-dark small">
                                    <?php echo formatPrice($total_item); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Nhập mã giảm giá -->
                    <div class="coupon-section mb-4 pb-3 border-bottom">
                        <label class="form-label fw-semibold text-secondary small mb-1">Mã giảm giá / Voucher</label>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill ps-3" id="couponCode" placeholder="Nhập mã ưu đãi...">
                            <button class="btn btn-outline-primary rounded-end-pill px-3" type="button" id="applyCoupon">
                                Áp dụng
                            </button>
                        </div>
                        <div id="couponSuggestions" class="mt-2"></div>
                        <div id="couponResult" class="mt-2"></div>
                    </div>
                    <script>
// Format price function (same as PHP formatPrice)
function formatPrice(price) {
    if (typeof price !== 'number') {
        price = parseFloat(price) || 0;
    }
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}

document.addEventListener('DOMContentLoaded', function() {
    const applyCouponBtn = document.getElementById('applyCoupon');
    const couponInput = document.getElementById('couponCode');
    const couponResult = document.getElementById('couponResult');
    if (applyCouponBtn && couponInput && couponResult) {
        applyCouponBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            console.log('🔍 [DEBUG] Coupon code entered:', code);
            if (!code) {
                couponResult.innerHTML = '<small class="text-danger">Vui lòng nhập mã giảm giá</small>';
                return;
            }
            applyCouponBtn.innerHTML = '<span style="font-size: 1rem; margin-right: 5px;">⏳</span>';
            applyCouponBtn.disabled = true;
            fetch('./apply-coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'coupon_code=' + encodeURIComponent(code)
            })
            .then(response => {
                console.log('📡 [DEBUG] Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('📝 [DEBUG] Raw response:', text);
                let data;
                try {
                    data = JSON.parse(text);
                    console.log('✅ [DEBUG] Parsed data:', data);
                } catch (e) {
                    console.error('❌ [DEBUG] JSON parse error:', e);
                    couponResult.innerHTML = '<small class="text-danger">Lỗi phản hồi server: ' + text.substring(0, 200) + '</small>';
                    return;
                }
                if (data.success) {
                    couponResult.innerHTML = '<small class="text-success"><span style="font-size: 1rem; margin-right: 5px;">✅</span> ' + data.message + '</small>';
                    
                    // Update all price values
                    const subtotalEl = document.getElementById('subtotal');
                    const shippingEl = document.getElementById('shipping-fee');
                    const discountEl = document.getElementById('discount-amount');
                    const totalEl = document.getElementById('total-amount');
                    const couponDiscountDiv = document.getElementById('coupon-discount');
                    
                    // Show discount row
                    if (couponDiscountDiv) {
                        couponDiscountDiv.classList.remove('d-none');
                    }
                    
                    // Update subtotal (should not change, but update for consistency)
                    if (subtotalEl) {
                        subtotalEl.textContent = formatPrice(data.cart.subtotal || 0);
                    }
                    
                    // Update shipping fee (should not change, but update for consistency)
                    if (shippingEl) {
                        const shippingFee = data.cart.shipping_fee || 0;
                        if (shippingFee > 0) {
                            shippingEl.innerHTML = formatPrice(shippingFee);
                        } else {
                            shippingEl.innerHTML = '<span class="text-success">Miễn phí</span>';
                        }
                    }
                    
                    // Update discount amount
                    if (discountEl) {
                        discountEl.textContent = '-' + formatPrice(data.cart.discount || 0);
                    }
                    
                    // Update total amount
                    if (totalEl) {
                        totalEl.textContent = formatPrice(data.cart.final_total || 0);
                    }
                    
                    console.log('✅ Updated prices:', {
                        subtotal: data.cart.subtotal,
                        shipping: data.cart.shipping_fee,
                        discount: data.cart.discount,
                        total: data.cart.final_total
                    });
                } else {
                    couponResult.innerHTML = '<small class="text-danger">❌ ' + (data.message || 'Có lỗi xảy ra') + '</small>';
                    
                    // Hide discount row and reset to original prices
                    const couponDiscountDiv = document.getElementById('coupon-discount');
                    if (couponDiscountDiv) {
                        couponDiscountDiv.classList.add('d-none');
                    }
                    
                    // Reset discount amount
                    const discountEl = document.getElementById('discount-amount');
                    if (discountEl) {
                        discountEl.textContent = '0đ';
                    }
                    
                    // Reset total to original (subtotal + shipping_fee)
                    const subtotalEl = document.getElementById('subtotal');
                    const shippingEl = document.getElementById('shipping-fee');
                    const totalEl = document.getElementById('total-amount');
                    
                    if (subtotalEl && shippingEl && totalEl) {
                        const subtotal = parseFloat(subtotalEl.textContent.replace(/[^\d]/g, '')) || 0;
                        const shippingText = shippingEl.textContent;
                        const shipping = shippingText.includes('Miễn phí') ? 0 : (parseFloat(shippingText.replace(/[^\d]/g, '')) || 0);
                        const originalTotal = subtotal + shipping;
                        totalEl.textContent = formatPrice(originalTotal);
                    }
                    
                    if (data.debug) {
                        console.warn('⚠️ [DEBUG] Backend debug info:', data.debug);
                    }
                }
                applyCouponBtn.innerHTML = '<span style="font-size: 1rem; margin-right: 5px;">🏷️</span> Áp dụng';
                applyCouponBtn.disabled = false;
            })
            .catch(error => {
                console.error('❌ [DEBUG] Network or JS error:', error);
                couponResult.innerHTML = '<small class="text-danger">❌ Lỗi: ' + error.message + '</small>';
                applyCouponBtn.innerHTML = '<span style="font-size: 1rem; margin-right: 5px;">🏷️</span> Áp dụng';
                applyCouponBtn.disabled = false;
            });
        });
    }
});
</script>
<style>
.coupon-suggestion {
    display: inline-block; background: #fff3cd; color: #d35400; border-radius: 8px;
    padding: 4px 10px; margin: 2px 4px; cursor: pointer; font-weight: 600; transition: background 0.2s;
    border: 1px dashed #ffd580; font-size: 13px;
}
.coupon-suggestion:hover { background: #ffe9a7; color: #b34700; }
</style>
                    
                    <!-- Tổng cộng -->
                    <div class="order-summary mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="subtotal"><?php echo formatPrice($cart_summary['subtotal']); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span id="shipping-fee">
                                <?php echo $cart_summary['shipping_fee'] > 0 ? formatPrice($cart_summary['shipping_fee']) : '<span class="text-success">Miễn phí</span>'; ?>
                            </span>
                        </div>
                        
                        <div id="coupon-discount" class="d-flex justify-content-between mb-2 d-none">
                            <span class="text-success">Giảm giá:</span>
                            <span class="text-success" id="discount-amount">0đ</span>
                        </div>
                        
                        <?php if (!$cart_summary['is_free_shipping'] && $cart_summary['free_shipping_remaining'] > 0): ?>
                            <div class="p-3 rounded-3 bg-light mb-3">
                                <div class="small text-secondary mb-1">
                                    <i class="fas fa-truck text-pink me-1"></i> Mua thêm <strong><?php echo formatPrice($cart_summary['free_shipping_remaining']); ?></strong> để được <strong>FREESHIP!</strong>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-pink" style="width: <?php echo ($cart_summary['subtotal'] / FREE_SHIPPING_MIN) * 100; ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="fs-5 text-dark">Tổng cộng:</strong>
                            <strong class="fs-4 text-danger" id="total-amount"><?php echo formatPrice($cart_summary['total']); ?></strong>
                        </div>
                        
                        <!-- Thời gian giao hàng dự kiến -->
                        <div class="p-3 rounded-3 bg-light mb-3">
                            <h6 class="fw-bold text-dark small mb-2"><i class="fas fa-shipping-fast text-pink me-1"></i> Thời gian giao hàng dự kiến:</h6>
                            <ul class="mb-0 small text-secondary ps-3">
                                <li>Nội thành TP.HCM & Hà Nội: 1 - 2 ngày</li>
                                <li>Các tỉnh thành khác: 2 - 4 ngày</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Điều hướng phụ -->
            <div class="d-flex flex-column gap-2">
                <a href="../cart/index.php" class="btn btn-outline-secondary rounded-pill w-100 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại giỏ hàng
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Gợi ý mã giảm giá động
require_once __DIR__ . '/../config/connect.php';
$today = date('Y-m-d');
$active_coupons = $db->fetchAll("SELECT code, name, value, type FROM coupons WHERE status = 'active' AND start_date <= ? AND end_date >= ? ORDER BY id DESC LIMIT 5", [$today, $today]);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hiển thị gợi ý mã giảm giá
    var couponSuggestions = document.getElementById('couponSuggestions');
    if (couponSuggestions) {
        couponSuggestions.innerHTML = `<?php foreach ($active_coupons as $c): ?>
            <span class="coupon-suggestion" data-code="<?php echo htmlspecialchars($c['code']); ?>">
                <i class="fas fa-tag me-1 text-warning"></i> <b><?php echo htmlspecialchars($c['code']); ?></b>
                <?php if ($c['type'] === 'percentage'): ?>- <?php echo (float)$c['value']; ?>%<?php elseif ($c['type'] === 'fixed'): ?>- <?php echo number_format($c['value']); ?>đ<?php endif; ?>
            </span>
        <?php endforeach; ?>`;
    }
    // Sự kiện click gợi ý mã giảm giá
    document.querySelectorAll('.coupon-suggestion').forEach(function(el) {
        el.addEventListener('click', function() {
            var code = this.dataset.code;
            document.getElementById('couponCode').value = code;
            var applyBtn = document.getElementById('applyCoupon');
            if (applyBtn) applyBtn.click();
        });
    });
});
</script>
<style>
.coupon-suggestion {
    display: inline-block; background: #fff3cd; color: #d35400; border-radius: 8px;
    padding: 4px 10px; margin: 2px 4px; cursor: pointer; font-weight: 600; transition: background 0.2s;
    border: 1px dashed #ffd580; font-size: 13px;
}
.coupon-suggestion:hover { background: #ffe9a7; color: #b34700; }

/* Address Auto-Complete Styles */
.address-suggestions-container {
    border: 1px solid #e0e0e0;
    border-top: none;
    background: white;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
}

.address-suggestion-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 14px;
}

.address-suggestion-item:hover {
    background: #f5f5f5;
}

.address-suggestion-item:last-child {
    border-bottom: none;
}

#shippingAddressInput:focus {
    border-color: #1890ff !important;
    background: #f8fbff !important;
}
</style>
<!-- Address Auto-Complete Script -->
<script src="../js/address-autocomplete.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo Address Auto-Complete
        const addressAutocomplete = new AddressAutocomplete(
            '#shippingAddressInput',
            'body'
        );
        
        // Xử lý khi chọn địa chỉ
        document.getElementById('shippingAddressInput').addEventListener('addressSelected', function(e) {
            const detail = e.detail;
            
            // Lưu tọa độ vào hidden inputs
            document.getElementById('addressLat').value = detail.lat || '';
            document.getElementById('addressLng').value = detail.lng || '';
            
            if (detail.normalized) {
                document.getElementById('addressFormat').value = detail.normalized.is_new_format ? '34_new' : '64_old';
            }
            
            console.log('✅ Địa chỉ đã chọn:', detail);
        });
    });
</script>
<?php include "../includes/footer.php"; ?>