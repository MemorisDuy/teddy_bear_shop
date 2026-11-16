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

<!-- Giao diện Đăng ký sạch sẽ, đồng bộ tone hồng pastel -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <?php if (!empty($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show rounded-4 mb-3" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <!-- Header Card -->
                <div class="text-center p-4 bg-pink-light border-bottom">
                    <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center shadow-xs mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-user-plus text-pink fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Tạo tài khoản mới</h4>
                    <p class="text-muted small mb-0">Tham gia cùng Teddy Bear Paradise để nhận nhiều ưu đãi</p>
                </div>

                <!-- Body Form -->
                <div class="card-body p-4">
                    <form method="POST" id="registerForm" novalidate>
                        <!-- Họ và tên -->
                        <div class="mb-3">
                            <label for="fullName" class="form-label fw-semibold text-secondary small">Họ và tên *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       id="fullName" 
                                       name="full_name"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Nguyễn Văn A"
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       required 
                                       minlength="2">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-secondary small">Địa chỉ Email *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       id="email" 
                                       name="email"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="email@example.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Số điện thoại -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold text-secondary small">Số điện thoại (tùy chọn)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="0912345678"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       pattern="[0-9]{10,11}">
                            </div>
                        </div>

                        <!-- Mật khẩu -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       id="password" 
                                       name="password"
                                       class="form-control border-start-0 border-end-0 ps-0"
                                       placeholder="Tối thiểu 6 ký tự"
                                       required 
                                       minlength="6">
                                <button type="button" class="btn btn-light border border-start-0 text-muted" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu -->
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-semibold text-secondary small">Xác nhận mật khẩu *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-pink">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                <input type="password" 
                                       id="confirmPassword" 
                                       name="confirm_password"
                                       class="form-control border-start-0 border-end-0 ps-0"
                                       placeholder="Nhập lại mật khẩu"
                                       required 
                                       minlength="6">
                                <button type="button" class="btn btn-light border border-start-0 text-muted" onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Điều khoản -->
                        <div class="mb-2">
                            <div class="form-check small">
                                <input type="checkbox" id="agreeTerms" name="agree_terms" class="form-check-input" required checked>
                                <label for="agreeTerms" class="form-check-label text-secondary">
                                    Tôi đồng ý với <a href="javascript:void(0)" class="text-pink fw-semibold">Điều khoản sử dụng</a> và <a href="javascript:void(0)" class="text-pink fw-semibold">Chính sách bảo mật</a>
                                </label>
                            </div>
                        </div>

                        <!-- Newsletter -->
                        <div class="mb-4">
                            <div class="form-check small">
                                <input type="checkbox" id="newsletter" name="subscribe_newsletter" class="form-check-input" checked>
                                <label for="newsletter" class="form-check-label text-secondary">
                                    Nhận thông tin khuyến mãi và mẫu gấu bông mới
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                                <i class="fas fa-check-circle me-2"></i> Tạo tài khoản
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
                            <button type="button" class="btn btn-light border rounded-pill w-100 btn-sm py-2" onclick="alert('Tính năng đăng ký Google sắp ra mắt')">
                                <i class="fab fa-google text-danger me-1"></i> Google
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-light border rounded-pill w-100 btn-sm py-2" onclick="alert('Tính năng đăng ký Facebook sắp ra mắt')">
                                <i class="fab fa-facebook-f text-primary me-1"></i> Facebook
                            </button>
                        </div>
                    </div>

                    <!-- Footer Link -->
                    <div class="text-center pt-3 border-top small">
                        <p class="mb-0 text-muted">
                            Đã có tài khoản? <a href="login.php" class="text-pink fw-bold">Đăng nhập ngay</a>
                        </p>
                    </div>
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