<?php
/**
 * Create Admin User - Run this once to create admin account
 */

require_once 'config/config.php';

echo "<h2>🔧 Create Admin User</h2>";

try {
    // Check if admin table exists
    if (!$db->tableExists('admins')) {
        echo "<p style='color: red;'>❌ Table 'admins' không tồn tại. Vui lòng import database SQL trước.</p>";
        exit;
    }
    
    // Check if admin already exists
    $existing_admin = $db->fetch("SELECT * FROM admins WHERE email = ? OR username = ?", ['admin@teddyshop.com', 'admin']);
    
    if ($existing_admin) {
        echo "<p style='color: orange;'>⚠️ Admin đã tồn tại:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> " . $existing_admin['username'] . "</li>";
        echo "<li><strong>Email:</strong> " . $existing_admin['email'] . "</li>";
        echo "<li><strong>Role:</strong> " . $existing_admin['role'] . "</li>";
        echo "</ul>";
        
        // Update password if needed
        $new_password = password_hash('admin123', PASSWORD_DEFAULT);
        $updated = $db->execute(
            "UPDATE admins SET password = ?, updated_at = NOW() WHERE id = ?",
            [$new_password, $existing_admin['id']]
        );
        
        if ($updated) {
            echo "<p style='color: green;'>✅ Mật khẩu đã được reset thành 'admin123'</p>";
        }
        
    } else {
        // Create new admin
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $admin_id = $db->insert(
            "INSERT INTO admins (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            ['admin', 'admin@teddyshop.com', $hashed_password, 'Super Administrator', 'super_admin', 'active']
        );
        
        if ($admin_id) {
            echo "<p style='color: green;'>✅ Admin đã được tạo thành công!</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $admin_id . "</li>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Email:</strong> admin@teddyshop.com</li>";
            echo "<li><strong>Password:</strong> admin123</li>";
            echo "<li><strong>Role:</strong> super_admin</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Không thể tạo admin!</p>";
        }
    }
    
    // Also create a demo admin
    $demo_admin = $db->fetch("SELECT * FROM admins WHERE username = ?", ['demo_admin']);
    
    if (!$demo_admin) {
        $demo_password = password_hash('demo123', PASSWORD_DEFAULT);
        
        $demo_id = $db->insert(
            "INSERT INTO admins (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            ['demo_admin', 'demo@teddyshop.com', $demo_password, 'Demo Administrator', 'admin', 'active']
        );
        
        if ($demo_id) {
            echo "<p style='color: blue;'>✅ Demo admin cũng đã được tạo!</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> demo_admin</li>";
            echo "<li><strong>Email:</strong> demo@teddyshop.com</li>";
            echo "<li><strong>Password:</strong> demo123</li>";
            echo "</ul>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🚀 Hướng dẫn đăng nhập:</h3>";
    echo "<ol>";
    echo "<li>Vào <a href='admin/admin-login.php' target='_blank'>admin/admin-login.php</a></li>";
    echo "<li>Nhập thông tin:</li>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@teddyshop.com</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    echo "<li>Hoặc click nút 'Super Admin' để tự động điền</li>";
    echo "<li>Nhấn 'ĐĂNG NHẬP'</li>";
    echo "</ol>";
    
    echo "<p><a href='admin/admin-login.php' class='btn btn-primary'>🔗 Đăng nhập Admin ngay</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Add some basic styling
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
    .btn:hover { background: #0056b3; }
    ul, ol { margin: 10px 0; }
    hr { margin: 30px 0; }
</style>";
?>