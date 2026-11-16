<?php
// Suppress warnings to prevent JavaScript breaks
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

/**
 * Cart Page - Trang giỏ hàng
 * File: cart/index.php
 */

// DEBUG - Add at top of cart/index.php
if (defined('APP_DEBUG') && APP_DEBUG) {
    echo "<!-- DEBUG SESSION CART: " . print_r($_SESSION['cart'] ?? [], true) . " -->";
    $user_id = $_SESSION['user_id'] ?? null;
    echo "<!-- DEBUG USER ID: " . ($user_id ?? 'NULL') . " -->";
    if ($user_id) {
        $db_cart = $db->fetchAll("SELECT * FROM cart WHERE user_id = ?", [$user_id]);
        echo "<!-- DEBUG DB CART: " . print_r($db_cart, true) . " -->";
    }
    $cart_items = getCartItems($user_id);
    echo "<!-- DEBUG CART ITEMS: " . print_r($cart_items, true) . " -->";
    $cart_summary = getCartSummary($user_id);
    echo "<!-- DEBUG CART SUMMARY: " . print_r($cart_summary, true) . " -->";
}

if (defined('APP_DEBUG') && APP_DEBUG) {
    echo "<script>console.log('SESSION CART:', " . json_encode($_SESSION['cart'] ?? []) . ");</script>";
}

session_start();
require_once '../config/config.php';
require_once '../config/cart_functions.php';

$page_title = "Giỏ hàng - Teddy Bear Shop";

// Get user ID if logged in
$user_id = $_SESSION['user_id'] ?? null;

// Get cart items and summary
$cart_items = getCartItems($user_id);
$cart_summary = getCartSummary($user_id);

// FIX: Ensure cart_summary has all required keys
$cart_summary = array_merge([
    'count' => 0,
    'total_quantity' => 0, // Alias for count
    'subtotal' => 0,
    'shipping_fee' => 30000,
    'total' => 0,
    'is_free_shipping' => false,
    'free_shipping_remaining' => 500000
], $cart_summary);

// Ensure total_quantity = count for backward compatibility
if (!isset($cart_summary['total_quantity']) || $cart_summary['total_quantity'] == 0) {
    $cart_summary['total_quantity'] = $cart_summary['count'];
}

// Handle AJAX requests
if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                $result = updateCartItem($product_id, $quantity, $user_id);
                echo json_encode($result);
                exit;
                
            case 'remove_item':
                $product_id = (int)$_POST['product_id'];
                $result = removeCartItem($product_id, $user_id);
                echo json_encode($result);
                exit;
                
            case 'clear_cart':
                $result = clearCart($user_id);
                echo json_encode(['success' => $result]);
                exit;
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
                    <li class="breadcrumb-item active text-pink fw-semibold">Giỏ hàng</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark mb-0">
                    <i class="fas fa-shopping-bag text-pink me-2"></i> Giỏ hàng của bạn
                </h2>
                <?php if (!empty($cart_items)): ?>
                    <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="clearCart()">
                        <i class="fas fa-trash-alt me-1"></i> Xóa tất cả
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (empty($cart_items)): ?>
        <!-- Giỏ hàng trống -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
                    <div class="rounded-circle bg-pink-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 90px; height: 90px;">
                        <i class="fas fa-shopping-cart text-pink fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Giỏ hàng của bạn đang trống</h4>
                    <p class="text-muted mb-4">Hãy khám phá các sản phẩm gấu bông đáng yêu và thêm vào giỏ nhé!</p>
                    <div>
                        <a href="../pages/products.php" class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Danh sách sản phẩm trong giỏ -->
        <div class="row g-4">
            <!-- Cột sản phẩm -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            Sản phẩm đã chọn 
                            <span class="badge bg-pink text-white rounded-pill ms-2"><?php echo $cart_summary['total_quantity']; ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted">
                                        <th style="width: 45%;">Sản phẩm</th>
                                        <th style="width: 18%;" class="text-center">Đơn giá</th>
                                        <th style="width: 18%;" class="text-center">Số lượng</th>
                                        <th style="width: 14%;" class="text-end">Thành tiền</th>
                                        <th style="width: 5%;" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody class="cart-items">
                                    <?php foreach ($cart_items as $item): ?>
                                        <?php 
                                        $product_id = $item['product_id'] ?? $item['id'] ?? 0;
                                        $price = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
                                        $total_item = $price * $item['quantity'];
                                        $image_path = !empty($item['main_image']) ? $item['main_image'] : 'placeholder.jpg';
                                        ?>
                                        <tr class="cart-item" data-product-id="<?php echo $product_id; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo IMG_PATH . $image_path; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                         style="width: 65px; height: 65px; object-fit: cover;" 
                                                         class="rounded-3 border me-3 flex-shrink-0"
                                                         onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-dark">
                                                            <a href="../pages/product-detail.php?slug=<?php echo $item['slug'] ?? ''; ?>" 
                                                               class="text-decoration-none text-dark hover-pink">
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            </a>
                                                        </h6>
                                                        <?php if (!empty($item['size'])): ?>
                                                            <span class="badge bg-light text-secondary border small me-1">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                                        <?php endif; ?>
                                                        <span class="small text-success d-block mt-1">
                                                            <i class="fas fa-check-circle me-1"></i> Có sẵn
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($item['sale_price'])): ?>
                                                    <div class="fw-bold text-danger"><?php echo formatPrice($item['sale_price']); ?></div>
                                                    <small class="text-muted text-decoration-line-through"><?php echo formatPrice($item['price']); ?></small>
                                                <?php else: ?>
                                                    <div class="fw-bold text-dark"><?php echo formatPrice($item['price']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm mx-auto" style="width: 110px;">
                                                    <button class="btn btn-outline-secondary px-2" type="button" 
                                                            onclick="changeQuantity(<?php echo $product_id; ?>, -1)">
                                                        <i class="fas fa-minus small"></i>
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center fw-bold" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="1" 
                                                           max="99"
                                                           id="qty_<?php echo $product_id; ?>"
                                                           onchange="updateQuantityDirect(<?php echo $product_id; ?>, this.value)">
                                                    <button class="btn btn-outline-secondary px-2" type="button"
                                                            onclick="changeQuantity(<?php echo $product_id; ?>, 1)">
                                                        <i class="fas fa-plus small"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-primary item-total" data-item-total="<?php echo $product_id; ?>">
                                                    <?php echo formatPrice($total_item); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-link text-danger p-0" 
                                                        onclick="removeCartItem(<?php echo $product_id; ?>)"
                                                        title="Xóa sản phẩm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Nút quay lại mua sắm -->
                <div class="mb-4">
                    <a href="../pages/products.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Xem thêm sản phẩm khác
                    </a>
                </div>
            </div>
            
            <!-- Cột Tóm tắt thanh toán -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0 text-dark">Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-semibold text-dark cart-subtotal"><?php echo formatPrice($cart_summary['subtotal']); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="shipping-fee fw-semibold">
                                <?php echo $cart_summary['shipping_fee'] > 0 ? formatPrice($cart_summary['shipping_fee']) : '<span class="text-success">Miễn phí</span>'; ?>
                            </span>
                        </div>
                        
                        <?php if (!$cart_summary['is_free_shipping'] && $cart_summary['free_shipping_remaining'] > 0): ?>
                        <div class="p-3 rounded-3 bg-light mb-3">
                            <div class="small text-secondary mb-1">
                                <i class="fas fa-truck text-pink me-1"></i> Mua thêm <strong><?php echo formatPrice($cart_summary['free_shipping_remaining']); ?></strong> để được <strong>FREESHIP!</strong>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-pink" 
                                     style="width: <?php echo min(100, ($cart_summary['subtotal'] / FREE_SHIPPING_MIN) * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold text-dark fs-5">Tổng cộng:</span>
                            <span class="fw-bold text-danger fs-4 cart-total"><?php echo formatPrice($cart_summary['total']); ?></span>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <a href="checkout.php" class="btn btn-primary btn-lg rounded-pill fw-semibold py-2">
                                <i class="fas fa-lock me-2"></i> Tiến hành thanh toán
                            </a>
                        </div>
                        
                        <div class="text-center pt-2">
                            <small class="text-muted d-block mb-2">Phương thức thanh toán hỗ trợ:</small>
                            <div class="d-flex justify-content-center gap-3 text-secondary fs-4">
                                <i class="fab fa-cc-visa text-primary" title="Visa"></i>
                                <i class="fab fa-cc-mastercard text-danger" title="Mastercard"></i>
                                <i class="fas fa-money-bill-wave text-success" title="Tiền mặt (COD)"></i>
                                <i class="fas fa-qrcode text-dark" title="Chuyển khoản QR"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cam kết bảo mật -->
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                    <div class="d-flex align-items-center justify-content-center gap-3 text-start">
                        <i class="fas fa-shield-alt text-success fs-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Thanh toán an toàn 100%</h6>
                            <small class="text-muted">Bảo mật thông tin đơn hàng & Hỗ trợ đổi trả trong 7 ngày</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Format price function (global)
function formatPrice(price) {
    if (typeof price !== 'number') {
        price = parseFloat(price) || 0;
    }
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
}

// Fix cart functions
function removeCartItem(productId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        return;
    }
    fetch('remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            // Xóa item khỏi DOM
            const item = document.querySelector(`[data-product-id="${productId}"]`);
            if (item) item.remove();
            updateCartCount(data.cart_count);
            // Update cart summary
            if (data.cart_subtotal !== undefined || data.cart_total !== undefined) {
                updateCartSummary(data);
            }
            // Nếu giỏ hàng trống, reload để hiển thị giao diện trống
            if (data.cart_count === 0) setTimeout(() => location.reload(), 800);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Có lỗi xảy ra!', 'error');
    });
}

function changeQuantity(productId, change) {
    const input = document.getElementById('qty_' + productId);
    const currentQty = parseInt(input.value);
    const newQty = currentQty + change;
    if (newQty < 1) {
        removeCartItem(productId);
        return;
    }
    input.value = newQty;
    updateQuantityDirect(productId, newQty);
}

function updateQuantityDirect(productId, newQuantity) {
    const qty = parseInt(newQuantity);
    if (qty < 1) {
        removeCartItem(productId);
        return;
    }
    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: qty })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📦 Update cart response:', data);
        if (data.success) {
            showNotification('✅ Đã cập nhật số lượng', 'success');
            updateCartUI(data, productId);
            // Update cart summary display (includes count)
            updateCartSummary(data);
        } else {
            showNotification('❌ ' + (data.message || 'Có lỗi xảy ra'), 'error');
        }
    })
    .catch(error => {
        console.error('❌ Update cart error:', error);
        showNotification('❌ Có lỗi xảy ra: ' + error.message, 'error');
    });
}

function clearCart() {
    if (!confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')) {
        return;
    }
    fetch('clear-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ Đã xóa tất cả sản phẩm!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showNotification('❌ Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Có lỗi xảy ra!', 'error');
    });
}

function updateCartUI(data, productId) {
    // Update item total
    const itemTotal = document.querySelector(`[data-item-total="${productId}"]`);
    if (itemTotal && data.item_total !== undefined) {
        itemTotal.textContent = formatPrice(data.item_total);
    }
    // Update cart summary
    updateCartSummary(data);
}

function updateCartSummary(data) {
    console.log('🔄 Updating cart summary:', data);
    
    if (data.cart_subtotal !== undefined) {
        const subtotal = document.querySelector('.cart-subtotal');
        if (subtotal) {
            subtotal.textContent = formatPrice(data.cart_subtotal);
            console.log('✅ Updated subtotal:', data.cart_subtotal);
        }
    }
    if (data.cart_total !== undefined) {
        const total = document.querySelector('.cart-total');
        if (total) {
            total.textContent = formatPrice(data.cart_total);
            console.log('✅ Updated total:', data.cart_total);
        }
    }
    if (data.shipping_fee !== undefined) {
        const shipping = document.querySelector('.shipping-fee');
        if (shipping) {
            if (data.shipping_fee > 0) {
                shipping.textContent = formatPrice(data.shipping_fee);
            } else {
                shipping.innerHTML = '<span class="text-success">Miễn phí</span>';
            }
            console.log('✅ Updated shipping:', data.shipping_fee);
        }
    }
    
    // Update cart count badge in header
    const cartCount = data.cart_count !== undefined ? data.cart_count : (data.total_quantity !== undefined ? data.total_quantity : null);
    if (cartCount !== null && cartCount !== undefined) {
        console.log('🔄 Updating cart count to:', cartCount);
        updateCartCount(cartCount);
        
        // Update cart quantity badge in page (badge next to "Sản phẩm trong giỏ hàng")
        const cartBadge = document.querySelector('.card-header .badge');
        if (cartBadge) {
            cartBadge.textContent = cartCount;
            console.log('✅ Updated page badge to:', cartCount);
        } else {
            console.warn('⚠️ Cart badge not found in page');
        }
    } else {
        console.warn('⚠️ No cart_count in response:', data);
    }
}

function updateCartCount(count) {
    const cartBadge = document.getElementById('cartCount');
    const numCount = parseInt(count) || 0;
    console.log('🔄 Updating cart count badge to:', numCount);
    
    if (cartBadge) {
        cartBadge.textContent = numCount;
        if (numCount > 0) {
            cartBadge.style.display = 'flex'; // Đảm bảo đồng bộ với CSS header
        } else {
            cartBadge.style.display = 'none';
        }
        console.log('✅ Updated header cart badge');
    } else {
        console.warn('⚠️ Cart badge (#cartCount) not found in header');
    }
}

function showNotification(message, type) {
    // Simple notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = message + '<button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>';
    document.body.appendChild(notification);
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    fetch('get-cart-count.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
        }
    })
    .catch(error => console.log('Cart count load error:', error));
});
</script>

<?php include '../includes/footer.php'; ?>