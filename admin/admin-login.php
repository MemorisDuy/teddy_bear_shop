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
    <title>Đăng nhập Quản trị - Teddy Bear Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', 'Nunito', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffe4ec 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1e293b;
        }
        .admin-login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 20px;
            border: 1px solid #ffe4ec;
            box-shadow: 0 12px 36px rgba(255, 107, 150, 0.14);
            overflow: hidden;
            background: #ffffff;
        }
        .admin-login-header {
            background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .admin-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 24px;
        }
        .btn-admin-login {
            background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            border-radius: 999px;
            padding: 11px 24px;
            transition: all 0.25s ease;
        }
        .btn-admin-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 107, 150, 0.35);
            color: #ffffff;
        }
        .form-control:focus {
            border-color: #ff6b96;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 150, 0.2);
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div class="admin-login-header">
            <div class="admin-icon-circle">
                <i class="fas fa-user-shield"></i>
            </div>
            <h4 class="fw-bold mb-1">Admin Panel</h4>
            <p class="small mb-0 opacity-90">Hệ thống quản trị Teddy Bear Paradise</p>
        </div>

        <div class="p-4">
            <?php if ($error_message): ?>
                <div class="alert alert-danger rounded-3 small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success rounded-3 small mb-3">
                    <i class="fas fa-check-circle me-1"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">Tên đăng nhập hoặc Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" required 
                               placeholder="admin hoặc admin@teddyshop.com"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" required 
                               placeholder="Nhập mật khẩu...">
                    </div>
                </div>

                <div class="form-check mb-4 small">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label text-secondary">Ghi nhớ đăng nhập</label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-admin-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                    </button>
                </div>
            </form>

            <div class="text-center pt-3 border-top">
                <a href="../index.php" class="text-decoration-none small fw-semibold" style="color: #ff6b96;">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>