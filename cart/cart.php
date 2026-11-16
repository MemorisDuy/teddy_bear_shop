<?php
/**
 * Shopping Cart Page Redirect
 * Tự động chuyển hướng về trang giỏ hàng chính thức cart/index.php
 */
require_once '../config/config.php';
header("Location: " . SITE_URL . "/cart/index.php");
exit;
