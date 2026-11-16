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

<!-- Giao diện Đăng nhập sạch sẽ, đồng bộ tone hồng pastel -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <!-- Header Card -->
                <div class="text-center p-4 bg-pink-light border-bottom">
                    <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center shadow-xs mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-heart text-pink fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Chào mừng trở lại!</h4>
                    <p class="text-muted small mb-0">Đăng nhập để tiếp tục mua sắm cùng Teddy Bear Paradise</p>
                </div>

                <!-- Body Form -->
                <div class="card-body p-4">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success rounded-3 small mb-3">
                            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['flash'])): ?>
                        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> rounded-3 small mb-3">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endforeach; ?>
                        <?php unset($_SESSION['flash']); ?>
                    <?php endif; ?>

                    <form method="POST" id="loginForm">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-secondary small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 ps-0" 
                                       id="email" 
                                       name="email"
                                       placeholder="email@example.com"
                                       value="<?php echo !empty($prefill_email) ? htmlspecialchars($prefill_email) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0 ps-0" 
                                       id="password" 
                                       name="password"
                                       placeholder="Nhập mật khẩu"
                                       required>
                            </div>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-4 small">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label text-secondary" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                            <a href="#" onclick="showForgotPasswordModal(); return false;" class="text-pink fw-semibold">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="text-center my-3 position-relative">
                        <hr class="text-muted opacity-25">
                        <span class="bg-white px-2 text-muted small position-absolute top-50 start-50 translate-middle">hoặc</span>
                    </div>

                    <!-- Social Login -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <button type="button" class="btn btn-light border rounded-pill w-100 btn-sm py-2" onclick="loginWithGoogle()">
                                <i class="fab fa-google text-danger me-1"></i> Google
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-light border rounded-pill w-100 btn-sm py-2" onclick="loginWithFacebook()">
                                <i class="fab fa-facebook-f text-primary me-1"></i> Facebook
                            </button>
                        </div>
                    </div>

                    <!-- Footer Links -->
                    <div class="text-center pt-3 border-top small">
                        <p class="mb-2 text-muted">
                            Chưa có tài khoản? <a href="register.php" class="text-pink fw-bold">Đăng ký ngay</a>
                        </p>
                        <a href="../admin/admin-login.php" class="text-secondary text-decoration-none">
                            <i class="fas fa-user-shield me-1"></i> Đăng nhập Quản trị viên
                        </a>
                    </div>
                </div>
            </div>
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