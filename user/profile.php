<?php
/**
 * User Profile Page - Thông tin cá nhân 👤
 */

require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('../auth/login.php');
}

// Get user info
$user = getUserById($_SESSION['user_id']);

if (!$user) {
    logoutUser();
    redirect('../auth/login.php');
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $full_name = sanitizeInput($_POST['full_name']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $birthday = sanitizeInput($_POST['birthday']);
        $gender = sanitizeInput($_POST['gender']);
        
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = 'Họ tên không được để trống';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }
        
        if (empty($errors)) {
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, birthday = ?, gender = ?, updated_at = NOW() WHERE id = ?";
            $params = [$full_name, $phone, $address, $birthday ?: null, $gender ?: null, $user['id']];
            
            $updated = $db->execute($sql, $params);
            if (!$updated) {
                // Fallback nếu cột birthday/gender chưa có
                $updated = $db->execute(
                    "UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?",
                    [$full_name, $phone, $address, $user['id']]
                );
            }
            
            if ($updated) {
                $_SESSION['user_name'] = $full_name;
                flashMessage('success', 'Cập nhật thông tin thành công! ✅');
                redirect('profile.php');
            } else {
                flashMessage('error', 'Có lỗi xảy ra, vui lòng thử lại!');
            }
        } else {
            foreach ($errors as $error) {
                flashMessage('error', $error);
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = 'Vui lòng nhập đầy đủ thông tin';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Mật khẩu hiện tại không chính xác';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            
            if ($db->execute($sql, [$hashed_password, $user['id']])) {
                flashMessage('success', 'Đổi mật khẩu thành công! 🔐');
                redirect('profile.php');
            } else {
                flashMessage('error', 'Có lỗi xảy ra, vui lòng thử lại!');
            }
        } else {
            foreach ($errors as $error) {
                flashMessage('error', $error);
            }
        }
    } elseif ($action === 'upload_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploaded_file = uploadImage($_FILES['avatar'], 'avatars');
            
            if ($uploaded_file && !empty($uploaded_file['success']) && !empty($uploaded_file['filename'])) {
                $sql = "UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?";
                
                if ($db->execute($sql, [$uploaded_file['filename'], $user['id']])) {
                    flashMessage('success', 'Cập nhật ảnh đại diện thành công! 📸');
                    redirect('profile.php');
                } else {
                    flashMessage('error', 'Có lỗi xảy ra, vui lòng thử lại!');
                }
            } else {
                flashMessage('error', $uploaded_file['message'] ?? 'File không hợp lệ hoặc quá lớn!');
            }
        }
    } elseif ($action === 'update_notifications') {
        flashMessage('success', 'Đã lưu cài đặt thông báo! 🔔');
        redirect('profile.php');
    }
}

// Get updated user info
$user = getUserById($_SESSION['user_id']);

// Get user statistics
$total_orders = $db->count('orders', 'user_id = ?', [$user['id']]);
$total_spent_result = $db->fetch("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND order_status != 'cancelled'", [$user['id']]);
$total_spent = $total_spent_result['total'] ?: 0;

// Get recent orders
$recent_orders = $db->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3", [$user['id']]);

// SEO Meta
$page_title = 'Thông tin cá nhân';
$page_description = 'Quản lý thông tin cá nhân và cài đặt tài khoản tại Teddy Bear Paradise.';

// Custom CSS
$custom_css = "
    .profile-container {
        padding: 50px 0;
        background: linear-gradient(135deg, #ffeef0 0%, #fff5f5 100%);
        min-height: 80vh;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .profile-container * {
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .profile-sidebar {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .profile-content {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .profile-sidebar h4,
    .profile-sidebar p,
    .profile-sidebar label,
    .profile-sidebar .form-label,
    .profile-content h5,
    .profile-content label,
    .profile-content .form-label,
    .profile-content input,
    .profile-content select,
    .profile-content textarea,
    .profile-content button {
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .avatar-upload {
        position: relative;
        display: inline-block;
    }
    
    .avatar-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        cursor: pointer;
        font-size: 1.5rem;
    }
    
    .avatar-upload:hover .avatar-overlay {
        opacity: 1;
    }
    
    .nav-pills .nav-link {
        border-radius: 15px;
        padding: 12px 20px;
        margin-bottom: 10px;
        color: #666;
        font-weight: 500;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
        transition: all 0.3s ease;
    }
    
    .nav-pills .nav-link:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }
    
    .nav-pills .nav-link.active {
        background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
        color: white;
    }
    
    .stat-card {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .user-info h4 {
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
        font-weight: 600;
        color: #333;
    }
    
    .user-info p {
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
        color: #666;
    }
    
    .recent-order {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #ff6b6b;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
    }
    
    .form-control,
    .form-select {
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 10px 15px;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #ff6b6b;
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
        border: none;
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 500;
        font-family: 'UVN Van', 'Nunito', 'Poppins', Arial, sans-serif !important;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
    }
";

include '../includes/header.php';
?>

<div class="profile-container">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar">
                    <!-- User Info -->
                    <div class="user-info text-center mb-4">
                        <div class="avatar-upload">
                            <img src="../images/<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'teddy-01.jpg'; ?>" 
                                 alt="Avatar" class="avatar-image"
                                 onerror="this.src='../images/teddy-01.jpg'">
                            <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click()">
                                <span style="font-size: 1.5rem;">📷</span>
                            </div>
                            <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                                <input type="hidden" name="action" value="upload_avatar">
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                            </form>
                        </div>
                        <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="user-badges">
                            <span class="badge bg-primary">Thành viên từ <?php echo date('m/Y', strtotime($user['created_at'])); ?></span>
                            <?php if ($total_orders >= 10): ?>
                            <span class="badge bg-warning">VIP Customer</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="user-stats">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_orders; ?></div>
                            <div class="stat-label">Tổng đơn hàng</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(45deg, #ff6b6b, #ff8e8e);">
                            <div class="stat-number"><?php echo formatPrice($total_spent); ?></div>
                            <div class="stat-label">Tổng chi tiêu</div>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#profile-info">
                                <span style="font-size: 1rem; margin-right: 8px;">👤</span> Thông tin cá nhân
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#change-password">
                                <span style="font-size: 1rem; margin-right: 8px;">🔐</span> Đổi mật khẩu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <span style="font-size: 1rem; margin-right: 8px;">📦</span> Đơn hàng của tôi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../pages/products.php">
                                <span style="font-size: 1rem; margin-right: 8px;">❤️</span> Sản phẩm yêu thích
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#notifications">
                                <span style="font-size: 1rem; margin-right: 8px;">🔔</span> Thông báo
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Recent Orders -->
                <?php if (!empty($recent_orders)): ?>
                <div class="profile-sidebar">
                    <h6 class="fw-bold mb-3">📦 Đơn hàng gần đây</h6>
                    <?php foreach ($recent_orders as $order): ?>
                    <div class="recent-order">
                        <div class="d-flex justify-content-between">
                            <strong>#<?php echo $order['order_number']; ?></strong>
                            <span class="badge bg-<?php 
                                echo $order['order_status'] === 'delivered' ? 'success' : 
                                     ($order['order_status'] === 'cancelled' ? 'danger' : 'warning'); 
                            ?>">
                                <?php 
                                $status_text = [
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipping' => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy'
                                ];
                                echo $status_text[$order['order_status']] ?? $order['order_status'];
                                ?>
                            </span>
                        </div>
                        <div class="small text-muted mt-1">
                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?> - 
                            <?php echo formatPrice($order['total_amount']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="orders.php" class="btn btn-outline-primary btn-sm w-100">
                        Xem tất cả đơn hàng
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Content -->
            <div class="col-lg-8">
                <?php if (!empty($_SESSION['flash'])): ?>
                    <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                        <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show mb-3" role="alert">
                            <?php echo htmlspecialchars($msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>
                <div class="profile-content">
                    <div class="tab-content">
                        <!-- Profile Info -->
                        <div class="tab-pane fade show active" id="profile-info">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">👤 Thông tin cá nhân</h5>
                                <span class="text-muted">Cập nhật lần cuối: <?php echo timeAgo($user['updated_at']); ?></span>
                            </div>
                            
                            <form method="POST" class="row">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Họ và tên *</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số điện thoại</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                           pattern="[0-9]{10,11}">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giới tính</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Nam</option>
                                        <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Nữ</option>
                                        <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày sinh</label>
                                    <input type="date" name="birthday" class="form-control" 
                                           value="<?php echo $user['birthday'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Địa chỉ</label>
                                    <textarea name="address" class="form-control" rows="3" 
                                              placeholder="Nhập địa chỉ của bạn..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <span style="font-size: 1rem; margin-right: 8px;">💾</span> Cập nhật thông tin
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="tab-pane fade" id="change-password">
                            <h5 class="fw-bold mb-4">🔐 Đổi mật khẩu</h5>
                            
                            <form method="POST" class="col-md-6">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mật khẩu hiện tại *</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mật khẩu mới *</label>
                                    <input type="password" name="new_password" class="form-control" 
                                           required minlength="6" id="newPassword">
                                    <small class="text-muted">Ít nhất 6 ký tự</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Xác nhận mật khẩu mới *</label>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           required id="confirmPassword">
                                </div>
                                
                                <div class="password-tips mb-3">
                                    <small class="text-muted">
                                        <strong>Mẹo bảo mật:</strong><br>
                                        • Sử dụng cả chữ hoa và chữ thường<br>
                                        • Bao gồm số và ký tự đặc biệt<br>
                                        • Không sử dụng thông tin cá nhân
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <span style="font-size: 1rem; margin-right: 8px;">🔑</span> Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="tab-pane fade" id="notifications">
                            <h5 class="fw-bold mb-4">🔔 Cài đặt thông báo</h5>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="update_notifications">
                                
                                <div class="notification-settings">
                                    <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <strong>Email thông báo đơn hàng</strong>
                                            <br><small class="text-muted">Nhận email khi trạng thái đơn hàng thay đổi</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="email_orders" checked>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <strong>Email khuyến mãi</strong>
                                            <br><small class="text-muted">Nhận thông tin về các chương trình ưu đãi</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="email_promotions" checked>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <strong>SMS thông báo</strong>
                                            <br><small class="text-muted">Nhận SMS khi có cập nhật quan trọng</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="sms_notifications">
                                        </div>
                                    </div>
                                    
                                    <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <strong>Thông báo sinh nhật</strong>
                                            <br><small class="text-muted">Nhận ưu đãi đặc biệt trong ngày sinh nhật</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="birthday_notifications" checked>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <span style="font-size: 1rem; margin-right: 8px;">💾</span> Lưu cài đặt
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>