<?php
/**
 * Script tự động giải nén mã nguồn Teddy Bear Shop trên hosting
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$zipFile = __DIR__ . '/update_code_only.zip';
if (!file_exists($zipFile)) {
    $zipFile = __DIR__ . '/upload_to_hosting.zip';
}

if (!file_exists($zipFile)) {
    die("<h2 style='color:red;'>Lỗi: Không tìm thấy file zip nào trong thư mục!</h2><p>Vui lòng upload file <code>update_code_only.zip</code> hoặc <code>upload_to_hosting.zip</code> vào cùng thư mục với unzip.php.</p>");
}

if (!class_exists('ZipArchive')) {
    die("<h2 style='color:red;'>Lỗi: Hosting không hỗ trợ ZipArchive!</h2>");
}

$zip = new ZipArchive();
$result = $zip->open($zipFile);

if ($result === TRUE) {
    $zip->extractTo(__DIR__);
    $zip->close();
    
    // Tự động xóa file unzip.php để bảo mật
    @unlink(__FILE__);
    
    echo "<div style='font-family: Arial, sans-serif; text-align: center; padding: 50px;'>";
    echo "<h1 style='color: #28a745;'>🎉 GIẢI NÉN THÀNH CÔNG!</h1>";
    echo "<p style='font-size: 18px; color: #555;'>Toàn bộ giao diện và mã nguồn mới đã được cập nhật thành công lên website.</p>";
    echo "<p><a href='index.php' style='display: inline-block; padding: 12px 25px; background: #ff6b96; color: white; text-decoration: none; border-radius: 25px; font-weight: bold;'>👉 Xem trang chủ ngay</a></p>";
    echo "</div>";
} else {
    echo "<h2 style='color:red;'>Lỗi khi mở file zip: Mã lỗi " . $result . "</h2>";
}
