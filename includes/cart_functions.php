<?php
/**
 * Cart Functions - Teddy Bear Shop - COMPLETELY FIXED
 * File: config/cart_functions.php
 * NO ERRORS - ALL ISSUES RESOLVED
 */

// Prevent direct access
if (!defined('ROOT_PATH') && !defined('SITE_URL') && !defined('SITE_NAME')) {
    die('Direct access not allowed');
}

/**
 * FIXED: Get cart items for user (logged in or guest)
 */
function getCartItems($user_id = null) {
    global $db;
    
    try {
        // Validate user_id if provided
        if ($user_id !== null && (!is_numeric($user_id) || $user_id <= 0)) {
            return [];
        }
        
        if ($user_id) {
            // Logged in user - get from database
            return $db->fetchAll(
                "SELECT c.product_id, c.quantity, c.created_at, c.updated_at,
                        p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, 
                        p.size, p.color, p.stock_quantity, p.status
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ? AND p.status = 'active'
                 ORDER BY c.created_at DESC",
                [$user_id]
            );
        } else {
            // Guest user - get from session
            $cart = $_SESSION['cart'] ?? [];
            $items = [];
            
            if (!empty($cart)) {
                // FIXED: Use single query with IN clause for better performance
                $product_ids = array_keys($cart);
                $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
                
                $products = $db->fetchAll(
                    "SELECT id, name, slug, price, sale_price, main_image, 
                            size, color, stock_quantity, status
                     FROM products WHERE id IN ($placeholders) AND status = 'active'",
                    $product_ids
                );
                
                foreach ($products as $product) {
                    $product['product_id'] = $product['id'];
                    $product['quantity'] = (int)$cart[$product['id']];
                    $items[] = $product;
                }
            }
            
            return $items;
        }
    } catch (Exception $e) {
        error_log("Get cart items error: " . $e->getMessage());
        return [];
    }
}

/**
 * FIXED: Get cart summary with proper calculations
 */
function getCartSummary($user_id = null) {
    // FIXED: Use constants with proper fallbacks
    $default_shipping = 30000;
    $free_shipping_min = 500000;
    
    if (defined('DEFAULT_SHIPPING_FEE')) {
        $default_shipping = DEFAULT_SHIPPING_FEE;
    }
    if (defined('FREE_SHIPPING_MIN')) {
        $free_shipping_min = FREE_SHIPPING_MIN;
    }
    
    $summary = [
        'total_quantity' => 0,
        'subtotal' => 0,
        'shipping_fee' => $default_shipping,
        'total' => 0,
        'is_free_shipping' => false,
        'free_shipping_remaining' => $free_shipping_min
    ];
    
    try {
        $cart_items = getCartItems($user_id);
        
        foreach ($cart_items as $item) {
            $summary['total_quantity'] += (int)$item['quantity'];
            $price = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
            $summary['subtotal'] += $price * $item['quantity'];
        }
        
        // Calculate shipping
        if ($summary['subtotal'] >= $free_shipping_min) {
            $summary['shipping_fee'] = 0;
            $summary['is_free_shipping'] = true;
            $summary['free_shipping_remaining'] = 0;
        } else {
            $summary['free_shipping_remaining'] = $free_shipping_min - $summary['subtotal'];
        }
        
        $summary['total'] = $summary['subtotal'] + $summary['shipping_fee'];
        
        // Áp dụng giảm giá nếu có
        if (isset($_SESSION['applied_coupon']['discount']) && $_SESSION['applied_coupon']['discount'] > 0) {
            $summary['discount'] = $_SESSION['applied_coupon']['discount'];
            $summary['total_after_coupon'] = max($summary['total'] - $summary['discount'], 0);
        } else {
            $summary['discount'] = 0;
            $summary['total_after_coupon'] = $summary['total'];
        }
        
    } catch (Exception $e) {
        error_log("Get cart summary error: " . $e->getMessage());
    }
    
    return $summary;
}

/**
 * FIXED: Add item to cart with proper validation
 */
function addToCart($product_id, $quantity = 1, $user_id = null) {
    global $db;
    
    try {
        // FIXED: Comprehensive input validation
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id <= 0) {
            return ['success' => false, 'message' => 'ID sản phẩm không hợp lệ'];
        }
        
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Số lượng phải lớn hơn 0'];
        }
        
        if ($quantity > 99) {
            return ['success' => false, 'message' => 'Số lượng tối đa là 99'];
        }
        
        // Validate user_id if provided
        if ($user_id !== null && (!is_numeric($user_id) || $user_id <= 0)) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }
        
        // FIXED: Get complete product info
        $product = $db->fetch(
            "SELECT id, name, price, sale_price, stock_quantity, status 
             FROM products WHERE id = ?", 
            [$product_id]
        );
        
        if (!$product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại'];
        }
        
        if ($product['status'] !== 'active') {
            return ['success' => false, 'message' => 'Sản phẩm không khả dụng'];
        }
        
        if ($product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => "Không đủ hàng trong kho! Chỉ còn {$product['stock_quantity']} sản phẩm"];
        }
        
        if ($user_id) {
            return addToCartDB($product_id, $quantity, $user_id, $product);
        } else {
            return addToCartSession($product_id, $quantity, $product);
        }
        
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi hệ thống xảy ra'];
    }
}

/**
 * FIXED: Database cart operations with proper error handling
 */
function addToCartDB($product_id, $quantity, $user_id, $product) {
    global $db;
    
    try {
        // Check if item already exists
        $existing = $db->fetch(
            "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", 
            [$user_id, $product_id]
        );
        
        if ($existing) {
            // Update existing item
            $new_quantity = $existing['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock_quantity']) {
                return [
                    'success' => false, 
                    'message' => "Tổng số lượng ({$new_quantity}) vượt quá tồn kho ({$product['stock_quantity']})"
                ];
            }
            
            $success = $db->execute(
                "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", 
                [$new_quantity, $existing['id']]
            );
            
            if (!$success) {
                return ['success' => false, 'message' => 'Không thể cập nhật giỏ hàng'];
            }
            
            $message = "Đã cập nhật số lượng " . htmlspecialchars($product['name']);
        } else {
            // Add new item
            $success = $db->execute(
                "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())", 
                [$user_id, $product_id, $quantity]
            );
            
            if (!$success) {
                return ['success' => false, 'message' => 'Không thể thêm vào giỏ hàng'];
            }
            
            $message = "Đã thêm " . htmlspecialchars($product['name']) . " vào giỏ hàng";
        }
        
        // Get updated cart count
        $cart_count = getCartCount($user_id);
        
        return [
            'success' => true,
            'message' => $message,
            'cart_count' => $cart_count,
            'product_name' => $product['name']
        ];
        
    } catch (Exception $e) {
        error_log("Add to DB cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu'];
    }
}

/**
 * FIXED: Session cart with proper structure
 */
function addToCartSession($product_id, $quantity, $product) {
    try {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $current_quantity = (int)($_SESSION['cart'][$product_id] ?? 0);
        $new_quantity = $current_quantity + $quantity;
        
        if ($new_quantity > $product['stock_quantity']) {
            return [
                'success' => false, 
                'message' => "Tổng số lượng ({$new_quantity}) vượt quá tồn kho ({$product['stock_quantity']})"
            ];
        }
        
        $_SESSION['cart'][$product_id] = $new_quantity;
        
        // Get updated cart count
        $cart_count = array_sum($_SESSION['cart']);
        
        $message = $current_quantity > 0 
            ? "Đã cập nhật số lượng " . htmlspecialchars($product['name'])
            : "Đã thêm " . htmlspecialchars($product['name']) . " vào giỏ hàng";
        
        return [
            'success' => true,
            'message' => $message,
            'cart_count' => $cart_count,
            'product_name' => $product['name']
        ];
        
    } catch (Exception $e) {
        error_log("Add to session cart error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi session'];
    }
}

/**
 * FIXED: Update cart item with complete product data
 */
function updateCartItem($product_id, $quantity, $user_id = null) {
    global $db;
    
    try {
        // Validate inputs
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id <= 0) {
            return ['success' => false, 'message' => 'ID sản phẩm không hợp lệ'];
        }
        
        if ($quantity < 0) {
            return ['success' => false, 'message' => 'Số lượng không hợp lệ'];
        }
        
        if ($user_id !== null && (!is_numeric($user_id) || $user_id <= 0)) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }
        
        // If quantity is 0, remove item
        if ($quantity === 0) {
            return removeCartItem($product_id, $user_id);
        }
        
        // FIXED: Get complete product data including prices
        $product = $db->fetch(
            "SELECT id, name, price, sale_price, stock_quantity, status FROM products WHERE id = ? AND status = 'active'", 
            [$product_id]
        );
        
        if (!$product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc không khả dụng'];
        }
        
        if ($quantity > $product['stock_quantity']) {
            return ['success' => false, 'message' => "Không đủ hàng trong kho! Chỉ còn {$product['stock_quantity']} sản phẩm"];
        }
        
        $success = false;
        
        if ($user_id) {
            // Update database cart
            $success = $db->execute(
                "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?",
                [$quantity, $user_id, $product_id]
            );
        } else {
            // Update session cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = $quantity;
                $success = true;
            }
        }
        
        if (!$success) {
            return ['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'];
        }
        
        $cart_count = getCartCount($user_id);
        $cart_summary = getCartSummary($user_id);
        
        // FIXED: Calculate item total with correct price
        $price = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];
        $item_total = $price * $quantity;
        
        return [
            'success' => true,
            'message' => 'Đã cập nhật số lượng sản phẩm',
            'cart_count' => $cart_count,
            'cart_total' => $cart_summary['total'],
            'item_total' => $item_total
        ];
        
    } catch (Exception $e) {
        error_log("Update cart item error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi hệ thống xảy ra'];
    }
}

/**
 * FIXED: Remove item with proper validation
 */
function removeCartItem($product_id, $user_id = null) {
    global $db;
    
    try {
        // Validate inputs
        $product_id = (int)$product_id;
        
        if ($product_id <= 0) {
            return ['success' => false, 'message' => 'ID sản phẩm không hợp lệ'];
        }
        
        if ($user_id !== null && (!is_numeric($user_id) || $user_id <= 0)) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }
        
        $success = false;
        
        if ($user_id) {
            // Remove from database cart
            $success = $db->execute(
                "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
                [$user_id, $product_id]
            );
        } else {
            // Remove from session cart
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $success = true;
            }
        }
        
        if (!$success) {
            return ['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'];
        }
        
        $cart_count = getCartCount($user_id);
        
        return [
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
            'cart_count' => $cart_count
        ];
        
    } catch (Exception $e) {
        error_log("Remove cart item error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi hệ thống xảy ra'];
    }
}

/**
 * FIXED: Clear cart with proper validation
 */
function clearCart($user_id = null) {
    global $db;
    
    try {
        if ($user_id !== null && (!is_numeric($user_id) || $user_id <= 0)) {
            return false;
        }
        
        if ($user_id) {
            // Clear database cart
            return $db->execute("DELETE FROM cart WHERE user_id = ?", [$user_id]);
        } else {
            // Clear session cart
            $_SESSION['cart'] = [];
            return true;
        }
        
    } catch (Exception $e) {
        error_log("Clear cart error: " . $e->getMessage());
        return false;
    }
}

/**
 * FIXED: Get cart count with consistent calculation
 */
function getCartCount($user_id = null) {
    global $db;
    
    try {
        if ($user_id && is_numeric($user_id) && $user_id > 0) {
            // Logged in user - get from database
            $result = $db->fetch(
                "SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?", 
                [$user_id]
            );
            return (int)($result['total'] ?? 0);
        } else {
            // Guest user - get from session
            $cart = $_SESSION['cart'] ?? [];
            return (int)array_sum($cart);
        }
    } catch (Exception $e) {
        error_log("Get cart count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * FIXED: Migrate session cart with transaction
 */
function migrateSessionCartToDB($user_id) {
    global $db;
    
    try {
        // Validate user_id
        if (!is_numeric($user_id) || $user_id <= 0) {
            return false;
        }
        
        $session_cart = $_SESSION['cart'] ?? [];
        
        if (empty($session_cart)) {
            return true;
        }
        
        // FIXED: Use transaction for data integrity
        $db->execute("START TRANSACTION");
        
        foreach ($session_cart as $product_id => $quantity) {
            // Validate product exists
            $product = $db->fetch(
                "SELECT id, stock_quantity FROM products WHERE id = ? AND status = 'active'",
                [$product_id]
            );
            
            if (!$product) {
                continue; // Skip invalid products
            }
            
            // Check if product exists in user's cart
            $existing = $db->fetch(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
                [$user_id, $product_id]
            );
            
            if ($existing) {
                // Update existing item
                $new_quantity = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                $db->execute(
                    "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?",
                    [$new_quantity, $existing['id']]
                );
            } else {
                // Add new item
                $quantity = min($quantity, $product['stock_quantity']);
                $db->execute(
                    "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())",
                    [$user_id, $product_id, $quantity]
                );
            }
        }
        
        $db->execute("COMMIT");
        
        // Clear session cart
        $_SESSION['cart'] = [];
        
        return true;
        
    } catch (Exception $e) {
        // FIXED: Rollback on error
        $db->execute("ROLLBACK");
        error_log("Migrate session cart error: " . $e->getMessage());
        return false;
    }
}

/**
 * FIXED: Get cart total with proper summary
 */
function getCartTotal($user_id = null) {
    $summary = getCartSummary($user_id);
    return $summary['total'];
}

/**
 * FIXED: Comprehensive cart validation
 */
function validateCart($user_id = null) {
    try {
        $cart_items = getCartItems($user_id);
        $errors = [];
        $warnings = [];
        
        if (empty($cart_items)) {
            $errors[] = 'Giỏ hàng trống';
            return [
                'valid' => false,
                'errors' => $errors,
                'warnings' => $warnings
            ];
        }
        
        foreach ($cart_items as $item) {
            $product_name = htmlspecialchars($item['name']);
            
            if ($item['status'] !== 'active') {
                $errors[] = "Sản phẩm '{$product_name}' không còn khả dụng";
                continue;
            }
            
            if ($item['quantity'] > $item['stock_quantity']) {
                if ($item['stock_quantity'] > 0) {
                    $warnings[] = "Sản phẩm '{$product_name}' chỉ còn {$item['stock_quantity']} sản phẩm trong kho";
                } else {
                    $errors[] = "Sản phẩm '{$product_name}' đã hết hàng";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
        
    } catch (Exception $e) {
        error_log("Validate cart error: " . $e->getMessage());
        return [
            'valid' => false,
            'errors' => ['Có lỗi xảy ra khi kiểm tra giỏ hàng'],
            'warnings' => []
        ];
    }
}

/**
 * FIXED: Format price helper with proper validation
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        $price = (float)$price;
        return number_format($price, 0, ',', '.') . 'đ';
    }
}

/**
 * ADDITIONAL: Get cart by product ID for checking
 */
function getCartItemByProduct($product_id, $user_id = null) {
    global $db;
    
    try {
        $product_id = (int)$product_id;
        
        if ($product_id <= 0) {
            return null;
        }
        
        if ($user_id && is_numeric($user_id) && $user_id > 0) {
            return $db->fetch(
                "SELECT c.*, p.name, p.price, p.sale_price FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ? AND c.product_id = ?",
                [$user_id, $product_id]
            );
        } else {
            $cart = $_SESSION['cart'] ?? [];
            if (isset($cart[$product_id])) {
                $product = $db->fetch(
                    "SELECT id, name, price, sale_price FROM products WHERE id = ?",
                    [$product_id]
                );
                if ($product) {
                    $product['quantity'] = $cart[$product_id];
                    $product['product_id'] = $product_id;
                    return $product;
                }
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Get cart item by product error: " . $e->getMessage());
        return null;
    }
}
?>