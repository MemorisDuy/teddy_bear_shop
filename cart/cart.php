<?php
/**
 * Shopping Cart Page - FIXED VERSION 🛒
 */

require_once '../config/config.php';

// Get cart items using helper function
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);

// Calculate shipping
$shipping_fee = $cart_total >= FREE_SHIPPING_MIN ? 0 : SHIPPING_FEE;
$final_total = $cart_total + $shipping_fee;

// SEO Meta
$page_title = 'Giỏ hàng';
$page_description = 'Xem lại giỏ hàng và tiến hành thanh toán tại Teddy Bear Paradise.';

// Map product_id với ảnh tương ứng
// Version an toàn - chỉ dùng file có 100% chắc chắn
$product_images = [
    1 => 'teddy-01.jpg',
    2 => 'teddy-02.jpg', 
    3 => 'category-panda.jpg',    // Dùng category-panda thay panda-01
    4 => 'polar-01.jpg',
    5 => 'koala-01.jpg',
    6 => 'teddy-blue.jpg',
    7 => 'teddy-01.jpg',          // Dùng teddy-01 thay giant-bear
    8 => 'bunny-bear.jpg',
    9 => 'unicorn-bear.jpg',
    10 => 'couple-bears.jpg',
    11 => 'christmas-bear.jpg',
    12 => 'teddy-01.jpg'          // Dùng teddy-01 làm default
];/*  */

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="page-title text-center mb-4" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif; font-weight: 700;">
                <span style="font-size: 1.5rem; margin-right: 8px;">🛒</span> Giỏ hàng của bạn
            </h2>
        </div>
    </div>
    
    <?php if (!empty($cart_items)): ?>
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <?php 
                $product_id = $item['product_id'] ?? $item['id'];
                $image_name = $product_images[$product_id] ?? 'teddy-01.jpg'; // Default
                ?>
                <div class="cart-item mb-3 p-3 border rounded" data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="../images/<?php echo $image_name; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="img-fluid rounded cart-item-image" 
                                 style="max-height: 80px;">
                        </div>
                        <div class="col-md-4">
                            <div class="cart-item-info">
                                <h6 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="cart-item-price mb-1" data-price="<?php echo $item['sale_price'] ?: $item['price']; ?>">
                                    <?php if ($item['sale_price']): ?>
                                        <span class="text-danger fw-bold"><?php echo formatPrice($item['sale_price']); ?></span>
                                        <small class="text-muted text-decoration-line-through ms-1"><?php echo formatPrice($item['price']); ?></small>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo formatPrice($item['price']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($item['size']): ?>
                                <small class="text-muted">Kích thước: <?php echo $item['size']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="quantity-controls d-flex align-items-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-minus" data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                    <span style="font-size: 1rem;">➖</span>
                                </button>
                                <input type="number" class="form-control mx-2 text-center quantity-input" 
                                       style="width: 70px;"
                                       value="<?php echo $item['quantity']; ?>" 
                                       data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>"
                                       min="1" max="<?php echo $item['stock_quantity']; ?>"
                                       data-original-value="<?php echo $item['quantity']; ?>">
                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-plus" data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>">
                                    <span style="font-size: 1rem;">➕</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="cart-item-total text-center">
                                <strong class="item-total"><?php echo formatPrice(($item['sale_price'] ?: $item['price']) * $item['quantity']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-outline-danger btn-sm remove-item" 
                                    onclick="removeCartItem(<?php echo $item['product_id'] ?? $item['id']; ?>)"
                                    data-product-id="<?php echo $item['product_id'] ?? $item['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($item['name']); ?>">
                                <span style="font-size: 1rem;">🗑️</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Actions -->
            <div class="cart-actions d-flex justify-content-between mt-4">
                <a href="../pages/products.php" class="btn btn-outline-primary">
                    <span style="font-size: 1rem; margin-right: 5px;">⬅️</span> Tiếp tục mua sắm
                </a>
                <button class="btn btn-outline-secondary" onclick="clearCart()">
                    <span style="font-size: 1rem; margin-right: 5px;">🗑️</span> Xóa tất cả
                </button>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div class="col-lg-4">
            <div class="cart-summary p-4 border rounded bg-light">
                <h5 class="fw-bold mb-3" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">📋 Tóm tắt đơn hàng</h5>
                
                <!-- Free Shipping Progress -->
                <div class="free-shipping-progress mb-4">
                    <div class="free-shipping-message text-center mb-2">
                        <?php if ($cart_total >= FREE_SHIPPING_MIN): ?>
                            <span style="font-size: 1.2rem; color: #28a745; margin-right: 5px;">✅</span>
                            <strong class="text-success">Chúc mừng! Bạn được MIỄN PHÍ SHIP! 🎉</strong>
                        <?php else: ?>
                            <span style="font-size: 1.2rem; margin-right: 5px;">🚚</span>
                            Mua thêm <strong><?php echo formatPrice(FREE_SHIPPING_MIN - $cart_total); ?></strong> để được <strong>MIỄN PHÍ SHIP</strong>!
                        <?php endif; ?>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?php echo min(100, ($cart_total / FREE_SHIPPING_MIN) * 100); ?>%"></div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span id="subtotal"><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping"><?php echo formatPrice($shipping_fee); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span><strong>Tổng cộng:</strong></span>
                        <span><strong id="total" class="text-primary"><?php echo formatPrice($final_total); ?></strong></span>
                    </div>
                </div>
                
                <!-- Checkout Button -->
                <a href="checkout.php" class="btn btn-primary btn-lg w-100 mb-3">
                    <span style="font-size: 1.1rem; margin-right: 5px;">💳</span> Thanh toán ngay
                </a>
                
                <!-- Security Info -->
                <div class="security-info text-center">
                    <small class="text-muted">
                        <span style="font-size: 1rem; color: #28a745; margin-right: 5px;">🛡️</span>
                        Thanh toán an toàn & bảo mật
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Empty Cart -->
    <div class="empty-cart text-center py-5">
        <div class="empty-cart-icon mb-4">
            <span style="font-size: 5rem; color: #ddd; display: block;">🛒</span>
        </div>
        <h3 style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif; font-weight: 700;">Giỏ hàng trống 🛒</h3>
        <p class="text-muted mb-4" style="font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif;">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
        <a href="../pages/products.php" class="btn btn-primary btn-lg">
            <span style="font-size: 1.1rem; margin-right: 5px;">🛍️</span> Bắt đầu mua sắm
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- JavaScript -->
<script src="../assets/js/jquery.min.js"></script>
<script src="../js/cart.js"></script>
<!-- <script src="../assets/js/bootstrap.bundle.min.js"></script> -->

<?php include '../includes/footer.php'; ?>