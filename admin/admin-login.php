<?php
// CLEAN VERSION - NO DEBUG
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

$error_message = '';
$success_message = '';

// XỬ LÝ LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $admin = $db->fetch("SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active'", [$username, $username]);
            
            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_login_time'] = time();
                
                $success_message = 'Đăng nhập thành công! Đang chuyển hướng...';
                
                // Redirect với JavaScript để user thấy thông báo thành công
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "dashboard.php";
                    }, 1500);
                </script>';
            } else {
                $error_message = 'Sai tài khoản hoặc mật khẩu!';
            }
        } catch (Exception $e) {
            $error_message = 'Lỗi hệ thống. Vui lòng thử lại!';
        }
    } else {
        $error_message = 'Vui lòng nhập đầy đủ thông tin!';
    }
}

// Kiểm tra đã login chưa
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Teddy Bear Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon"><i class="fas fa-heart"></i></div>
            <h1 class="login-title">Admin Panel</h1>
            <p class="login-subtitle">Hệ thống quản trị Teddy Bear Shop</p>
        </div>

        <div class="login-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger" style="font-size:16px; font-weight:bold; color:#b30000; background:#fff3f3; border:2px solid #ffb3b3; padding:16px; margin-bottom:20px; border-radius:8px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success" style="font-size:16px; font-weight:bold; color:#006600; background:#f0fff0; border:2px solid #90EE90; padding:16px; margin-bottom:20px; border-radius:8px;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label class="form-label">Tên đăng nhập hoặc Email</label>
                    <input type="text" name="username" class="form-control" required 
                           placeholder="admin hoặc admin@teddyshop.com"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required 
                           placeholder="Nhập mật khẩu...">
                </div>

                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="fas fa-lock"></i>
                    <span>Đăng nhập</span>
                </button>
            </form>
        </div>

        <div class="demo-section">
            <h6 style="margin-bottom: 15px; color: #666; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span>ℹ️</span>
                Thông tin đăng nhập
            </h6>
            <div style="background: white; padding: 15px; border-radius: 10px; border: 2px solid #e9ecef; margin-bottom: 15px;">
                <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                    
                </p>
            </div>
            <small style="color: #666; font-size: 12px;">
                <i class="fas fa-lightbulb"></i> Hệ thống sẽ tự động tạo tài khoản admin nếu chưa tồn tại
            </small>
        </div>
    </div>

    <div class="back-link">
        <a href="../index.php">
            <span>⬅️</span> Quay lại trang chủ
        </a>
    </div>
</body>
</html>