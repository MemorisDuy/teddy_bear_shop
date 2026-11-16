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

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4 bg-white">
                <!-- User Info -->
                <div class="avatar-upload position-relative d-inline-block mb-3">
                    <img src="../images/<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'teddy-01.jpg'; ?>" 
                         alt="Avatar" class="rounded-circle border shadow-sm"
                         style="width: 110px; height: 110px; object-fit: cover;"
                         onerror="this.src='../images/teddy-01.jpg'">
                    <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2 shadow-sm" onclick="document.getElementById('avatarInput').click()" title="Đổi ảnh đại diện">
                        <i class="fas fa-camera"></i>
                    </button>
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" name="action" value="upload_avatar">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                    </form>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                <p class="text-muted small mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="mb-3">
                    <span class="badge bg-pink-light text-pink rounded-pill px-3 py-1">Thành viên từ <?php echo date('m/Y', strtotime($user['created_at'])); ?></span>
                </div>
                
                <!-- Thống kê nhanh -->
                <div class="row g-2 text-center pt-3 border-top">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded-3">
                            <span class="fs-5 fw-bold text-dark d-block"><?php echo $total_orders; ?></span>
                            <small class="text-muted" style="font-size: 0.75rem;">Đơn hàng</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded-3">
                            <span class="fs-6 fw-bold text-danger d-block text-truncate"><?php echo formatPrice($total_spent); ?></span>
                            <small class="text-muted" style="font-size: 0.75rem;">Đã chi tiêu</small>
                        </div>
                    </div>
                </div>
                
                <!-- Menu điều hướng -->
                <ul class="nav nav-pills flex-column gap-2 mt-4 text-start">
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" href="#profile-info">
                            <i class="fas fa-user me-2"></i> Thông tin cá nhân
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" href="#change-password">
                            <i class="fas fa-lock me-2"></i> Đổi mật khẩu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 fw-semibold" href="orders.php">
                            <i class="fas fa-box me-2"></i> Đơn hàng của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 fw-semibold" href="../pages/products.php">
                            <i class="fas fa-shopping-bag me-2"></i> Tiếp tục mua sắm
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Đơn hàng gần đây -->
            <?php if (!empty($recent_orders)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-history text-pink me-2"></i> Đơn hàng gần đây</h6>
                <?php foreach ($recent_orders as $order): ?>
                <div class="p-3 bg-light rounded-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">#<?php echo $order['order_number']; ?></span>
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
                    <div class="d-flex justify-content-between text-muted small">
                        <span><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                        <span class="fw-semibold text-danger"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="orders.php" class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-2">
                    Xem tất cả đơn hàng
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Nội dung chính -->
        <div class="col-lg-8">
            <?php if (!empty($_SESSION['flash'])): ?>
                <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
                    <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show rounded-4 mb-3" role="alert">
                        <?php echo htmlspecialchars($msg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="tab-content">
                    <!-- Tab thông tin cá nhân -->
                    <div class="tab-pane fade show active" id="profile-info">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-user-edit text-pink me-2"></i> Thông tin tài khoản</h5>
                            <small class="text-muted">Cập nhật: <?php echo timeAgo($user['updated_at']); ?></small>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Họ và tên *</label>
                                    <input type="text" name="full_name" class="form-control rounded-3" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Địa chỉ Email</label>
                                    <input type="email" class="form-control rounded-3 bg-light" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Số điện thoại</label>
                                    <input type="tel" name="phone" class="form-control rounded-3" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                           pattern="[0-9]{10,11}">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Giới tính</label>
                                    <select name="gender" class="form-select rounded-3">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Nam</option>
                                        <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Nữ</option>
                                        <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Ngày sinh</label>
                                    <input type="date" name="birthday" class="form-control rounded-3" 
                                           value="<?php echo $user['birthday'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Tên đăng nhập</label>
                                    <input type="text" class="form-control rounded-3 bg-light" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">Địa chỉ nhận hàng</label>
                                    <textarea name="address" class="form-control rounded-3" rows="3" 
                                              placeholder="Nhập địa chỉ mặc định..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                                        <i class="fas fa-save me-2"></i> Lưu thay đổi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab đổi mật khẩu -->
                    <div class="tab-pane fade" id="change-password">
                        <div class="mb-4 pb-2 border-bottom">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-key text-pink me-2"></i> Đổi mật khẩu</h5>
                        </div>
                        
                        <form method="POST" class="col-md-8">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary small">Mật khẩu hiện tại *</label>
                                <input type="password" name="current_password" class="form-control rounded-3" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary small">Mật khẩu mới * (tối thiểu 6 ký tự)</label>
                                <input type="password" name="new_password" class="form-control rounded-3" 
                                       required minlength="6" id="newPassword">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-secondary small">Xác nhận mật khẩu mới *</label>
                                <input type="password" name="confirm_password" class="form-control rounded-3" 
                                       required id="confirmPassword">
                            </div>
                            
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                                <i class="fas fa-shield-alt me-2"></i> Cập nhật mật khẩu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>