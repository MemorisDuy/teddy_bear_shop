<?php
require_once 'config/config.php';

echo "<h1>Test 3 Reproduction</h1>";

// TEST 3 CODE - EXACT COPY
echo "<div><strong>Running INSERT...</strong></div>";

$db->query(
    "INSERT INTO custom_orders (customer_name, customer_email, customer_phone, search_keyword, description, status, created_at)
     VALUES (?, ?, ?, ?, ?, 'new', NOW())",
    ['Test2', 'test2@test.com', '0908222222', 'keyword2', 'desc2']
);

echo "<div style='color: green;'><strong>✅ Parameterized query executed</strong></div>";

// Check again
echo "<div><strong>Running SELECT...</strong></div>";

$result2 = $db->fetch("SELECT * FROM custom_orders WHERE customer_email = 'test2@test.com' ORDER BY id DESC LIMIT 1");

if ($result2) {
    echo "<div style='color: green;'><strong>✅ Parameterized data found!<br>";
    echo "ID: " . $result2['id'] . "<br>";
    echo "Name: " . $result2['customer_name'] . "<br>";
    echo "</strong></div>";
    
    // Clean up
    $db->query("DELETE FROM custom_orders WHERE id = " . $result2['id']);
    echo "<div style='color: green;'><strong>✅ Cleaned up</strong></div>";
} else {
    echo "<div style='color: red;'><strong>❌ No parameterized data found</strong></div>";
}
?>
