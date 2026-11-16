<?php
/**
 * Login Page - Đăng nhập 🔐
 * Simple version with debug and fixes
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Check if functions exist
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../user/profile.php');
}

// Initialize flash messages array
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}

// Debug mode
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if ($debug) {
        error_log("=== LOGIN DEBUG START ===");
        error_log("Email: " . $email);
        error_log("Password length: " . strlen($password));
        error_log("Remember: " . ($remember ? 'yes' : 'no'));
    }
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['flash']['error'] = 'Vui lòng nhập đầy đủ thông tin!';
        if ($debug) error_log("Validation failed: empty fields");
    } else {
        try {
            // Luôn dùng $db từ config/config.php
            if (isset($db)) {
                // Attempt login
                $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
                if ($debug) error_log("Executing SQL: " . $sql);
                
                $user = $db->fetch($sql, [$email]);
                
                if ($debug) {
                    if ($user) {
                        error_log("User found: " . $user['full_name'] . " (ID: " . $user['id'] . ")");
                        error_log("Stored hash: " . substr($user['password'], 0, 20) . "...");
                    } else {
                        error_log("User not found with email: " . $email);
                        
                        // Check if user exists with any status
                        $anyUser = $db->fetch("SELECT id, email, status FROM users WHERE email = ?", [$email]);
                        if ($anyUser) {
                            error_log("User exists but status is: " . $anyUser['status']);
                        } else {
                            error_log("User does not exist at all");
                        }
                    }
                }
                
                if ($user && password_verify($password, $user['password'])) {
                    if ($debug) error_log("Password verification: SUCCESS");
                    
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'] ?? 'user';
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    // Merge session cart to database
                    if (function_exists('mergeSessionCartToDatabase')) {
                        mergeSessionCartToDatabase($_SESSION['user_id']);
                    }
                    
                    // Set remember me cookie
                    if ($remember) {
                        $token = base64_encode($email . '|' . time());
                        setcookie('teddy_remember', $token, time() + (86400 * 30), '/'); // 30 days
                        if ($debug) error_log("Remember me cookie set");
                    }
                    
                    // Update last login
                    try {
                        $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                        if ($debug) error_log("Last login updated");
                    } catch (Exception $e) {
                        if ($debug) error_log("Failed to update last login: " . $e->getMessage());
                    }
                    
                    $_SESSION['flash']['success'] = 'Đăng nhập thành công! Chào mừng bạn trở lại! 🎉';
                    
                    // Redirect to intended page or home
                    $redirect_url = $_SESSION['redirect_after_login'] ?? '../index.php';
                    unset($_SESSION['redirect_after_login']);
                    
                    if ($debug) error_log("Redirecting to: " . $redirect_url);
                    
                    // For AJAX requests, return JSON
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => 'Đăng nhập thành công!',
                            'redirect' => $redirect_url
                        ]);
                        exit();
                    }
                    
                    header('Location: ' . $redirect_url);
                    exit();
                } else {
                    if ($debug) {
                        if ($user) {
                            error_log("Password verification: FAILED");
                            $testHash = password_hash($password, PASSWORD_DEFAULT);
                            error_log("Test hash: " . $testHash);
                            error_log("Test verify: " . (password_verify($password, $testHash) ? 'OK' : 'FAILED'));
                        }
                    }
                    $_SESSION['flash']['error'] = 'Email hoặc mật khẩu không chính xác!';
                }
            }
        } catch (Exception $e) {
            if ($debug) error_log("Login exception: " . $e->getMessage());
            $_SESSION['flash']['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
    
    if ($debug) error_log("=== LOGIN DEBUG END ===");
}

// Handle URL parameters first
$success_message = '';
$prefill_email = '';

if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success_message = 'Đăng ký thành công! Vui lòng đăng nhập với tài khoản mới.';
}

if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $prefill_email = $_GET['email'];
}

// Check remember me cookie
if (isset($_COOKIE['teddy_remember']) && !isLoggedIn()) {
    $cookie_data = base64_decode($_COOKIE['teddy_remember']);
    if (strpos($cookie_data, '|') !== false) {
        list($email, $timestamp) = explode('|', $cookie_data);
        
        // Check if cookie is still valid (30 days)
        if (time() - $timestamp < 86400 * 30) {
            if (isset($db)) {
                $user = $db->fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'] ?? 'user';
                    redirect('../index.php');
                }
            }
        }
    }
}

// SEO Meta
$page_title = 'Đăng nhập - ' . (defined('SITE_NAME') ? SITE_NAME : 'Teddy Bear Paradise');
$page_description = 'Đăng nhập vào tài khoản Teddy Bear Paradise để trải nghiệm mua sắm tuyệt vời.';

// Include header
include '../includes/header.php';
?>

<!-- Link to Modern CSS -->
<link rel="stylesheet" href="../css/auth-modern.css">

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <span class="auth-icon">🐻</span>
            <h2 class="auth-title">Chào mừng trở lại!</h2>
            <p class="auth-subtitle">Đăng nhập để tiếp tục hành trình mua sắm</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            <!-- Success Message from Registration -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <span>✅</span>
                    <div><?php echo htmlspecialchars($success_message); ?></div>
                </div>
            <?php endif; ?>

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'error' : $type; ?>">
                        <span><?php echo $type === 'success' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️'); ?></span>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="auth-form" id="loginForm">
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <div class="form-input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input type="email" 
                               class="form-input" 
                               id="email" 
                               name="email"
                               placeholder="Nhập email của bạn"
                               value="<?php echo !empty($prefill_email) ? htmlspecialchars($prefill_email) : ''; ?>"
                               required>
                    </div>
                    <div class="form-feedback error" id="emailError"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="form-input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" 
                               class="form-input" 
                               id="password" 
                               name="password"
                               placeholder="Nhập mật khẩu"
                               required>
                    </div>
                    <div class="form-feedback error" id="passwordError"></div>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-options">
                    <div class="form-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="#" onclick="showForgotPasswordModal(); return false;" class="forgot-link">
                        Quên mật khẩu?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-auth btn-primary-auth">
                    <span>🔐</span> Đăng nhập
                </button>

            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <!-- Social Login -->
            <div class="social-buttons">
                <button type="button" class="social-button" onclick="loginWithGoogle()">
                    <span>🔴</span>
                    <span>Google</span>
                </button>
                <button type="button" class="social-button" onclick="loginWithFacebook()">
                    <span>📘</span>
                    <span>Facebook</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay 🎉</a></p>
            <p style="margin-top: 12px; font-size: 12px;">
                <a href="../admin/admin-login.php" style="color: var(--secondary-color); font-weight: 600;">👑 Đăng nhập Admin</a>
            </p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotPasswordModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; padding: 30px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
        <h5 style="font-weight: 700; margin-bottom: 15px;">🔑 Quên mật khẩu?</h5>
        <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Nhập email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu.</p>
        <input type="email" id="resetEmail" placeholder="Nhập email" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; font-size: 14px;">
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('forgotPasswordModal').style.display='none'" style="flex: 1; padding: 10px; border: 2px solid #e0e0e0; background: white; border-radius: 8px; cursor: pointer; font-weight: 600;">Hủy</button>
            <button onclick="sendResetEmail()" style="flex: 1; padding: 10px; background: var(--primary-color); color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Gửi</button>
        </div>
    </div>
</div>

<!-- Simplified JavaScript - NO CONFLICTS -->
<script>
// Simple demo login function
function quickLogin(type) {
    const accounts = {
        'user': { email: 'user@demo.com', password: '123456' },
        'admin': { email: 'admin@teddyshop.com', password: 'admin123' }
    };
    
    const account = accounts[type];
    if (account) {
        document.getElementById('email').value = account.email;
        document.getElementById('password').value = account.password;
        
        // Show notification
        showSimpleNotification(`✨ Đã điền thông tin ${type}! Nhấn "Đăng nhập" để tiếp tục.`);
    }
}

// Simple notification
function showSimpleNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: #17a2b8; color: white; padding: 15px 20px;
        border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%); opacity: 0; transition: all 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Simple social login placeholders
function loginWithGoogle() {
    showSimpleNotification('🚧 Đăng nhập với Google đang được phát triển!');
}

function loginWithFacebook() {
    showSimpleNotification('🚧 Đăng nhập với Facebook đang được phát triển!');
}

function showForgotPasswordModal() {
    showSimpleNotification('🚧 Tính năng quên mật khẩu đang được phát triển!');
}

function sendResetEmail() {
    showSimpleNotification('📧 Link đặt lại mật khẩu đã được gửi!');
}

console.log('🐻 Login page loaded successfully!');
</script>

<?php 
// Include footer with error handling
$footer_included = false;
if (file_exists('../includes/footer.php')) {
    try {
        include '../includes/footer.php';
        $footer_included = true;
    } catch (Exception $e) {
        $footer_included = false;
    }
}

if (!$footer_included) {
    echo '</body></html>';
}
?>