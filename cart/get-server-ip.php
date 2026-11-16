<?php
/**
 * Get Server IP - Lấy IP của máy chủ
 * Dùng để tạo URL cho QR code
 */

// Hàm lấy IP của máy tính
function getServerIP() {
    // Cách 1: Từ $_SERVER
    $ip = $_SERVER['SERVER_ADDR'] ?? '';
    
    // Cách 2: Từ hostname
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
        $hostname = gethostname();
        $ip = gethostbyname($hostname);
        
        // Nếu vẫn là localhost, thử lấy từ network interfaces
        if ($ip === $hostname || $ip === '127.0.0.1') {
            // Windows: ipconfig
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = @shell_exec('ipconfig');
                if ($output) {
                    // Tìm IPv4 Address
                    if (preg_match('/IPv4 Address[\.\s]+:\s*(\d+\.\d+\.\d+\.\d+)/i', $output, $matches)) {
                        $ip = $matches[1];
                    }
                }
            } else {
                // Linux/Mac: ifconfig hoặc ip
                $output = @shell_exec('hostname -I 2>/dev/null || ifconfig | grep "inet " | grep -v 127.0.0.1 | head -1 | awk \'{print $2}\'');
                if ($output) {
                    $ip = trim($output);
                    $ip = explode(' ', $ip)[0];
                    $ip = explode("\n", $ip)[0];
                }
            }
        }
    }
    
    // Filter: chỉ lấy IP private (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return $ip; // Là IP private hoặc local
    }
    
    // Nếu là IP public, vẫn trả về (có thể dùng được)
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    
    return '127.0.0.1'; // Fallback
}

// Trả về IP dưới dạng JSON
header('Content-Type: application/json');
echo json_encode([
    'ip' => getServerIP(),
    'hostname' => gethostname(),
    'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A'
]);


