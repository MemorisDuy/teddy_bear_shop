<?php
/**
 * Cart Functions - NO DUPLICATES
 * File: config/cart_functions.php
 * Chỉ chứa các function cho giỏ hàng, không duplicate với config.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Lấy danh sách sản phẩm trong giỏ hàng
 */
function getCartItems($user_id = null) {
    global $db;
    
    if ($user_id && isset($db)) {
        // Database cart for logged-in users
        try {
            return $db->fetchAll(
                "SELECT c.*, c.size as cart_size, p.* FROM cart c INNER JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 'active' ORDER BY c.created_at DESC",
                [$user_id]
            );
        } catch (Exception $e) {
            error_log("Get cart items DB error: " . $e->getMessage());
            return [];
        }
    } else {
        // Session cart for guests
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        
        if (!empty($cart) && isset($db)) {
            foreach ($cart as $key => $quantity) {
                // Parse key: product_id|size
                $parts = explode('|', $key);
                $product_id = $parts[0];
                $size = $parts[1] ?? '';
                try {
                    $product = $db->fetch(
                        "SELECT *, id as product_id FROM products WHERE id = ? AND status = 'active'",
                        [$product_id]
                    );
                    if ($product) {
                        $product['quantity'] = $quantity;
                        $product['size'] = $size; // Gán size vào item
                        $items[] = $product;
                    }
                } catch (Exception $e) {
                    error_log("Get cart item error: " . $e->getMessage());
                }
            }
        }
        
        return $items;
    }
}

/**
 * Tính tóm tắt giỏ hàng
 */
function getCartSummary($user_id = null) {
    global $db;
    $subtotal = 0;
    $count = 0;

    // Lấy danh sách sản phẩm trong giỏ hàng
    $items = getCartItems($user_id);

    foreach ($items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        $quantity = $item['quantity'];
        $subtotal += $price * $quantity;
        $count += $quantity;
    }

    // Tính phí vận chuyển
    $shipping_fee = calculateShippingFee($subtotal);

    // Miễn phí ship nếu đủ điều kiện
    $free_shipping_min = defined('FREE_SHIPPING_MIN') ? FREE_SHIPPING_MIN : 500000;
    $is_free_shipping = $shipping_fee == 0;
    $free_shipping_remaining = $is_free_shipping ? 0 : max(0, $free_shipping_min - $subtotal);

    // Tổng cộng
    $total = $subtotal + $shipping_fee;

    return [
        'count' => $count,
        'subtotal' => $subtotal,
        'shipping_fee' => $shipping_fee,
        'total' => $total,
        'is_free_shipping' => $is_free_shipping,
        'free_shipping_remaining' => $free_shipping_remaining
    ];
}

/**
 * Tính phí ship
 */
function calculateShippingFee($subtotal) {
    if ($subtotal >= FREE_SHIPPING_MIN) {
        return 0;
    }
    return DEFAULT_SHIPPING_FEE;
}

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function addToCart($product_id, $quantity = 1, $user_id = null) {
    global $db;
    
    try {
        // Kiểm tra sản phẩm
        $product = $db->fetch(
            "SELECT id, name, price, sale_price, stock_quantity, status FROM products WHERE id = ?", 
            [$product_id]
        );
        
        if (!$product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại!'];
        }
        
        if ($product['status'] !== 'active') {
            return ['success' => false, 'message' => 'Sản phẩm không khả dụng!'];
        }
        
        if ($product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
        }
        
        if ($user_id) {
            // Database cart
            $existing = $db->fetch(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", 
                [$user_id, $product_id]
            );
            
            if ($existing) {
                $new_quantity = $existing['quantity'] + $quantity;
                if ($new_quantity > $product['stock_quantity']) {
                    return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
                }
                
                $db->execute(
                    "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", 
                    [$new_quantity, $existing['id']]
                );
            } else {
                $db->execute(
                    "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())", 
                    [$user_id, $product_id, $quantity]
                );
            }
        } else {
            // Session cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            $current_quantity = $_SESSION['cart'][$product_id] ?? 0;
            $new_quantity = $current_quantity + $quantity;
            
            if ($new_quantity > $product['stock_quantity']) {
                return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
            }
            
            $_SESSION['cart'][$product_id] = $new_quantity;
        }
        
        return [
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng!',
            'cart_count' => getCartCount($user_id)
        ];
        
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi xảy ra!'];
    }
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ hàng
 */
function updateCartItem($product_id, $quantity, $user_id = null) {
    global $db;
    
    try {
        if ($quantity <= 0) {
            return removeCartItem($product_id, $user_id);
        }
        
        // Kiểm tra stock
        $product = $db->fetch(
            "SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'", 
            [$product_id]
        );
        
        if (!$product || $product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
        }
        
        if ($user_id) {
            $result = $db->execute(
                "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?", 
                [$quantity, $user_id, $product_id]
            );
        } else {
            $cart_key = $product_id;
            foreach ($_SESSION['cart'] ?? [] as $key => $qty) {
                $parts = explode('|', $key);
                if ((int)$parts[0] === (int)$product_id) {
                    $cart_key = $key;
                    break;
                }
            }
            $_SESSION['cart'][$cart_key] = $quantity;
            $result = true;
        }
        
        return [
            'success' => $result,
            'message' => 'Đã cập nhật giỏ hàng!',
            'cart_count' => getCartCount($user_id)
        ];
        
    } catch (Exception $e) {
        error_log("Update cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi xảy ra!'];
    }
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 */
function removeCartItem($product_id, $user_id = null) {
    global $db;
    $success = false;
    if ($user_id) {
        // Xóa trong database cho user đã đăng nhập
        $result = $db->execute("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        $success = $result ? true : false;
    } else {
        // Xóa trong session cho khách (hỗ trợ cả key product_id và product_id|size)
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $success = true;
        }
        foreach ($_SESSION['cart'] ?? [] as $key => $qty) {
            $parts = explode('|', $key);
            if ((int)$parts[0] === (int)$product_id) {
                unset($_SESSION['cart'][$key]);
                $success = true;
            }
        }
    }
    $cart_count = getCartCount($user_id);
    return [
        'success' => $success,
        'message' => $success ? 'Đã xóa sản phẩm khỏi giỏ hàng!' : 'Sản phẩm không tồn tại trong giỏ hàng!',
        'cart_count' => $cart_count
    ];
}

/**
 * Xóa toàn bộ giỏ hàng
 */
function clearCart($user_id = null) {
    global $db;
    
    try {
        if ($user_id) {
            $db->execute("DELETE FROM cart WHERE user_id = ?", [$user_id]);
        } else {
            $_SESSION['cart'] = [];
        }
        return true;
    } catch (Exception $e) {
        error_log("Clear cart error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate cart items trước khi checkout
 */
function validateCartItems($user_id = null) {
    global $db;
    
    $items = getCartItems($user_id);
    $errors = [];
    
    foreach ($items as $item) {
        $product_id = $item['product_id'] ?? $item['id'];
        
        // Kiểm tra sản phẩm còn tồn tại và active
        try {
            $current_product = $db->fetch(
                "SELECT id, name, stock_quantity, status FROM products WHERE id = ?", 
                [$product_id]
            );
            
            if (!$current_product) {
                $errors[] = [
                    'product_id' => $product_id,
                    'message' => "Sản phẩm '{$item['name']}' không còn tồn tại"
                ];
                continue;
            }
            
            if ($current_product['status'] !== 'active') {
                $errors[] = [
                    'product_id' => $product_id,
                    'message' => "Sản phẩm '{$item['name']}' không còn khả dụng"
                ];
                continue;
            }
            
            if ($current_product['stock_quantity'] < $item['quantity']) {
                $errors[] = [
                    'product_id' => $product_id,
                    'message' => "Sản phẩm '{$item['name']}' chỉ còn {$current_product['stock_quantity']} trong kho"
                ];
            }
        } catch (Exception $e) {
            $errors[] = [
                'product_id' => $product_id,
                'message' => "Lỗi kiểm tra sản phẩm '{$item['name']}'"
            ];
        }
    }
    
    return $errors;
}

/**
 * Chuyển đổi session cart sang database cart khi user đăng nhập
 */
function mergeSessionCartToDatabase($user_id) {
    global $db;
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        error_log("Merge cart: No session cart for user $user_id");
        return;
    }
    
    error_log("Merge cart: Session cart = " . print_r($_SESSION['cart'], true));
    
    try {
        foreach ($_SESSION['cart'] as $cart_key => $quantity) {
            // Parse format: "product_id|size" hoặc "product_id"
            $parts = explode('|', $cart_key);
            $product_id = (int)$parts[0];
            $size = isset($parts[1]) ? $parts[1] : '';
            
            if ($product_id <= 0 || $quantity <= 0) continue;
            
            // Check existing với size
            $existing = $db->fetch(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?",
                [$user_id, $product_id, $size]
            );
            
            if ($existing) {
                // Update existing
                $new_quantity = $existing['quantity'] + $quantity;
                $db->execute(
                    "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?",
                    [$new_quantity, $existing['id']]
                );
                error_log("Merge cart: Updated product $product_id, size '$size', qty $new_quantity");
            } else {
                // Insert new với size
                $db->execute(
                    "INSERT INTO cart (user_id, product_id, quantity, size, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$user_id, $product_id, $quantity, $size]
                );
                error_log("Merge cart: Inserted product $product_id, size '$size', qty $quantity");
            }
        }
        
        // Clear session cart SAU KHI merge thành công
        unset($_SESSION['cart']);
        error_log("Merge cart: Cleared session cart for user $user_id");
        
    } catch (Exception $e) {
        error_log("Merge cart ERROR: " . $e->getMessage());
    }
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'TB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Order functions
 */
function getOrderByNumber($orderNumber) {
    global $db;
    try {
        return $db->fetch("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
    } catch (Exception $e) {
        error_log("Get order error: " . $e->getMessage());
        return null;
    }
}

if (!function_exists('getOrderItems')) {
function getOrderItems($order_id) {
    global $db;
    try {
        return $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);
    } catch (Exception $e) {
        error_log("Get order items error: " . $e->getMessage());
        return [];
    }
}
}

function getUserOrders($userId, $limit = 10) {
    global $db;
    try {
        return $db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    } catch (Exception $e) {
        error_log("Get user orders error: " . $e->getMessage());
        return [];
    }
}

function getCartCount($user_id = null) {
    global $db;
    try {
        if ($user_id && is_numeric($user_id) && $user_id > 0) {
            // Logged in user - get from database
            $result = $db->fetch(
                "SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?",
                [$user_id]
            );
            $count = (int)($result['total'] ?? 0);
            error_log("DB Cart count for user $user_id: $count");
            return $count;
        } else {
            // Guest user - get from session
            $cart = $_SESSION['cart'] ?? [];
            $count = (int)array_sum($cart);
            error_log("Session cart count: $count, Cart data: " . print_r($cart, true));
            return $count;
        }
    } catch (Exception $e) {
        error_log("Get cart count error: " . $e->getMessage());
        return 0;
    }
}

function getCartTotal($user_id = null) {
    $summary = getCartSummary($user_id);
    return $summary['total'];
}

// ⭐ REMOVED: Không định nghĩa lại các function đã có trong config.php:
// - validateEmail()
// - validatePhone() 
// - sanitizeInput()
// - formatPrice()
// - formatDateVN()
// - setFlashMessage()
// - getFlashMessage()
// - isLoggedIn()
// - redirect()
?>