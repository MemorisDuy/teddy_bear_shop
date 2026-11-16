<?php
require_once '../config/config.php';
require_once '../config/cart_functions.php';
header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? null;
echo json_encode(['success' => true, 'cart_count' => getCartCount($user_id)]);

?>