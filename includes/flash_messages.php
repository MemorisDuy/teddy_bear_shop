<?php
/**
 * FLASH MESSAGES SYSTEM - Teddy Bear Shop 💬
 * HỆ THỐNG THÔNG BÁO FLASH HOÀN CHỈNH
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize flash array if not exists
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}

/**
 * Set a flash message
 * @param string $type - success, error, warning, info
 * @param string $message - Message content
 */
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
}

/**
 * Get and remove a flash message
 * @param string $type - Message type
 * @return string|null
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}

/**
 * Get all flash messages and clear them
 * @return array
 */
if (!function_exists('getAllFlashMessages')) {
    function getAllFlashMessages() {
        $messages = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $messages;
    }
}

/**
 * Check if flash message exists
 * @param string $type - Message type
 * @return bool
 */
if (!function_exists('hasFlashMessage')) {
    function hasFlashMessage($type = null) {
        if ($type === null) {
            return !empty($_SESSION['flash']);
        }
        return isset($_SESSION['flash'][$type]);
    }
}

/**
 * Alias for setFlashMessage
 * @param string $type
 * @param string $message
 */
if (!function_exists('flashMessage')) {
    function flashMessage($type, $message) {
        setFlashMessage($type, $message);
    }
}

/**
 * Display flash messages as HTML
 * @param bool $auto_dismiss - Auto dismiss after time
 * @return string
 */
function displayFlashMessages($auto_dismiss = true) {
    if (!hasFlashMessage()) {
        return '';
    }
    
    $html = '';
    $messages = getAllFlashMessages();
    
    foreach ($messages as $type => $message) {
        $alertType = $type === 'error' ? 'danger' : $type;
        $icon = getFlashIcon($type);
        $dismissClass = $auto_dismiss ? 'alert-dismissible fade show' : '';
        
        $html .= '<div class="alert alert-' . $alertType . ' ' . $dismissClass . '" role="alert">';
        $html .= '<span class="cute-icon me-2">' . $icon . '</span>';
        $html .= htmlspecialchars($message);
        
        if ($auto_dismiss) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        }
        
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Get icon for flash message type
 * @param string $type
 * @return string
 */
function getFlashIcon($type) {
    $icons = [
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️',
        'danger' => '❌'
    ];
    
    return $icons[$type] ?? 'ℹ️';
}

/**
 * Display flash messages with JavaScript notification
 * @return string
 */
function displayFlashNotifications() {
    if (!hasFlashMessage()) {
        return '';
    }
    
    $messages = getAllFlashMessages();
    $script = '<script>';
    $script .= 'document.addEventListener("DOMContentLoaded", function() {';
    
    foreach ($messages as $type => $message) {
        $jsMessage = addslashes($message);
        $script .= "showNotification('$jsMessage', '$type');";
    }
    
    $script .= '});';
    $script .= '</script>';
    
    return $script;
}

/**
 * Clear all flash messages
 */
function clearFlashMessages() {
    $_SESSION['flash'] = [];
}

/**
 * Set multiple flash messages at once
 * @param array $messages - ['type' => 'message', ...]
 */
function setFlashMessages($messages) {
    foreach ($messages as $type => $message) {
        setFlashMessage($type, $message);
    }
}

/**
 * Flash message shortcuts
 */
function flashSuccess($message) {
    setFlashMessage('success', $message);
}

function flashError($message) {
    setFlashMessage('error', $message);
}

function flashWarning($message) {
    setFlashMessage('warning', $message);
}

function flashInfo($message) {
    setFlashMessage('info', $message);
}

/**
 * Enhanced flash message with emoji
 * @param string $type
 * @param string $message
 * @param string $emoji
 */
function flashWithEmoji($type, $message, $emoji = '') {
    if (empty($emoji)) {
        $emojis = [
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️'
        ];
        $emoji = $emojis[$type] ?? '';
    }
    
    setFlashMessage($type, $emoji . ' ' . $message);
}

/**
 * Flash message for specific actions
 */
function flashLoginSuccess($username = 'bạn') {
    flashWithEmoji('success', "Chào mừng $username trở lại! Đăng nhập thành công.", '🎉');
}

function flashLogoutSuccess($username = 'bạn') {
    flashWithEmoji('success', "Tạm biệt $username! Hẹn gặp lại.", '👋');
}

function flashRegistrationSuccess() {
    flashWithEmoji('success', 'Đăng ký thành công! Vui lòng đăng nhập.', '🎊');
}

function flashCartAdded($productName = 'sản phẩm') {
    flashWithEmoji('success', "Đã thêm $productName vào giỏ hàng!", '🛒');
}

function flashOrderCreated($orderNumber = '') {
    $message = $orderNumber ? "Đặt hàng thành công! Mã đơn: $orderNumber" : 'Đặt hàng thành công!';
    flashWithEmoji('success', $message, '📦');
}

/**
 * Debug flash messages (only in debug mode)
 * @param string $message
 */
function flashDebug($message) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        setFlashMessage('info', '[DEBUG] ' . $message);
    }
}

/**
 * Get flash messages as JSON (for AJAX)
 * @param bool $clear - Clear messages after getting
 * @return string
 */
function getFlashMessagesJson($clear = true) {
    $messages = $_SESSION['flash'] ?? [];
    
    if ($clear) {
        clearFlashMessages();
    }
    
    return json_encode($messages);
}

/**
 * Set flash message from AJAX
 * @param string $json - JSON string
 */
function setFlashMessagesFromJson($json) {
    $messages = json_decode($json, true);
    if (is_array($messages)) {
        setFlashMessages($messages);
    }
}

/**
 * Middleware to preserve flash messages across redirects
 */
function preserveFlashMessages() {
    if (hasFlashMessage()) {
        $messages = $_SESSION['flash'];
        // Store in a temp variable to restore after redirect
        $_SESSION['flash_preserve'] = $messages;
    }
}

function restoreFlashMessages() {
    if (isset($_SESSION['flash_preserve'])) {
        $_SESSION['flash'] = array_merge($_SESSION['flash'] ?? [], $_SESSION['flash_preserve']);
        unset($_SESSION['flash_preserve']);
    }
}

/**
 * Count flash messages
 * @param string|null $type - Specific type or all
 * @return int
 */
function countFlashMessages($type = null) {
    if ($type === null) {
        return count($_SESSION['flash'] ?? []);
    }
    
    return isset($_SESSION['flash'][$type]) ? 1 : 0;
}

/**
 * Flash message template for consistent styling
 * @param string $type
 * @param string $title
 * @param string $message
 * @param array $options
 */
function flashTemplate($type, $title, $message, $options = []) {
    $template = "<strong>$title:</strong> $message";
    
    if (isset($options['link'])) {
        $template .= ' <a href="' . $options['link']['url'] . '" class="alert-link">' . $options['link']['text'] . '</a>';
    }
    
    setFlashMessage($type, $template);
}

/**
 * System status flash messages
 */
function flashSystemStatus() {
    // Check system health
    $status = [];
    
    // Database check
    try {
        global $db;
        if ($db && $db->ping()) {
            $status['database'] = true;
        }
    } catch (Exception $e) {
        $status['database'] = false;
        flashError('Lỗi kết nối cơ sở dữ liệu!');
    }
    
    // Session check
    if (session_status() === PHP_SESSION_ACTIVE) {
        $status['session'] = true;
    } else {
        $status['session'] = false;
        flashWarning('Lỗi phiên làm việc!');
    }
    
    return $status;
}

/**
 * Alias for displayFlashMessages - displays flash messages
 * @param bool $auto_dismiss - Auto dismiss after time
 */
function displayFlashMessage($auto_dismiss = true) {
    return displayFlashMessages($auto_dismiss);
}

/**
 * Auto-clear old flash messages (security)
 */
function autoCleanFlashMessages() {
    if (isset($_SESSION['flash_timestamp'])) {
        // Clear messages older than 10 minutes
        if (time() - $_SESSION['flash_timestamp'] > 600) {
            clearFlashMessages();
        }
    }
    
    $_SESSION['flash_timestamp'] = time();
}