<?php
session_start();
require_once "../config/config.php";

echo "<h2>🧪 Test Coupon API</h2>";

try {
    // Test 1: Database connection
    echo "✅ Database connected<br>";
    
    // Test 2: Query coupon
    $coupon = $db->fetch("SELECT * FROM coupons WHERE code = 'HELLO2025'");
    if ($coupon) {
        echo "✅ Coupon found: " . json_encode($coupon) . "<br>";
    } else {
        echo "❌ Coupon not found<br>";
    }
    
    // Test 3: Case insensitive
    $coupon2 = $db->fetch("SELECT * FROM coupons WHERE UPPER(code) = UPPER('hello2025')");
    if ($coupon2) {
        echo "✅ Case insensitive works<br>";
    }
    
    // Test 4: Simulate apply
    $_SESSION['applied_coupon'] = [
        'code' => 'HELLO2025',
        'discount' => 50000
    ];
    echo "✅ Session test OK<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 