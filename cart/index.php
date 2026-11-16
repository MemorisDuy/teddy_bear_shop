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
                <ol class="breadcrumb" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">
                    <li class="breadcrumb-item"><a href="../index.php" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Trang chủ</a></li>
                    <li class="breadcrumb-item active" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Giỏ hàng</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;"><span style="font-size: 1.3rem; margin-right: 8px;">🛒</span> Giỏ hàng của bạn</h2>
                <?php if (!empty($cart_items)): ?>
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <span style="font-size: 1rem; margin-right: 5px;">🗑️</span> Xóa tất cả
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="empty-cart text-center py-5">
                    <span style="font-size: 4rem; color: #6c757d; display: block; margin-bottom: 1rem;">🛒</span>
                    <h4 style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Giỏ hàng trống</h4>
                    <p class="text-muted mb-4" style="font-family: 'Nunito', 'Poppins', Arial, sans-serif;">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="../pages/products.php" class="btn btn-primary btn-lg">
                        <span style="font-size: 1.1rem; margin-right: 5px;">🛍️</span> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Cart with Items -->
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Sản phẩm trong giỏ hàng 
                            <span class="badge bg-primary"><?php echo $cart_summary['total_quantity']; ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50%">Sản phẩm</th>
                                        <th width="15%" class="text-center">Đơn giá</th>
                                        <th width="15%" class="text-center">Số lượng</th>
                                        <th width="15%" class="text-end">Thành tiền</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody class="cart-items">
                                    <?php foreach ($cart_items as $item): ?>
                                        <?php 
                                        // FIX: Ensure all required fields exist
                                        $product_id = $item['product_id'] ?? $item['id'] ?? 0;
                                        $price = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
                                        $total_item = $price * $item['quantity'];
                                        $image_path = !empty($item['main_image']) ? $item['main_image'] : 'placeholder.jpg';
                                        ?>
                                        <tr class="cart-item" data-product-id="<?php echo $product_id; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="product-image me-3">
                                                        <img src="<?php echo IMG_PATH . $image_path; ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                             style="width: 80px; height: 80px; object-fit: cover;" 
                                                             class="rounded border cart-item-image"
                                                             onerror="this.src='<?php echo IMG_PATH; ?>placeholder.jpg'">
                                                    </div>
                                                    <div class="product-info">
                                                        <h6 class="mb-1">
                                                            <?php if (!empty($item['slug'])): ?>
                                                            <a href="../pages/product-detail.php?slug=<?php echo $item['slug']; ?>" 
                                                               class="text-decoration-none">
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            </a>
                                                            <?php else: ?>
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            <?php endif; ?>
                                                        </h6>
                                                        
                                                        <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                                                        <div class="product-details">
                                                            <?php if (!empty($item['size'])): ?>
                                                                <small class="text-muted">Size: <?php echo $item['size']; ?></small><br>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['color'])): ?>
                                                                <small class="text-muted">Màu: <?php echo $item['color']; ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="stock-info">
                                                            <small class="text-success">
                                                                <span style="font-size: 0.9rem; color: #28a745; margin-right: 5px;">✅</span> Còn hàng
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="price-info">
                                                    <?php if (!empty($item['sale_price'])): ?>
                                                        <span class="text-danger fw-bold"><?php echo formatPrice($item['sale_price']); ?></span><br>
                                                        <small class="text-muted text-decoration-line-through"><?php echo formatPrice($item['price']); ?></small>
                                                    <?php else: ?>
                                                        <span class="fw-bold"><?php echo formatPrice($item['price']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="quantity-controls">
                                                    <div class="input-group input-group-sm" style="width: 140px; margin: 0 auto;">
                                                        <button class="btn btn-outline-secondary" type="button" 
                                                                onclick="changeQuantity(<?php echo $product_id; ?>, -1)">
                                                            <span style="font-size: 1rem;">➖</span>
                                                        </button>
                                                        <input type="number" 
                                                               class="form-control text-center" 
                                                               value="<?php echo $item['quantity']; ?>" 
                                                               min="1" 
                                                               max="99"
                                                               id="qty_<?php echo $product_id; ?>"
                                                               onchange="updateQuantityDirect(<?php echo $product_id; ?>, this.value)"
                                                               style="max-width: 60px;">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                                onclick="changeQuantity(<?php echo $product_id; ?>, 1)">
                                                            <span style="font-size: 1rem;">➕</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end align-middle">
                                                <span class="fw-bold text-primary item-total" data-item-total="<?php echo $product_id; ?>">
                                                    <?php echo formatPrice($total_item); ?>
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="removeCartItem(<?php echo $product_id; ?>)"
                                                        title="Xóa sản phẩm"
                                                        style="min-width: 40px;">
                                                    <span style="font-size: 1rem;">🗑️</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="mt-3">
                    <a href="../pages/products.php" class="btn btn-outline-primary">
                        <span style="font-size: 1rem; margin-right: 5px;">⬅️</span> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="order-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span class="cart-subtotal"><?php echo formatPrice($cart_summary['subtotal']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <span class="shipping-fee">
                                    <?php echo $cart_summary['shipping_fee'] > 0 ? formatPrice($cart_summary['shipping_fee']) : '<span class="text-success">Miễn phí</span>'; ?>
                                </span>
                            </div>
                            
                            <?php if (!$cart_summary['is_free_shipping'] && $cart_summary['free_shipping_remaining'] > 0): ?>
                            <div class="free-shipping-progress mb-3">
                                <small class="text-info">
                                    <span style="font-size: 1rem; margin-right: 5px;">🚚</span> 
                                    Mua thêm <?php echo formatPrice($cart_summary['free_shipping_remaining']); ?> để được <strong>MIỄN PHÍ SHIP!</strong>
                                </small>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-info" 
                                         style="width: <?php echo ($cart_summary['subtotal'] / FREE_SHIPPING_MIN) * 100; ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="h5">Tổng cộng:</strong>
                                <strong class="h5 text-danger cart-total"><?php echo formatPrice($cart_summary['total']); ?></strong>
                            </div>
                            
                            <div class="d-grid">
                                <a href="checkout.php" class="btn btn-danger btn-lg">
                                    <span style="font-size: 1.1rem; margin-right: 5px;">💳</span> Thanh toán
                                </a>
                            </div>
                            
                            <div class="payment-methods mt-3">
                                <small class="text-muted">Chúng tôi chấp nhận:</small>
                                <div class="payment-icons mt-1">
                                    <span style="font-size: 1.3rem; margin-right: 8px;">💳</span>
                                    <span style="font-size: 1.3rem; margin-right: 8px;">💳</span>
                                    <span style="font-size: 1.3rem; margin-right: 8px;">💰</span>
                                    <span style="font-size: 1.3rem;">📱</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Info -->
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <div class="security-badges">
                            <div class="mb-2">
                                <span style="font-size: 2rem; color: #28a745; display: block;">🛡️</span>
                            </div>
                            <h6>Mua sắm an toàn</h6>
                            <small class="text-muted">
                                • Bảo mật thông tin 100%<br>
                                • Đổi trả trong 7 ngày<br>
                                • Hỗ trợ 24/7
                            </small>
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