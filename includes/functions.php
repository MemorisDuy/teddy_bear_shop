<?php
/**
 * Helper Functions - Teddy Bear Shop 🐻
 * SAFE VERSION - No duplicate function declarations
 */

// Product Functions
if (!function_exists('getAllProducts')) {
    function getAllProducts($limit = null, $offset = 0) {
        global $db;
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        return $db->fetchAll($sql);
    }
}

if (!function_exists('getProductsByCategory')) {
    function getProductsByCategory($category_slug, $limit = null) {
        global $db;
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND c.slug = ?
                ORDER BY p.created_at DESC";
        
        $params = [$category_slug];
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $db->fetchAll($sql, $params);
    }
}

// SKIP getFeaturedProducts - already in config.php

if (!function_exists('getLatestProducts')) {
    function getLatestProducts($limit = 12) {
        global $db;
        return $db->fetchAll(
            "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
}

if (!function_exists('searchProducts')) {
    function searchProducts($keyword, $category_id = '', $min_price = '', $max_price = '', $sort = 'relevance', $limit = 1000) {
        global $db;
        $params = [];
        $sql = "SELECT * FROM products WHERE status = 'active'";
        if (!empty($keyword)) {
            $sql .= " AND (LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(short_description) LIKE ?)";
            $kw = '%' . mb_strtolower($keyword, 'UTF-8') . '%';
            $params[] = $kw; $params[] = $kw; $params[] = $kw;
        }
        if (!empty($category_id)) {
            $sql .= " AND category_id = ?";
            $params[] = $category_id;
        }
        if ($min_price !== '' && is_numeric($min_price)) {
            $sql .= " AND (sale_price > 0 AND sale_price >= ? OR (sale_price = 0 OR sale_price IS NULL) AND price >= ?)";
            $params[] = $min_price; $params[] = $min_price;
        }
        if ($max_price !== '' && is_numeric($max_price)) {
            $sql .= " AND (sale_price > 0 AND sale_price <= ? OR (sale_price = 0 OR sale_price IS NULL) AND price <= ?)";
            $params[] = $max_price; $params[] = $max_price;
        }
        // Sort
        switch ($sort) {
            case 'latest':
                $sql .= " ORDER BY created_at DESC";
                break;
            case 'price_low':
                $sql .= " ORDER BY (CASE WHEN sale_price > 0 THEN sale_price ELSE price END) ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY (CASE WHEN sale_price > 0 THEN sale_price ELSE price END) DESC";
                break;
            case 'name':
                $sql .= " ORDER BY name ASC";
                break;
            default:
                $sql .= " ORDER BY name ASC";
        }
        $sql .= " LIMIT ?";
        $params[] = $limit;
        return $db->fetchAll($sql, $params);
    }
}

if (!function_exists('getRelatedProducts')) {
    function getRelatedProducts($product_id, $category_id, $limit = 4) {
        global $db;
        return $db->fetchAll(
            "SELECT * FROM products 
             WHERE status = 'active' AND category_id = ? AND id != ?
             ORDER BY RAND() LIMIT ?",
            [$category_id, $product_id, $limit]
        );
    }
}

// SKIP getCategoryBySlug - already in config.php

if (!function_exists('getCategoryProducts')) {
    function getCategoryProducts($category_id, $limit = null) {
        global $db;
        $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
        $params = [$category_id];
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $db->fetchAll($sql, $params);
    }
}

// Cart Functions
if (!function_exists('getCartItems')) {
    function getCartItems($user_id = null) {
        global $db;
        
        if ($user_id) {
            return $db->fetchAll(
                "SELECT c.*, p.name, p.price, p.sale_price, p.main_image, p.stock_quantity
                 FROM cart c
                 INNER JOIN products p ON c.product_id = p.id
                 WHERE c.user_id = ? AND p.status = 'active'",
                [$user_id]
            );
        } else {
            // Session-based cart for guests
            $cart = $_SESSION['cart'] ?? [];
            $items = [];
            
            foreach ($cart as $product_id => $quantity) {
                $product = $db->fetch("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);
                if ($product) {
                    $product['quantity'] = $quantity;
                    $items[] = $product;
                }
            }
            
            return $items;
        }
    }
}

if (!function_exists('addToCart')) {
    function addToCart($product_id, $quantity = 1, $user_id = null) {
        global $db;
        
        // Check if product exists and is available
        $product = $db->fetch("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);
        if (!$product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại!'];
        }
        
        if ($product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
        }
        
        if ($user_id) {
            // Database cart for logged-in users
            $existingItem = $db->fetch("SELECT * FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
            
            if ($existingItem) {
                $newQuantity = $existingItem['quantity'] + $quantity;
                if ($newQuantity > $product['stock_quantity']) {
                    return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
                }
                
                $db->execute("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", [$newQuantity, $existingItem['id']]);
            } else {
                $db->execute("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", [$user_id, $product_id, $quantity]);
            }
        } else {
            // Session cart for guests
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            $currentQuantity = $_SESSION['cart'][$product_id] ?? 0;
            $newQuantity = $currentQuantity + $quantity;
            
            if ($newQuantity > $product['stock_quantity']) {
                return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
            }
            
            $_SESSION['cart'][$product_id] = $newQuantity;
        }
        
        return [
            'success' => true, 
            'message' => 'Đã thêm vào giỏ hàng!',
            'cart_count' => getCartCount($user_id)
        ];
    }
}

if (!function_exists('updateCartQuantity')) {
    function updateCartQuantity($product_id, $quantity, $user_id = null) {
        global $db;
        
        if ($quantity <= 0) {
            return removeFromCart($product_id, $user_id);
        }
        
        // Check stock
        $product = $db->fetch("SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'", [$product_id]);
        if (!$product || $product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Không đủ hàng trong kho!'];
        }
        
        if ($user_id) {
            $result = $db->execute("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?", 
                                  [$quantity, $user_id, $product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
            $result = true;
        }
        
        return [
            'success' => $result,
            'message' => $result ? 'Đã cập nhật giỏ hàng!' : 'Có lỗi xảy ra!',
            'cart_count' => getCartCount($user_id)
        ];
    }
}

if (!function_exists('removeFromCart')) {
    function removeFromCart($product_id, $user_id = null) {
        global $db;
        
        if ($user_id) {
            $result = $db->execute("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        } else {
            unset($_SESSION['cart'][$product_id]);
            $result = true;
        }
        
        return [
            'success' => $result,
            'message' => $result ? 'Đã xóa khỏi giỏ hàng!' : 'Có lỗi xảy ra!',
            'cart_count' => getCartCount($user_id)
        ];
    }
}

if (!function_exists('clearCart')) {
    function clearCart($user_id = null) {
        global $db;
        
        if ($user_id) {
            $db->execute("DELETE FROM cart WHERE user_id = ?", [$user_id]);
        } else {
            $_SESSION['cart'] = [];
        }
    }
}

// SKIP getCartCount - already in config.php

if (!function_exists('getCartTotal')) {
    function getCartTotal($user_id = null) {
        $items = getCartItems($user_id);
        $total = 0;
        
        foreach ($items as $item) {
            $price = $item['sale_price'] ?? $item['price'];
            $quantity = $item['quantity'];
            $total += $price * $quantity;
        }
        
        return $total;
    }
}

// Order Functions
if (!function_exists('createOrder')) {
    function createOrder($orderData, $orderItems, $user_id = null) {
        global $db;
        
        try {
            // Start transaction
            $db->getConnection()->begin_transaction();
            
            // Generate order number
            $orderNumber = generateOrderNumber();
            
            // Insert order
            $orderId = $db->insert(
                "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, 
                 shipping_address, subtotal, shipping_fee, total_amount, payment_method, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $orderNumber,
                    $user_id,
                    $orderData['customer_name'],
                    $orderData['customer_email'],
                    $orderData['customer_phone'],
                    $orderData['shipping_address'],
                    $orderData['subtotal'],
                    $orderData['shipping_fee'],
                    $orderData['total_amount'],
                    $orderData['payment_method'],
                    $orderData['notes'] ?? ''
                ]
            );
            
            // Insert order items
            foreach ($orderItems as $item) {
                $db->execute(
                    "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $orderId,
                        $item['product_id'],
                        $item['product_name'],
                        $item['product_image'],
                        $item['quantity'],
                        $item['price'],
                        $item['total']
                    ]
                );
                
                // Update stock
                $db->execute(
                    "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
            
            // Clear cart
            clearCart($user_id);
            
            // Commit transaction
            $db->getConnection()->commit();
            
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
            
        } catch (Exception $e) {
            $db->getConnection()->rollback();
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('getOrderByNumber')) {
    function getOrderByNumber($orderNumber) {
        global $db;
        return $db->fetch("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
    }
}

if (!function_exists('getOrderItems')) {
    function getOrderItems($orderId) {
        global $db;
        return $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);
    }
}

if (!function_exists('getUserOrders')) {
    function getUserOrders($userId, $limit = 10) {
        global $db;
        return $db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}

// User Functions
if (!function_exists('getUserById')) {
    function getUserById($userId) {
        global $db;
        return $db->fetch("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
    }
}

if (!function_exists('updateUser')) {
    function updateUser($userId, $userData) {
        global $db;
        
        $fields = [];
        $params = [];
        
        foreach ($userData as $field => $value) {
            if (in_array($field, ['full_name', 'email', 'phone', 'address', 'avatar'])) {
                $fields[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        return $db->execute($sql, $params);
    }
}

// SKIP getProductReviews, addReview - already in config.php

if (!function_exists('updateProductRating')) {
    function updateProductRating($productId) {
        global $db;
        
        $stats = $db->fetch(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
             FROM reviews WHERE product_id = ? AND status = 'approved'",
            [$productId]
        );
        
        if ($stats) {
            $db->execute(
                "UPDATE products SET rating = ?, review_count = ? WHERE id = ?",
                [round($stats['avg_rating'], 2), $stats['review_count'], $productId]
            );
        }
    }
}

// SKIP getSetting - already in config.php

if (!function_exists('updateSetting')) {
    function updateSetting($key, $value) {
        global $db;
        
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        
        if ($existing) {
            return $db->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
        } else {
            return $db->execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }
}

// Utility Functions
if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber() {
        return 'TB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('calculateShippingFee')) {
    function calculateShippingFee($total, $location = 'other') {
        $freeShippingMin = getSetting('free_shipping_min', 500000);
        
        if ($total >= $freeShippingMin) {
            return 0;
        }
        
        $baseFee = getSetting('shipping_fee', 30000);
        
        $locationFees = [
            'hcm' => $baseFee,
            'hn' => $baseFee,
            'dn' => $baseFee + 5000,
            'other' => $baseFee + 10000
        ];
        
        return $locationFees[$location] ?? $locationFees['other'];
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('isValidPhone')) {
    function isValidPhone($phone) {
        return preg_match('/^[0-9]{10,11}$/', str_replace([' ', '-', '.'], '', $phone));
    }
}

if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
    }
}

// SKIP generateSlug - already in config.php

if (!function_exists('getImagePath')) {
    function getImagePath($imageName, $folder = 'products') {
        if (empty($imageName)) {
            return IMG_PATH . 'placeholder.jpg';
        }
        
        $imagePath = IMG_PATH . $folder . '/' . $imageName;
        return $imagePath;
    }
}

if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Debug function
if (!function_exists('debugLog')) {
    function debugLog($data, $label = 'DEBUG') {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log($label . ': ' . print_r($data, true));
        }
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}
?>