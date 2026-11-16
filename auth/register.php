<?php
/**
 * Registration Page - Đăng ký tài khoản 🎉
 * Fixed version with proper database handling
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Helper functions
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

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $agree_terms = isset($_POST['agree_terms']);
    
    if ($debug) {
        error_log("=== REGISTER DEBUG START ===");
        error_log("Full name: " . $full_name);
        error_log("Email: " . $email);
        error_log("Password length: " . strlen($password));
        error_log("Phone: " . $phone);
        error_log("Agree terms: " . ($agree_terms ? 'yes' : 'no'));
    }
    
    // Validation
    $errors = [];
    
    if (empty($full_name) || strlen($full_name) < 2) {
        $errors[] = 'Họ tên phải có ít nhất 2 ký tự!';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ!';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp!';
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ!';
    }
    
    if (!$agree_terms) {
        $errors[] = 'Bạn phải đồng ý với điều khoản sử dụng!';
    }
    
    // Register user if no errors
    if (empty($errors)) {
        try {
            // Luôn dùng $db từ config/config.php
            // Check if email exists
            $existingUser = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            
            if ($existingUser) {
                $errors[] = 'Email này đã được sử dụng!';
                if ($debug) error_log("Email already exists: " . $email);
            } else {
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $username = explode('@', $email)[0]; // Create username from email
                
                if ($debug) {
                    error_log("Creating user with hashed password: " . substr($hashed_password, 0, 20) . "...");
                }
                
                $sql = "INSERT INTO users (username, full_name, email, password, phone, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";
                $params = [$username, $full_name, $email, $hashed_password, $phone];
                
                $userId = $db->insert($sql, $params);
                
                if ($userId) {
                    if ($debug) error_log("User created successfully with ID: " . $userId);
                    
                    $_SESSION['flash']['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    
                    // Nếu đăng nhập tự động sau đăng ký, merge session cart vào database
                    // if (function_exists('mergeSessionCartToDatabase')) {
                    //     mergeSessionCartToDatabase($userId);
                    // }
                    // Redirect to login page with success message
                    header('Location: login.php?registered=success&email=' . urlencode($email));
                    exit();
                } else {
                    $errors[] = 'Có lỗi xảy ra khi tạo tài khoản!';
                    if ($debug) error_log("Failed to create user");
                }
            }
            
        } catch (Exception $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            if ($debug) error_log("Registration exception: " . $e->getMessage());
        }
    }
    
    // Set error messages
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $_SESSION['flash']['error'] = $error;
            break; // Show only first error
        }
        if ($debug) error_log("Registration errors: " . implode(', ', $errors));
    }
    
    if ($debug) error_log("=== REGISTER DEBUG END ===");
}

// SEO Meta
$page_title = 'Đăng ký tài khoản - ' . (defined('SITE_NAME') ? SITE_NAME : 'Teddy Bear Paradise');
$page_description = 'Tạo tài khoản mới tại Teddy Bear Paradise để trải nghiệm mua sắm tuyệt vời.';

// Include header
include '../includes/header.php';
?>

<!-- Link to external CSS -->
<link rel="stylesheet" href="../css/auth-modern.css">

<div class="auth-wrapper">
    
    <div class="auth-container">
        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="auth-alert auth-alert-<?php echo $type === 'error' ? 'danger' : $type; ?>" role="alert">
                    <span style="font-size: 1.2rem; margin-right: 8px;"><?php echo $type === 'success' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️'); ?></span>
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button type="button" class="auth-alert-close" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="auth-card">
            <!-- Header with gradient -->
            <div class="auth-header">
                <div class="auth-icon">🎉</div>
                <h1 class="auth-title">Tham gia cùng tôi</h1>
                <p class="auth-subtitle">Tạo tài khoản để khám phá kho gấu bông dễ thương</p>
            </div>

            <!-- Body with form -->
            <div class="auth-body">
                <form method="POST" class="auth-form" id="registerForm" novalidate>
                    
                    <!-- Full Name -->
                    <div class="form-input-group">
                        <label for="fullName" class="form-input-label">Họ và tên</label>
                        <div class="form-input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" 
                                   id="fullName" 
                                   name="full_name"
                                   class="form-input"
                                   placeholder="Tên đầy đủ của bạn"
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                   required 
                                   minlength="2">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-input-group">
                        <label for="email" class="form-input-label">Email</label>
                        <div class="form-input-wrapper">
                            <span class="input-icon">✉️</span>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   class="form-input"
                                   placeholder="Email của bạn"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="form-input-group">
                        <label for="phone" class="form-input-label">Số điện thoại (tùy chọn)</label>
                        <div class="form-input-wrapper">
                            <span class="input-icon">📞</span>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone"
                                   class="form-input"
                                   placeholder="0xxxxxxxxx"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   pattern="[0-9]{10,11}">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-input-group">
                        <label for="password" class="form-input-label">Mật khẩu</label>
                        <div class="form-input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" 
                                   id="password" 
                                   name="password"
                                   class="form-input"
                                   placeholder="Tối thiểu 6 ký tự"
                                   required 
                                   minlength="6">
                            <button type="button" class="toggle-password" onclick="togglePassword('password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <span style="font-size: 1rem; color: #666;">👁️</span>
                            </button>
                        </div>
                    <!-- Confirm Password -->
                    <div class="form-input-group">
                        <label for="confirmPassword" class="form-input-label">Xác nhận mật khẩu</label>
                        <div class="form-input-wrapper">
                            <span class="input-icon">🔐</span>
                            <input type="password" 
                                   id="confirmPassword" 
                                   name="confirm_password"
                                   class="form-input"
                                   placeholder="Nhập lại mật khẩu"
                                   required 
                                   minlength="6">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <span style="font-size: 1rem; color: #666;">👁️</span>
                            </button>
                        </div>
                    </div>

                    <!-- Terms & Privacy -->
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="agreeTerms" 
                               name="agree_terms"
                               class="form-checkbox"
                               required>
                        <label for="agreeTerms" class="form-checkbox-label">
                            Tôi đồng ý với <a href="javascript:void(0)" class="auth-link">Điều khoản sử dụng</a> và <a href="javascript:void(0)" class="auth-link">Chính sách bảo mật</a>
                        </label>
                    </div>

                    <!-- Newsletter -->
                    <div class="form-checkbox-group">
                        <input type="checkbox" 
                               id="newsletter" 
                               name="subscribe_newsletter"
                               class="form-checkbox"
                               checked>
                        <label for="newsletter" class="form-checkbox-label">
                            📧 Nhận thông tin khuyến mãi và sản phẩm mới
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary-auth">
                        <span>Tạo tài khoản</span>
                        <span style="font-size: 1rem; margin-left: 8px;">➡️</span>
                    </button>

                </form>

                <!-- Divider -->
                <div class="auth-divider">
                    <span>hoặc</span>
                </div>

                <!-- Social Login -->
                <div class="auth-social">
                    <button type="button" class="btn-social btn-google" onclick="alert('Google registration coming soon')">
                        <span style="font-size: 1.2rem; margin-right: 8px;">🔴</span>
                        <span>Google</span>
                    </button>
                    <button type="button" class="btn-social btn-facebook" onclick="alert('Facebook registration coming soon')">
                        <span style="font-size: 1.2rem; margin-right: 8px;">📘</span>
                        <span>Facebook</span>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p>Đã có tài khoản? <a href="login.php" class="auth-link auth-link-strong">Đăng nhập ngay</a></p>
            </div>
        </div>

        <!-- Benefits Grid -->
        <div class="benefits">
            <div class="benefit-card">
                <div class="benefit-icon">🎁</div>
                <div class="benefit-text">
                    <h4>Ưu đãi đặc biệt</h4>
                    <p>Giảm 10% cho đơn đầu</p>
                </div>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">❤️</div>
                <div class="benefit-text">
                    <h4>Danh sách yêu thích</h4>
                    <p>Lưu sản phẩm yêu thích</p>
                </div>
            </div>
            <div class="benefit-card">
                <div class="benefit-icon">📦</div>
                <div class="benefit-text">
                    <h4>Theo dõi đơn hàng</h4>
                    <p>Quản lý dễ dàng</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation & Password Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate password match
            const pwd = document.getElementById('password').value;
            const confirmPwd = document.getElementById('confirmPassword').value;
            const terms = document.getElementById('agreeTerms').checked;
            
            if (pwd !== confirmPwd) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                document.getElementById('confirmPassword').focus();
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Vui lòng đồng ý với điều khoản sử dụng');
                return false;
            }
        });
    }
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const btn = event.target.closest('.toggle-password');
    
    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = '<span style="font-size: 1rem; color: #666;">🙈</span>';
    } else {
        field.type = 'password';
        btn.innerHTML = '<span style="font-size: 1rem; color: #666;">👁️</span>';
    }
}
</script>

<?php include '../includes/footer.php'; ?>