<?php
/**
 * Auto Upgrade Current User to Admin
 */

require_once '../config/config.php';

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$success = false;
$error = '';

// Nếu đã là admin rồi thì redirect
if (function_exists('isAdmin') && isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

// Xử lý upgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade'])) {
    try {
        $user_id = $_SESSION['user_id'];
        // Cập nhật role trong bảng users
        $db->execute("UPDATE users SET role = 'admin' WHERE id = ?", [$user_id]);
        // Tạo admin record
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);
        if ($user) {
            // Kiểm tra admin đã tồn tại chưa
            $admin_exists = $db->fetch("SELECT COUNT(*) as count FROM admins WHERE email = ?", [$user['email']]);
            if ($admin_exists['count'] == 0) {
                $admin_id = $db->insert(
                    "INSERT INTO admins (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())",
                    [
                        $user['username'] ?? explode('@', $user['email'])[0],
                        $user['email'],
                        $user['password'],
                        $user['full_name'],
                        'super_admin'
                    ]
                );
                if ($admin_id) {
                    // Cập nhật session
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin_id;
                    $_SESSION['admin_username'] = $user['username'] ?? explode('@', $user['email'])[0];
                    $_SESSION['admin_name'] = $user['full_name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role'] = 'super_admin';
                    $success = true;
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Admin - Teddy Bear Shop</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .upgrade-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .upgrade-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .upgrade-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        .upgrade-text {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="upgrade-container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>🎉 Upgrade thành công!</h3>
                <p>Bạn đã trở thành Admin. Đang chuyển hướng...</p>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            </script>
        <?php else: ?>
            <div class="upgrade-icon">👑</div>
            <h1 class="upgrade-title">Upgrade to Admin</h1>
            <p class="upgrade-text">
                Bạn hiện đang đăng nhập với tài khoản thường. 
                Để truy cập Admin Panel, bạn cần nâng cấp quyền admin.
            </p>
            
            <div class="user-info">
                <strong>Thông tin tài khoản hiện tại:</strong><br>
                <strong>Tên:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?><br>
                <strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['user_role'] ?? ''); ?>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Lỗi:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <button type="submit" name="upgrade" class="btn btn-primary">
                    🚀 Nâng cấp thành Admin
                </button>
            </form>
            
            <a href="../index.php" class="btn btn-secondary">
                ← Về trang chủ
            </a>
        <?php endif; ?>
    </div>
</body>
</html> 