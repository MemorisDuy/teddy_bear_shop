<?php
/**
 * EMAIL FUNCTIONS - Gửi email xác nhận đơn hàng
 * File: includes/email_functions.php
 * Version: 1.0
 */

// Protect from multiple inclusions
if (defined('EMAIL_FUNCTIONS_INCLUDED')) {
    return;
}
define('EMAIL_FUNCTIONS_INCLUDED', true);

/**
 * Gửi email xác nhận đơn hàng COD (Thanh toán tiền mặt)
 * 
 * @param array $order_data - Thông tin đơn hàng từ database
 * @param string $customer_email - Email khách hàng
 * @param string $customer_name - Tên khách hàng
 * @param string $shipping_address - Địa chỉ giao hàng
 * @param float $total_amount - Tổng tiền
 * @param array $order_items - Các sản phẩm trong đơn hàng
 */
function sendCODConfirmationEmail($order_data, $customer_email, $customer_name, $shipping_address, $total_amount, $order_items) {
        
        // Thiết lập email header
    $shop_email = "nguyenngocduy0358591058@gmail.com"; // Email shop
    $shop_name = "Teddy Bear Paradise";
    $order_number = $order_data['order_number'];
    $order_date = date('d/m/Y H:i', strtotime($order_data['created_at']));
    
    // Tiêu đề email
    $subject = "✅ Xác nhận đơn hàng #{$order_number} - Teddy Bear Paradise";
    
    // HTML Email Content
    $html_content = getEmailTemplate('cod', [
        'customer_name' => $customer_name,
        'order_number' => $order_number,
        'order_date' => $order_date,
        'shipping_address' => $shipping_address,
        'total_amount' => $total_amount,
        'order_items' => $order_items,
        'shop_email' => $shop_email,
        'shop_name' => $shop_name,
        'shop_phone' => '0358591058',
        'shop_address' => '123 Đường ABC, Quận XYZ, TP.HCM'
    ]);
    
    // Gửi email
    return sendEmail($customer_email, $subject, $html_content);
}

/**
 * Gửi email xác nhận đơn hàng đã thanh toán (QR/Quét mã)
 * 
 * @param array $order_data - Thông tin đơn hàng từ database
 * @param string $customer_email - Email khách hàng
 * @param string $customer_name - Tên khách hàng
 * @param string $shipping_address - Địa chỉ giao hàng
 * @param float $total_amount - Tổng tiền
 * @param array $order_items - Các sản phẩm trong đơn hàng
 * @param string $payment_method - Phương thức thanh toán (momo, zalopay, bank_transfer)
 */
function sendPaidConfirmationEmail($order_data, $customer_email, $customer_name, $shipping_address, $total_amount, $order_items, $payment_method = 'qr') {
    
    // Thiết lập email header
    $shop_email = "nguyenngocduy0358591058@gmail.com"; // Email shop
    $shop_name = "Teddy Bear Paradise";
    $order_number = $order_data['order_number'];
    $order_date = date('d/m/Y H:i', strtotime($order_data['created_at']));
    
    // Tiêu đề email
    $subject = "🎉 Thanh toán thành công! Đơn hàng #{$order_number} - Teddy Bear Paradise";
    
    // HTML Email Content
    $html_content = getEmailTemplate('paid', [
        'customer_name' => $customer_name,
        'order_number' => $order_number,
        'order_date' => $order_date,
        'shipping_address' => $shipping_address,
        'total_amount' => $total_amount,
        'order_items' => $order_items,
        'payment_method' => $payment_method,
        'shop_email' => $shop_email,
        'shop_name' => $shop_name,
        'shop_phone' => '0358591058',
        'shop_address' => '123 Đường ABC, Quận XYZ, TP.HCM'
    ]);
    
    // Gửi email
    return sendEmail($customer_email, $subject, $html_content);
}

/**
 * Lấy template email HTML
 * 
 * @param string $type - Loại email: 'cod' hoặc 'paid'
 * @param array $data - Dữ liệu để điền vào template
 * @return string - HTML content
 */
function getEmailTemplate($type, $data) {
    
    if ($type === 'cod') {
        return getEmailTemplateCOD($data);
    } elseif ($type === 'paid') {
        return getEmailTemplatePaid($data);
    }
    
    return '';
}

/**
 * Template email COD (Thanh toán tiền mặt)
 */
function getEmailTemplateCOD($data) {
    extract($data);
    
    // Tạo danh sách sản phẩm
    $items_html = '';
    foreach ($order_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $items_html .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>".number_format($item['price'])."đ</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>".number_format($item_total)."đ</td>
        </tr>";
    }
    
    $html = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 30px; }
            .order-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
            .info-row:last-child { border-bottom: none; }
            .info-label { font-weight: bold; color: #666; }
            .info-value { color: #333; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f0f0f0; padding: 12px; text-align: left; font-weight: bold; }
            .total-row { background: #fff3e0; padding: 15px; border-radius: 8px; margin: 20px 0; display: flex; justify-content: space-between; }
            .total-amount { font-size: 24px; font-weight: bold; color: #ff6b9d; }
            .highlight-box { background: #fff5f7; border-left: 4px solid #ff6b9d; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .highlight-box strong { color: #ff6b9d; }
            .steps { background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .steps ol { margin: 10px 0; padding-left: 20px; }
            .steps li { margin: 8px 0; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
            .contact-info { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .contact-info a { color: #ff6b9d; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧸 {$shop_name}</h1>
                <p style='margin: 10px 0 0 0; font-size: 14px;'>Xác nhận đơn hàng COD</p>
            </div>
            
            <div class='content'>
                <p>Xin chào <strong>{$customer_name}</strong>,</p>
                
                <p>Cảm ơn bạn đã đặt hàng tại {$shop_name}! 🎉 Đơn hàng của bạn đã được tiếp nhận thành công.</p>
                
                <div class='order-info'>
                    <div class='info-row'>
                        <span class='info-label'>📦 Mã đơn hàng:</span>
                        <span class='info-value'><strong>#{$order_number}</strong></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📅 Ngày đặt hàng:</span>
                        <span class='info-value'>{$order_date}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📍 Địa chỉ giao hàng:</span>
                        <span class='info-value'>{$shipping_address}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>💳 Phương thức thanh toán:</span>
                        <span class='info-value'><strong>Tiền mặt (COD)</strong></span>
                    </div>
                </div>
                
                <h3 style='color: #ff6b9d; margin-top: 25px;'>📋 Chi tiết sản phẩm:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style='text-align: center;'>Số lượng</th>
                            <th style='text-align: right;'>Giá</th>
                            <th style='text-align: right;'>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$items_html}
                    </tbody>
                </table>
                
                <div class='total-row'>
                    <span>💰 Tổng cộng:</span>
                    <span class='total-amount'>".number_format($total_amount)."đ</span>
                </div>
                
                <div class='highlight-box'>
                    <strong>⏳ CHỈNH SỬA QUAN TRỌNG:</strong>
                    <p>• Bạn sẽ <strong>thanh toán tiền mặt khi nhận hàng</strong> từ shipper<br>
                    • Vui lòng chuẩn bị đủ tiền mặt: <strong style='color: #ff6b9d;'>".number_format($total_amount)."đ</strong><br>
                    • Kiểm tra số lượng, chất lượng sản phẩm trước khi thanh toán<br>
                    • Nếu có vấn đề, vui lòng từ chối nhận hàng</p>
                </div>
                
                <div class='steps'>
                    <h4 style='color: #ff6b9d; margin-top: 0;'>📌 Các bước tiếp theo:</h4>
                    <ol>
                        <li><strong>Chờ xác nhận:</strong> Shop sẽ xác nhận đơn hàng trong vòng 1-2 giờ</li>
                        <li><strong>Chuẩn bị hàng:</strong> Sản phẩm sẽ được đóng gói cẩn thận</li>
                        <li><strong>Giao hàng:</strong> Shipper sẽ liên hệ với bạn để thỏa thuận thời gian giao</li>
                        <li><strong>Thanh toán:</strong> Bạn thanh toán tiền mặt khi nhận hàng</li>
                        <li><strong>Xác nhận:</strong> Sau khi giao, chúng tôi sẽ gửi thư cảm ơn</li>
                    </ol>
                </div>
                
                <div class='contact-info'>
                    <p><strong>📞 Cần hỗ trợ?</strong></p>
                    <p style='margin: 10px 0;'>
                        <strong>☎️ Hotline:</strong> <a href='tel:0358591058'>0358591058</a><br>
                        <strong>💬 Zalo:</strong> <a href='https://zalo.me/0358591058'>0358591058</a><br>
                        <strong>🏪 Địa chỉ:</strong> {$shop_address}<br>
                        <strong>🕒 Giờ mở:</strong> 8:00 - 22:00 (Mỗi ngày)
                    </p>
                    <p style='font-size: 12px; color: #999;'>Liên hệ chúng tôi ngay nếu bạn có bất kỳ câu hỏi nào!</p>
                </div>
                
                <p style='margin-top: 30px; color: #999; font-size: 14px;'>
                    Cảm ơn bạn đã tin tưởng và lựa chọn <strong>Teddy Bear Paradise</strong>! 💖<br>
                    <em>Chúng tôi cam kết cung cấp sản phẩm chất lượng cao và dịch vụ tốt nhất.</em>
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Teddy Bear Paradise. Tất cả quyền được bảo lưu.</p>
                <p>Email này được gửi từ hệ thống đặt hàng tự động. Vui lòng không trả lời email này.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

/**
 * Template email PAID (Thanh toán thành công - QR/Momo/ZaloPay)
 */
function getEmailTemplatePaid($data) {
    extract($data);
    
    // Tạo danh sách sản phẩm
    $items_html = '';
    foreach ($order_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $items_html .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>".number_format($item['price'])."đ</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>".number_format($item_total)."đ</td>
        </tr>";
    }
    
    // Xác định phương thức thanh toán
    $payment_text = '';
    $payment_icon = '';
    switch ($payment_method) {
        case 'qr':
        case 'momo':
            $payment_text = 'Ví Momo';
            $payment_icon = '📱';
            break;
        case 'zalopay':
            $payment_text = 'ZaloPay';
            $payment_icon = '🎯';
            break;
        case 'bank_transfer':
            $payment_text = 'Chuyển khoản ngân hàng';
            $payment_icon = '🏦';
            break;
        default:
            $payment_text = 'Thanh toán online';
            $payment_icon = '💳';
    }
    
    $html = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #4caf50, #45a049); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .success-badge { display: inline-block; background: #fff; color: #4caf50; padding: 8px 16px; border-radius: 20px; margin-top: 10px; font-weight: bold; }
            .content { padding: 30px; }
            .order-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
            .info-row:last-child { border-bottom: none; }
            .info-label { font-weight: bold; color: #666; }
            .info-value { color: #333; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f0f0f0; padding: 12px; text-align: left; font-weight: bold; }
            .total-row { background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 20px 0; display: flex; justify-content: space-between; }
            .total-amount { font-size: 24px; font-weight: bold; color: #4caf50; }
            .success-box { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .success-box strong { color: #4caf50; }
            .steps { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .steps ol { margin: 10px 0; padding-left: 20px; }
            .steps li { margin: 8px 0; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
            .contact-info { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .contact-info a { color: #4caf50; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 {$shop_name}</h1>
                <p style='margin: 10px 0 0 0; font-size: 14px;'>Thanh toán thành công!</p>
                <div class='success-badge'>✅ ĐÃ THANH TOÁN</div>
            </div>
            
            <div class='content'>
                <p>Xin chào <strong>{$customer_name}</strong>,</p>
                
                <p>Tuyệt vời! 🎊 Thanh toán đơn hàng của bạn đã được xác nhận thành công. Hàng của bạn sẽ sớm được giao đến tay!</p>
                
                <div class='order-info'>
                    <div class='info-row'>
                        <span class='info-label'>📦 Mã đơn hàng:</span>
                        <span class='info-value'><strong>#{$order_number}</strong></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📅 Ngày đặt hàng:</span>
                        <span class='info-value'>{$order_date}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📍 Địa chỉ giao hàng:</span>
                        <span class='info-value'>{$shipping_address}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>💳 Trạng thái thanh toán:</span>
                        <span class='info-value'><strong style='color: #4caf50;'>✅ ĐÃ THANH TOÁN</strong></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>💰 Phương thức:</span>
                        <span class='info-value'><strong>{$payment_icon} {$payment_text}</strong></span>
                    </div>
                </div>
                
                <h3 style='color: #4caf50; margin-top: 25px;'>📋 Chi tiết sản phẩm:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style='text-align: center;'>Số lượng</th>
                            <th style='text-align: right;'>Giá</th>
                            <th style='text-align: right;'>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$items_html}
                    </tbody>
                </table>
                
                <div class='total-row'>
                    <span>💰 Tổng cộng:</span>
                    <span class='total-amount'>".number_format($total_amount)."đ</span>
                </div>
                
                <div class='success-box'>
                    <strong>✨ THANH TOÁN THÀNH CÔNG!</strong>
                    <p>• Đơn hàng của bạn đã được <strong>xác nhận thanh toán</strong><br>
                    • Khoản tiền <strong>".number_format($total_amount)."đ</strong> đã được ghi nhận<br>
                    • Hàng đang được chuẩn bị giao đến bạn<br>
                    • Bạn sẽ nhận được thông báo khi shipper sắp đến</p>
                </div>
                
                <div class='steps'>
                    <h4 style='color: #4caf50; margin-top: 0;'>📌 Các bước tiếp theo:</h4>
                    <ol>
                        <li><strong>Chuẩn bị giao:</strong> Shop đang đóng gói sản phẩm cẩn thận</li>
                        <li><strong>Thông báo shipper:</strong> Shipper sẽ liên hệ để xác nhận thời gian giao hàng</li>
                        <li><strong>Giao hàng:</strong> Nhận hàng từ shipper (đã thanh toán, không cần trả tiền)</li>
                        <li><strong>Kiểm tra:</strong> Mở và kiểm tra sản phẩm ngay khi nhận</li>
                        <li><strong>Phản hồi:</strong> Gửi cho chúng tôi feedback hoặc đánh giá sản phẩm</li>
                    </ol>
                </div>
                
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <strong>ℹ️ LƯU Ý QUAN TRỌNG:</strong>
                    <p>• Hóa đơn điện tử sẽ được gửi qua email này<br>
                    • Kiểm tra email trong các folder: Inbox, Promotions, hoặc Spam<br>
                    • Nếu không tìm thấy, liên hệ: {$shop_phone}<br>
                    • Bạn có thể theo dõi đơn hàng qua mã: <strong>#{$order_number}</strong></p>
                </div>
                
                <div class='contact-info'>
                    <p><strong>📞 Cần hỗ trợ ngay?</strong></p>
                    <p style='margin: 10px 0;'>
                        <strong>☎️ Hotline:</strong> <a href='tel:0358591058'>0358591058</a><br>
                        <strong>💬 Zalo:</strong> <a href='https://zalo.me/0358591058'>0358591058</a><br>
                        <strong>🏪 Địa chỉ:</strong> {$shop_address}<br>
                        <strong>🕒 Giờ mở:</strong> 8:00 - 22:00 (Mỗi ngày)
                    </p>
                    <p style='font-size: 12px; color: #999;'>Liên hệ chúng tôi nếu có bất kỳ thắc mắc!</p>
                </div>
                
                <p style='margin-top: 30px; color: #999; font-size: 14px; text-align: center;'>
                    <strong>Cảm ơn bạn đã mua sắm tại Teddy Bear Paradise! 💖</strong><br>
                    Chúng tôi rất quý trọng sự tin tưởng của bạn.<br>
                    <em>Hãy để những chú gấu bông xinh xắn mang đến niềm vui cho bạn và những người yêu thương!</em>
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Teddy Bear Paradise. Tất cả quyền được bảo lưu.</p>
                <p>Email này được gửi từ hệ thống đặt hàng tự động. Vui lòng không trả lời email này.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

/**
 * Hàm gửi email (dùng mail() hoặc SMTP)
 * 
 * @param string $to - Email nhận
 * @param string $subject - Tiêu đề
 * @param string $html_content - Nội dung HTML
 * @return bool - True nếu gửi thành công
 */
function sendEmail($to, $subject, $html_content) {
    
    // ===== PHƯƠNG PHÁP 1: DÙNG GMAIL SMTP (KHUYÊN) =====
    try {
        $mail_result = sendEmailViaGmailSMTP($to, $subject, $html_content);
        if ($mail_result) {
            return true;
        }
    } catch (Exception $e) {
        error_log("Gmail SMTP error: " . $e->getMessage());
    }
    
    // ===== PHƯƠNG PHÁP 2: FALLBACK - DÙNG mail() =====
    // Nếu Gmail SMTP fail, thử dùng mail() thông thường
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Teddy Bear Paradise <nguyenngocduy0358591058@gmail.com>\r\n";
    $headers .= "Reply-To: nguyenngocduy0358591058@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $result = mail($to, $subject, $html_content, $headers);
    
    if ($result) {
        error_log("✅ Email sent via mail() to: {$to} | Subject: {$subject}");
    } else {
        error_log("❌ Email sending failed via mail() for: {$to} | Subject: {$subject}");
    }
    
    return $result;
}

/**
 * Gửi email qua Gmail SMTP
 * 
 * @param string $to - Email nhận
 * @param string $subject - Tiêu đề
 * @param string $html_content - Nội dung HTML
 * @return bool - True nếu gửi thành công
 */
function sendEmailViaGmailSMTP($to, $subject, $html_content) {
    
    // Cấu hình Gmail
    $gmail_email = "nguyenngocduy0358591058@gmail.com";
    $gmail_password = "jjyzgohrwtwygzac"; // App Password từ Google
    // Lấy App Password: https://myaccount.google.com/apppasswords
    // Chọn: Mail → Windows Computer → Sao chép password 16 ký tự
    
    // Cài đặt SMTP
    $smtp_host = "smtp.gmail.com";
    $smtp_port = 587;
    $smtp_secure = "tls";
    
    // Tạo kết nối SMTP
    $smtp = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
    
    if (!$smtp) {
        error_log("❌ SMTP Connection failed: $errstr ($errno)");
        return false;
    }
    
    // Hàm ghi vào SMTP
    if (!function_exists('smtp_write')) {
        function smtp_write(&$smtp, $str) {
            if ($smtp) {
                if (fwrite($smtp, $str) === false) {
                    return false;
                }
            }
            return true;
        }
    }
    
    // Hàm đọc từ SMTP
    if (!function_exists('smtp_read')) {
        function smtp_read(&$smtp) {
            if ($smtp) {
                $response = "";
                while (($line = fgets($smtp, 515)) != false) {
                    $response .= $line;
                    if (substr($line, 3, 1) == " ") {
                        break;
                    }
                }
                return $response;
            }
            return false;
        }
    }
    
    try {
        // Bước 1: EHLO
        $response = smtp_read($smtp);
        if (strpos($response, "220") === false) {
            throw new Exception("SMTP Connection error: " . trim($response));
        }
        
        smtp_write($smtp, "EHLO localhost\r\n");
        $response = smtp_read($smtp);
        
        // Bước 2: STARTTLS (mã hóa kết nối)
        smtp_write($smtp, "STARTTLS\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "220") === false) {
            throw new Exception("STARTTLS failed: " . trim($response));
        }
        
        // Bật SSL/TLS
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // Bước 3: EHLO lại sau STARTTLS
        smtp_write($smtp, "EHLO localhost\r\n");
        $response = smtp_read($smtp);
        
        // Bước 4: AUTH LOGIN
        smtp_write($smtp, "AUTH LOGIN\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "334") === false) {
            throw new Exception("AUTH LOGIN failed: " . trim($response));
        }
        
        // Bước 5: Gửi email (Base64)
        smtp_write($smtp, base64_encode($gmail_email) . "\r\n");
        $response = smtp_read($smtp);
        
        smtp_write($smtp, base64_encode($gmail_password) . "\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "235") === false) {
            throw new Exception("Authentication failed: " . trim($response));
        }
        
        // Bước 6: MAIL FROM
        smtp_write($smtp, "MAIL FROM: <$gmail_email>\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "250") === false) {
            throw new Exception("MAIL FROM failed: " . trim($response));
        }
        
        // Bước 7: RCPT TO
        smtp_write($smtp, "RCPT TO: <$to>\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "250") === false) {
            throw new Exception("RCPT TO failed: " . trim($response));
        }
        
        // Bước 8: DATA
        smtp_write($smtp, "DATA\r\n");
        $response = smtp_read($smtp);
        
        if (strpos($response, "354") === false) {
            throw new Exception("DATA failed: " . trim($response));
        }
        
        // Bước 9: Gửi email header + body
        $email_message = "To: $to\r\n";
        $email_message .= "From: Teddy Bear Paradise <$gmail_email>\r\n";
        $email_message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $email_message .= "MIME-Version: 1.0\r\n";
        $email_message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_message .= "\r\n";
        $email_message .= $html_content;
        $email_message .= "\r\n.\r\n";
        
        smtp_write($smtp, $email_message);
        $response = smtp_read($smtp);
        
        if (strpos($response, "250") === false) {
            throw new Exception("Email sending failed: " . trim($response));
        }
        
        // Bước 10: QUIT
        smtp_write($smtp, "QUIT\r\n");
        fclose($smtp);
        
        error_log("✅ Email sent via Gmail SMTP to: {$to} | Subject: {$subject}");
        return true;
        
    } catch (Exception $e) {
        if ($smtp) {
            fclose($smtp);
        }
        error_log("❌ Gmail SMTP Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi email thông báo đến Shop khi khách hàng yêu cầu đặt hàng theo mẫu riêng
 * 
 * @param array $custom_order - Thông tin yêu cầu đặt hàng
 * @return bool - True nếu gửi thành công
 */
function sendCustomOrderNotificationToShop($custom_order) {
    
    $shop_email = "nguyenngocduy0358591058@gmail.com";
    $shop_name = "Teddy Bear Paradise";
    
    $customer_name = $custom_order['customer_name'];
    $customer_email = $custom_order['customer_email'];
    $customer_phone = $custom_order['customer_phone'];
    $search_keyword = $custom_order['search_keyword'];
    $description = $custom_order['description'];
    $image_url = $custom_order['image_url'] ?? '';
    $created_at = $custom_order['created_at'];
    $custom_order_id = $custom_order['id'];
    
    $subject = "🎨 Yêu cầu đặt hàng theo mẫu riêng - #{$custom_order_id}";
    
    $html_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 30px; }
            .info-box { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff6b9d; }
            .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
            .info-row:last-child { border-bottom: none; }
            .info-label { font-weight: bold; color: #ff6b9d; min-width: 120px; }
            .info-value { color: #333; text-align: right; }
            .description-box { background: #fff5f7; border: 1px solid #ffccdd; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .action-buttons { margin-top: 25px; text-align: center; }
            .btn { display: inline-block; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold; margin: 0 5px; }
            .btn-primary { background: #ff6b9d; color: white; }
            .btn-secondary { background: #999; color: white; }
            .image-preview { max-width: 300px; margin: 15px auto; border-radius: 8px; border: 1px solid #ddd; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
            .highlight { background: #fffacd; padding: 10px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎨 Yêu cầu đặt hàng theo mẫu</h1>
                <p style='margin: 10px 0 0 0; font-size: 14px;'>Mã yêu cầu: #" . $custom_order_id . "</p>
            </div>
            
            <div class='content'>
                <p><strong>Xin chào Shop!</strong></p>
                
                <p>Bạn có một <strong style='color: #ff6b9d;'>yêu cầu đặt hàng theo mẫu riêng</strong> từ khách hàng. 🎁</p>
                
                <div class='info-box'>
                    <div class='info-row'>
                        <span class='info-label'>👤 Tên khách hàng:</span>
                        <span class='info-value'><strong>$customer_name</strong></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📧 Email:</span>
                        <span class='info-value'><a href='mailto:$customer_email'>$customer_email</a></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>📱 Điện thoại:</span>
                        <span class='info-value'><a href='tel:$customer_phone'>$customer_phone</a></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>🔍 Từ khóa tìm kiếm:</span>
                        <span class='info-value'><strong style='color: #ff6b9d;'>$search_keyword</strong></span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>⏰ Ngày yêu cầu:</span>
                        <span class='info-value'>$created_at</span>
                    </div>
                </div>
                
                <h3 style='color: #ff6b9d; margin-top: 25px;'>📝 Mô tả chi tiết:</h3>
                <div class='description-box'>
                    " . nl2br(htmlspecialchars($description)) . "
                </div>";
                
                if (!empty($image_url)) {
                    $html_content .= "
                    <h3 style='color: #ff6b9d; margin-top: 25px;'>🖼️ Hình ảnh tham khảo:</h3>
                    <div style='text-align: center;'>
                        <img src='$image_url' alt='Mẫu khách hàng' class='image-preview' style='max-width: 300px; border-radius: 8px;'>
                    </div>";
                }
                
                $html_content .= "
                
                <div class='highlight' style='margin-top: 25px;'>
                    <h4 style='margin-top: 0; color: #ff6b9d;'>⚡ HÀNH ĐỘNG CẦN THIẾT:</h4>
                    <ol>
                        <li><strong>Liên hệ khách hàng</strong> để xác nhận chi phí và thời gian thực hiện</li>
                        <li><strong>Lấy ý kiến khách</strong> về thiết kế/mẫu trước khi sản xuất</li>
                        <li><strong>Xác nhận đơn hàng</strong> qua email hoặc điện thoại</li>
                        <li><strong>Sản xuất và giao hàng</strong> theo thỏa thuận</li>
                        <li><strong>Cập nhật trạng thái</strong> trong hệ thống quản lý đơn hàng</li>
                    </ol>
                </div>
                
                <div class='action-buttons'>
                    <a href='http://localhost/teddy_bear_shop/admin/manage-custom-orders.php' class='btn btn-primary'>
                        📋 Xem chi tiết đầy đủ
                    </a>
                </div>
                
                <div style='background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <strong style='color: #4caf50;'>💡 Gợi ý:</strong>
                    <p>• Hãy nhanh chóng phản hồi khách hàng để tăng cơ hội bán hàng<br>
                    • Gửi bản thiết kế hoặc mẫu giá cho khách xem<br>
                    • Nếu có câu hỏi, liên hệ khách hàng ngay lập tức<br>
                    • Theo dõi yêu cầu này cho đến khi khách hàng quyết định</p>
                </div>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Teddy Bear Paradise. Tất cả quyền được bảo lưu.</p>
                <p>Email này được gửi từ hệ thống quản lý đơn hàng tự động.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($shop_email, $subject, $html_content);
}

/**
 * Gửi email xác nhận cho khách hàng khi họ gửi yêu cầu đặt hàng theo mẫu
 * 
 * @param array $custom_order - Thông tin yêu cầu đặt hàng
 * @return bool - True nếu gửi thành công
 */
function sendCustomOrderConfirmationToCustomer($custom_order) {
    
    $shop_name = "Teddy Bear Paradise";
    $shop_email = "nguyenngocduy0358591058@gmail.com";
    $shop_phone = "0358591058";
    
    $customer_email = $custom_order['customer_email'];
    $customer_name = $custom_order['customer_name'];
    $search_keyword = $custom_order['search_keyword'];
    $custom_order_id = $custom_order['id'];
    
    $subject = "✅ Shop đã nhận yêu cầu - Mẫu gấu bông riêng của bạn";
    
    $html_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { padding: 30px; }
            .success-box { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .info-box { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .info-row { padding: 8px 0; }
            .info-label { font-weight: bold; color: #ff6b9d; }
            .info-value { color: #333; }
            .steps { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .steps ol { margin: 10px 0; padding-left: 20px; }
            .steps li { margin: 8px 0; }
            .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
            .contact-info { background: #fff5f7; border: 1px solid #ffccdd; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .contact-info a { color: #ff6b9d; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Yêu cầu đã được ghi nhận</h1>
                <p style='margin: 10px 0 0 0; font-size: 14px;'>Cảm ơn bạn đã tin tưởng chúng tôi! 🎁</p>
            </div>
            
            <div class='content'>
                <p>Xin chào <strong>$customer_name</strong>,</p>
                
                <div class='success-box'>
                    <strong style='color: #4caf50; font-size: 1.1em;'>✨ Shop đã nhận được yêu cầu của bạn!</strong>
                    <p>Cảm ơn bạn đã gửi yêu cầu đặt hàng theo mẫu riêng. Đội ngũ của chúng tôi sẽ sớm liên hệ với bạn.</p>
                </div>
                
                <div class='info-box'>
                    <div class='info-row'>
                        <span class='info-label'>📝 Mã yêu cầu:</span>
                        <span class='info-value'>#$custom_order_id</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>🔍 Mẫu bạn tìm:</span>
                        <span class='info-value'><strong style='color: #ff6b9d;'>$search_keyword</strong></span>
                    </div>
                </div>
                
                <div class='steps'>
                    <h4 style='color: #ff6b9d; margin-top: 0;'>📋 Các bước tiếp theo:</h4>
                    <ol>
                        <li><strong>Shop liên hệ:</strong> Chúng tôi sẽ gọi hoặc nhắn tin để trao đổi chi tiết</li>
                        <li><strong>Thảo luận mẫu:</strong> Gửi bản thiết kế/mẫu cho bạn xác nhận</li>
                        <li><strong>Xác nhận giá:</strong> Thống nhất giá cả và thời gian thực hiện</li>
                        <li><strong>Sản xuất:</strong> Chúng tôi sẽ làm theo đúng mẫu bạn yêu cầu</li>
                        <li><strong>Giao hàng:</strong> Khi xong, giao hàng đến tay bạn</li>
                    </ol>
                </div>
                
                <div class='contact-info'>
                    <p><strong>📞 Liên hệ trực tiếp với shop:</strong></p>
                    <p style='margin: 10px 0;'>
                        <strong>☎️ Hotline:</strong> <a href='tel:0358591058'>0358591058</a><br>
                        <strong>💬 Zalo:</strong> <a href='https://zalo.me/0358591058'>0358591058</a><br>
                        <strong>📧 Email:</strong> <a href='mailto:$shop_email'>$shop_email</a>
                    </p>
                    <p style='font-size: 12px; color: #999;'>Bạn có thể liên hệ shop bất kỳ lúc nào để trao đổi thêm!</p>
                </div>
                
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <strong style='color: #ff6b9d;'>💡 MẸO:</strong>
                    <p>• Chuẩn bị hình ảnh tham khảo hoặc mô tả chi tiết mẫu gấu bạn muốn<br>
                    • Cung cấp các chi tiết như: màu sắc, kích thước, đặc điểm riêng<br>
                    • Hỏi thêm về giá, thời gian giao hàng<br>
                    • Nếu bạn thích, có thể yêu cầu hình ảnh mẫu trước khi sản xuất</p>
                </div>
                
                <p style='margin-top: 30px; color: #999; font-size: 14px; text-align: center;'>
                    <strong>Chúng tôi rất mong muốn tạo ra mẫu gấu bông hoàn hảo cho bạn! 💖</strong><br>
                    <em>Teddy Bear Paradise luôn sẵn sàng thực hiện những yêu cầu đặc biệt của khách hàng.</em>
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2025 Teddy Bear Paradise. Tất cả quyền được bảo lưu.</p>
                <p>Email này được gửi từ hệ thống quản lý đơn hàng tự động.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $subject, $html_content);
}

// ========== CUSTOM ORDER FUNCTIONS ==========

/**
 * Dùng Gemini AI enhance prompt mô tả → prompt tạo ảnh chi tiết
 * 
 * @param string $description - Mô tả từ khách hàng
 * @return string - Enhanced prompt cho AI tạo ảnh
 */
function enhancePromptWithGemini($description) {
    $api_key = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;
    
    $prompt = "Chuyển đổi mô tả này thành prompt tiếng Anh để tìm ảnh gấu bông:

\"{$description}\"

Trả về CHÍNH XÁC 1 dòng prompt duy nhất, bắt đầu bằng \"teddy bear\" hoặc \"stuffed bear\", kết thúc bằng \"cute\". Ví dụ:
- teddy bear sunflower yellow cute
- stuffed bear pink rose cute  
- plush bear brown handmade cute

Prompt phải ngắn (dưới 10 từ), không có dấu câu.";
    
    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($result, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $enhanced = trim($data['candidates'][0]['content']['parts'][0]['text']);
            error_log('Gemini enhanced prompt: ' . $enhanced);
            return $enhanced;
        }
    }
    
    // Fallback: tự tạo prompt từ description
    error_log('Gemini enhancement failed (HTTP ' . $http_code . '), creating custom prompt');
    
    // Extract keywords từ description
    $keywords = [];
    if (preg_match('/(sunflower|hướng dương|hoa vàng)/i', $description)) {
        $keywords[] = 'sunflower';
    }
    if (preg_match('/(pink|hồng|rose)/i', $description)) {
        $keywords[] = 'pink';
    }
    if (preg_match('/(brown|nâu|chocolate)/i', $description)) {
        $keywords[] = 'brown';
    }
    if (preg_match('/(white|trắng)/i', $description)) {
        $keywords[] = 'white';
    }
    if (preg_match('/(big|lớn|size|kích thước)/i', $description)) {
        $keywords[] = 'large';
    }
    
    $keyword_str = count($keywords) > 0 ? implode(' ', $keywords) : 'cute';
    return "teddy bear {$keyword_str} cute";
}

/**
 * Tạo ảnh từ Unsplash API (Free & Fast)
 * 
 * @param string $prompt - Prompt/keyword tìm ảnh
 * @param int $num_images - Số lượng ảnh (1-6)
 * @return array - [['url' => ..., 'prompt' => ...], ...]
 */
function generateImagesWithUnsplash($prompt, $num_images = 6) {
    $images = [];
    
    // Extract keywords từ prompt
    $keywords = [];
    
    // Tách từ từ prompt
    $words = explode(' ', strtolower($prompt));
    
    // Thêm các keyword liên quan
    $search_queries = [];
    
    // Nếu prompt có "sunflower" hoặc "hướng dương"
    if (preg_match('/(sunflower|hướng dương|hoa)/i', $prompt)) {
        $search_queries[] = 'sunflower teddy bear';
        $search_queries[] = 'yellow teddy bear';
        $search_queries[] = 'teddy bear flower';
        $search_queries[] = 'plush bear sunflower';
    }
    
    // Nếu prompt có "pink", "hồng"
    if (preg_match('/(pink|hồng|rose)/i', $prompt)) {
        $search_queries[] = 'pink teddy bear';
        $search_queries[] = 'cute pink plush';
        $search_queries[] = 'rose teddy bear';
    }
    
    // Nếu prompt có "brown", "nâu"
    if (preg_match('/(brown|nâu|chocolate)/i', $prompt)) {
        $search_queries[] = 'brown teddy bear';
        $search_queries[] = 'chocolate plush toy';
        $search_queries[] = 'teddy bear brown';
    }
    
    // Fallback: generic queries
    if (count($search_queries) === 0) {
        $search_queries = [
            'teddy bear cute',
            'stuffed animal toy',
            'plush bear handmade',
            'soft teddy doll',
            'craft teddy bear',
            'cute plush toy'
        ];
    }
    
    // Nếu chưa đủ queries, thêm generic
    while (count($search_queries) < $num_images) {
        $search_queries[] = 'teddy bear';
    }
    
    for ($i = 0; $i < min($num_images, count($search_queries)); $i++) {
        $search_query = urlencode($search_queries[$i]);
        
        // Thêm random page để lấy ảnh khác nhau
        $page = rand(1, 3);
        $unsplash_url = "https://unsplash.com/napi/search/photos?query={$search_query}&page={$page}&per_page=1";
        
        $ch = curl_init($unsplash_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Teddy-Bear-Shop']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($result, true);
            if (isset($data['results'][0])) {
                $photo = $data['results'][0];
                $image_url = $photo['urls']['regular'] ?? $photo['urls']['small'];
                
                $images[] = [
                    'id' => 'unsplash_' . ($i + 1),
                    'thumb' => $photo['urls']['thumb'],
                    'full' => $image_url,
                    'prompt' => $search_queries[$i],
                    'source' => 'Unsplash'
                ];
                continue;
            }
        }
        
        // Fallback: placeholder
        $images[] = [
            'id' => 'placeholder_' . ($i + 1),
            'thumb' => 'https://via.placeholder.com/300x300?text=Teddy+Bear+' . ($i + 1),
            'full' => 'https://via.placeholder.com/600x600?text=Teddy+Bear+' . ($i + 1),
            'prompt' => 'Teddy bear image',
            'source' => 'Placeholder'
        ];
    }
    
    return $images;
}

/**
 * Tạo ảnh từ mô tả (Gemini enhance + Unsplash search + Pixabay fallback)
 */
function generateCustomOrderImages($description) {
    $images = [];
    
    try {
        // Step 1: Enhance prompt với Gemini
        error_log('Enhancing prompt with Gemini for: ' . $description);
        $enhanced_prompt = enhancePromptWithGemini($description);
        
        // Step 2: Tạo ảnh với Unsplash (FREE, FAST)
        error_log('Generating images with Unsplash');
        $ai_images = generateImagesWithUnsplash($enhanced_prompt, 6);
        
        if (count($ai_images) > 0) {
            return $ai_images;
        }
        
        throw new Exception('No images from Unsplash');
        
    } catch (Exception $e) {
        error_log('Image generation error: ' . $e->getMessage());
        
        // Fallback: Dùng Pixabay
        try {
            error_log('Falling back to Pixabay search');
            return getPixabayImages($description, 6);
        } catch (Exception $e2) {
            error_log('Pixabay fallback also failed: ' . $e2->getMessage());
            return [];
        }
    }
}

/**
 * Lấy danh sách ảnh từ Pixabay API
 * 
 * @param string $keyword - Từ khóa tìm kiếm (VD: "teddy bear", "gấu nâu")
 * @param int $per_page - Số ảnh trả về (default 6)
 * @return array - Danh sách ảnh [['id' => ..., 'thumb' => ..., 'full' => ...], ...]
 */
function getPixabayImages($keyword, $per_page = 6) {
    $api_key = PIXABAY_API_KEY;
    $keyword = urlencode($keyword);
    
    $url = "https://pixabay.com/api/?key={$api_key}&q={$keyword}&image_type=photo&per_page={$per_page}&safesearch=true";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception("Pixabay API error: HTTP {$http_code}");
    }
    
    $data = json_decode($response, true);
    
    if (!$data || empty($data['hits'])) {
        return [];
    }
    
    // Format lại dữ liệu để dễ dùng
    $images = [];
    foreach ($data['hits'] as $hit) {
        $images[] = [
            'id' => $hit['id'],
            'thumb' => $hit['previewURL'],  // Ảnh nhỏ để hiển thị
            'full' => $hit['largeImageURL'], // Ảnh lớn để lưu
            'tags' => $hit['tags'] ?? '',
            'user' => $hit['user'] ?? 'Pixabay User'
        ];
    }
    
    return $images;
}

/**
 * Email thông báo cho khách hàng rằng yêu cầu đã được nhận
 * (Bước 1 hoàn thành - đang tìm ảnh)
 */
function sendCustomOrderAcknowledgmentToCustomer($custom_order) {
    $customer_name = $custom_order['customer_name'];
    $customer_email = $custom_order['customer_email'];
    $custom_order_id = $custom_order['id'];
    $search_keyword = $custom_order['search_keyword'];
    
    $subject = "✅ Yêu cầu đặt mẫu riêng #{$custom_order_id} - Đang xử lý";
    
    $html_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 20px; border-radius: 8px; text-align: center; }
            .content { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #ff6b9d; }
            .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎨 Yêu cầu đặt mẫu riêng - Đã nhận</h1>
            </div>
            
            <div class='content'>
                <h3>Xin chào " . htmlspecialchars($customer_name) . ",</h3>
                <p>Cảm ơn bạn đã gửi yêu cầu đặt mẫu gấu bông riêng!</p>
                
                <p><strong>Thông tin yêu cầu:</strong></p>
                <ul>
                    <li><strong>Mã yêu cầu:</strong> #" . $custom_order_id . "</li>
                    <li><strong>Mẫu tìm kiếm:</strong> " . htmlspecialchars($search_keyword) . "</li>
                    <li><strong>Trạng thái:</strong> ⏳ Đang tìm kiếm ảnh gợi ý</li>
                </ul>
                
                <p style='color: #ff6b9d;'><strong>📸 Chúng tôi đang tìm kiếm các ảnh gấu bông phù hợp với mô tả của bạn. Vui lòng chờ một chút...</strong></p>
                
                <p>Sau khi tìm được các ảnh phù hợp, chúng tôi sẽ gửi email cho bạn để bạn chọn lựa ảnh gấu bông mà bạn thích nhất.</p>
            </div>
            
            <div class='footer'>
                <p>Teddy Bear Paradise - Cửa hàng gấu bông chất lượng cao</p>
                <p>Email: nguyenngocduy0358591058@gmail.com | Điện thoại: 0358591058</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $subject, $html_content);
}

/**
 * Email gửi ảnh gợi ý cho khách hàng để chọn
 */
function sendCustomOrderImagesToCustomer($custom_order, $images) {
    $customer_name = $custom_order['customer_name'];
    $customer_email = $custom_order['customer_email'];
    $custom_order_id = $custom_order['id'];
    
    $subject = "🖼️ Ảnh gợi ý cho yêu cầu #{$custom_order_id} - Vui lòng chọn";
    
    $images_html = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0;">';
    $image_count = 0;
    foreach ($images as $img) {
        $images_html .= "
        <div style='border: 1px solid #ddd; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.3s;' onclick='selectImage({$img['id']})'>
            <img src='{$img['thumb']}' style='width: 100%; height: 200px; object-fit: cover; display: block;' alt='Image'>
            <p style='padding: 8px; text-align: center; font-size: 12px; color: #999; margin: 0;'>
                <input type='radio' name='selected_image' value='{$img['id']}' data-url='{$img['full']}'>
                Chọn ảnh này
            </p>
        </div>";
        $image_count++;
    }
    $images_html .= '</div>';
    
    $html_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 20px; border-radius: 8px; text-align: center; }
            .content { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; }
            .images-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
            .image-item { border: 2px solid #ddd; border-radius: 8px; overflow: hidden; text-align: center; }
            .image-item img { width: 100%; height: 200px; object-fit: cover; display: block; }
            .image-info { padding: 10px; background: #f5f5f5; font-size: 12px; }
            .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
            .btn { display: inline-block; background: #ff6b9d; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; margin: 10px 5px; font-weight: bold; }
            .btn:hover { background: #e91e63; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🖼️ Ảnh gợi ý cho yêu cầu của bạn</h1>
            </div>
            
            <div class='content'>
                <h3>Xin chào " . htmlspecialchars($customer_name) . ",</h3>
                <p>Dưới đây là " . $image_count . " ảnh gợi ý phù hợp với mô tả của bạn. Vui lòng chọn 1 ảnh mà bạn thích nhất:</p>
                
                <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                    <p style='margin: 0;'><strong>💡 Hướng dẫn:</strong></p>
                    <p style='margin: 5px 0;'>1. Xem các ảnh bên dưới</p>
                    <p style='margin: 5px 0;'>2. Chọn ảnh mà bạn thích (click vào ảnh hoặc checkbox)</p>
                    <p style='margin: 5px 0;'>3. Đánh giá ảnh từ 1-5 sao (1 = không thích, 5 = rất thích)</p>
                    <p style='margin: 5px 0;'>4. Nhấn nút \"Xác nhận & Gửi\" để gửi cho shop</p>
                </div>
                
                {$images_html}
                
                <div style='text-align: center;'>
                    <a href='http://localhost/teddy_bear_shop/pages/search.php?confirm-custom-order=" . $custom_order_id . "' class='btn'>Xem & Xác nhận ảnh</a>
                </div>
            </div>
            
            <div class='footer'>
                <p>Teddy Bear Paradise - Cửa hàng gấu bông chất lượng cao</p>
                <p>Email: nguyenngocduy0358591058@gmail.com | Điện thoại: 0358591058</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $subject, $html_content);
}

/**
 * Email gửi cho shop khi khách xác nhận + chọn ảnh (Step 2 hoàn thành)
 */
function sendCustomOrderConfirmationToShop($custom_order) {
    $shop_email = "nguyenngocduy0358591058@gmail.com";
    $customer_name = $custom_order['customer_name'];
    $customer_email = $custom_order['customer_email'];
    $customer_phone = $custom_order['customer_phone'];
    $custom_order_id = $custom_order['id'];
    $search_keyword = $custom_order['search_keyword'];
    $description = $custom_order['description'];
    $selected_image_url = $custom_order['selected_image_url'] ?? '';
    $rating = intval($custom_order['rating'] ?? 0);
    
    // Debug log
    error_log("sendCustomOrderConfirmationToShop - Order #" . $custom_order_id . ", Image URL: " . (empty($selected_image_url) ? "EMPTY" : substr($selected_image_url, 0, 100)));
    
    $subject = "🎨 Yêu cầu đặt mẫu riêng #{$custom_order_id} - Khách đã xác nhận ảnh";
    
    $image_section = '';
    if (!empty($selected_image_url)) {
        $safe_url = htmlspecialchars($selected_image_url, ENT_QUOTES, 'UTF-8');
        $star_rating = str_repeat('⭐', $rating);
        $image_section = "
        <div style='margin: 20px 0; text-align: center;'>
            <p><strong>📸 Ảnh được chọn:</strong></p>
            <img src='{$safe_url}' style='max-width: 400px; max-height: 300px; border: 1px solid #ddd; border-radius: 8px;' alt='Selected Image'>
            <p style='margin-top: 10px;'><strong>⭐ Đánh giá: {$star_rating} ({$rating}/5 sao)</strong></p>
        </div>";
    }
    
    $html_content = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
            .header { background: linear-gradient(135deg, #ff6b9d, #ff8e72); color: white; padding: 20px; border-radius: 8px; text-align: center; }
            .content { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; }
            .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
            .info-box { background: #f0f7ff; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎨 Yêu cầu đặt mẫu riêng - Khách xác nhận</h1>
            </div>
            
            <div class='content'>
                <h3>Thông báo: Khách hàng đã xác nhận ảnh gợi ý!</h3>
                
                <div class='info-box'>
                    <p><strong>👤 Thông tin khách hàng:</strong></p>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li><strong>Tên:</strong> " . htmlspecialchars($customer_name) . "</li>
                        <li><strong>Email:</strong> " . htmlspecialchars($customer_email) . "</li>
                        <li><strong>Điện thoại:</strong> " . htmlspecialchars($customer_phone) . "</li>
                        <li><strong>Mã yêu cầu:</strong> #" . $custom_order_id . "</li>
                    </ul>
                </div>
                
                <div class='info-box'>
                    <p><strong>📝 Chi tiết mẫu gấu:</strong></p>
                    <p style='margin: 10px 0;'><strong>Mẫu tìm kiếm:</strong> " . htmlspecialchars($search_keyword) . "</p>
                    <p style='margin: 10px 0; white-space: pre-wrap;'><strong>Mô tả chi tiết:</strong><br>" . htmlspecialchars($description) . "</p>
                </div>
                
                {$image_section}
                
                <div style='background: #c8e6c9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                    <p style='margin: 0;'><strong style='color: #2e7d32;'>✅ Hành động cần làm:</strong></p>
                    <p style='margin: 10px 0;'>1. Kiểm tra ảnh được chọn + mô tả từ khách</p>
                    <p style='margin: 10px 0;'>2. Liên hệ khách hàng để thảo luận giá & thời gian</p>
                    <p style='margin: 10px 0;'>3. Thực hiện thiết kế/sản xuất theo yêu cầu</p>
                    <p style='margin: 10px 0;'>4. Cập nhật trạng thái trong admin panel</p>
                </div>
            </div>
            
            <div class='footer'>
                <p>Teddy Bear Paradise - Hệ thống đặt mẫu riêng</p>
                <p><a href='http://localhost/teddy_bear_shop/admin/manage-custom-orders.php?id=" . $custom_order_id . "'>Xem chi tiết trong admin</a></p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($shop_email, $subject, $html_content);
}

?>


