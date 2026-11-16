<?php
/**
 * User Functions - Các hàm xử lý người dùng
 */

// Kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Kiểm tra admin đăng nhập
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Đăng nhập user
function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Đăng nhập admin
function loginAdmin($username, $password) {
    global $pdo;
    
    try {
        // Kiểm tra trong bảng admins trước
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            // Nếu không có bảng admins, kiểm tra users với role admin
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'admin' AND status = 'active'");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();
        }
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Set admin session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'] ?? $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['name'];
            $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Admin login error: " . $e->getMessage());
        return false;
    }
}

// Lấy thông tin user theo email
function getUserByEmail($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return false;
    }
}

// Lấy thông tin user theo ID
function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get user by ID error: " . $e->getMessage());
        return false;
    }
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Tạo user mới
function createUser($data) {
    global $pdo;
    
    try {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, password, phone, address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);
    } catch (PDOException $e) {
        error_log("Create user error: " . $e->getMessage());
        return false;
    }
}

// Kiểm tra email đã tồn tại
function emailExists($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Email exists check error: " . $e->getMessage());
        return false;
    }
}

// Logout user
function logoutUser() {
    // Clear user session
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
    
    // Remove remember me cookie
    if (isset($_COOKIE['teddy_remember'])) {
        setcookie('teddy_remember', '', time() - 3600, '/');
    }
}

// Logout admin
function logoutAdmin() {
    // Clear admin session
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
}

// Tạo admin mặc định nếu chưa có
function createDefaultAdmin() {
    global $pdo;
    
    try {
        // Kiểm tra có admin nào chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                'Administrator',
                'admin@teddyshop.com',
                $hashedPassword,
                'admin',
                'active'
            ]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Create default admin error: " . $e->getMessage());
        return false;
    }
}
?>