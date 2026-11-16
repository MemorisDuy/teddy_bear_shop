<?php
/**
 * Debug API - Kiểm tra chi tiết lỗi
 */

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Debug Custom Order API</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .pass { border-left-color: #4CAF50; }
        .fail { border-left-color: #f44336; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Debug Custom Order API</h1>";

// Test 1: Check Config
echo "<div class='test'><strong>Test 1: Config File</strong><br>";
if (file_exists('../config/config.php')) {
    echo "<span style='color: green;'>✅ config.php exists</span><br>";
} else {
    echo "<span style='color: red;'>❌ config.php NOT found</span><br>";
}
echo "</div>";

// Test 2: Check if can include
echo "<div class='test'><strong>Test 2: Include Config</strong><br>";
try {
    require_once '../config/config.php';
    echo "<span style='color: green;'>✅ config.php loaded successfully</span><br>";
    echo "SITE_URL: " . SITE_URL . "<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Error loading config: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 3: Check Database Connection
echo "<div class='test'><strong>Test 3: Database Connection</strong><br>";
try {
    if (!isset($db)) {
        echo "<span style='color: red;'>❌ \$db not initialized</span><br>";
    } else {
        $result = $db->fetch("SELECT 1 as test");
        if ($result) {
            echo "<span style='color: green;'>✅ Database connected</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 4: Check if table exists
echo "<div class='test'><strong>Test 4: Check custom_orders Table</strong><br>";
try {
    $tables = $db->fetchAll("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
    $table_names = array_column($tables, 'TABLE_NAME');
    
    if (in_array('custom_orders', $table_names)) {
        echo "<span style='color: green;'>✅ custom_orders table EXISTS</span><br>";
    } else {
        echo "<span style='color: red;'>❌ custom_orders table NOT FOUND</span><br>";
        echo "Available tables: " . implode(", ", $table_names) . "<br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Error checking table: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 5: Check Email Functions
echo "<div class='test'><strong>Test 5: Email Functions</strong><br>";
try {
    require_once '../includes/email_functions.php';
    
    if (function_exists('sendCustomOrderConfirmationToCustomer')) {
        echo "<span style='color: green;'>✅ sendCustomOrderConfirmationToCustomer exists</span><br>";
    } else {
        echo "<span style='color: red;'>❌ sendCustomOrderConfirmationToCustomer NOT found</span><br>";
    }
    
    if (function_exists('sendCustomOrderNotificationToShop')) {
        echo "<span style='color: green;'>✅ sendCustomOrderNotificationToShop exists</span><br>";
    } else {
        echo "<span style='color: red;'>❌ sendCustomOrderNotificationToShop NOT found</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Error loading email functions: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 6: Test INSERT
echo "<div class='test'><strong>Test 6: Test INSERT into custom_orders</strong><br>";
try {
    $test_result = $db->query(
        "INSERT INTO custom_orders (customer_name, customer_email, customer_phone, search_keyword, description, status, created_at)
         VALUES (?, ?, ?, ?, ?, 'new', NOW())",
        ['Test User', 'test@example.com', '0908123456', 'test keyword', 'test description']
    );
    
    $last_id = $db->getLastInsertId();
    if ($last_id > 0) {
        echo "<span style='color: green;'>✅ INSERT successful! Last ID: " . $last_id . "</span><br>";
        
        // Clean up test data
        $db->query("DELETE FROM custom_orders WHERE id = ?", [$last_id]);
        echo "<span style='color: green;'>✅ Test data deleted</span><br>";
    } else {
        echo "<span style='color: red;'>❌ INSERT failed</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ INSERT Error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Test 7: Upload folder
echo "<div class='test'><strong>Test 7: Upload Folder</strong><br>";
$upload_dir = '../images/custom-orders';
if (is_dir($upload_dir)) {
    echo "<span style='color: green;'>✅ Folder exists: " . realpath($upload_dir) . "</span><br>";
    if (is_writable($upload_dir)) {
        echo "<span style='color: green;'>✅ Folder is writable</span><br>";
    } else {
        echo "<span style='color: red;'>❌ Folder NOT writable</span><br>";
    }
} else {
    echo "<span style='color: red;'>❌ Folder does NOT exist: " . $upload_dir . "</span><br>";
    echo "<span style='color: orange;'>ℹ️ Attempting to create...</span><br>";
    if (@mkdir($upload_dir, 0777, true)) {
        echo "<span style='color: green;'>✅ Folder created successfully</span><br>";
    } else {
        echo "<span style='color: red;'>❌ Failed to create folder</span><br>";
    }
}
echo "</div>";

echo "
    <hr>
    <p>If all tests pass, the API should work. If any test fails, that's the issue!</p>
</body>
</html>";
?>
