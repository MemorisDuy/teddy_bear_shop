# 🎯 Hướng dẫn tạo QR Code thanh toán không cần IP

## Giải pháp: Sử dụng trang demo công khai

Thay vì dùng IP localhost, bạn sẽ host file `payment-demo.html` lên một service miễn phí công khai.

## 📋 Các bước thực hiện:

### Cách 1: GitHub Pages (Khuyến nghị - Miễn phí)

1. **Tạo repository trên GitHub:**
   - Vào https://github.com/new
   - Tạo repo mới (ví dụ: `teddy-payment-demo`)
   - Chọn Public

2. **Upload file:**
   - Upload file `cart/payment-demo.html` vào repo
   - Đổi tên thành `index.html` (hoặc giữ nguyên tên)

3. **Bật GitHub Pages:**
   - Vào Settings → Pages
   - Chọn branch `main` hoặc `master`
   - Save
   - Đợi vài phút, bạn sẽ có URL: `https://your-username.github.io/teddy-payment-demo/`

4. **Cập nhật code:**
   - Mở file `cart/mock-payment.php`
   - Tìm dòng: `$demo_base_url = 'https://your-username.github.io/teddy-payment-demo/payment-demo.html';`
   - Thay bằng URL GitHub Pages của bạn

### Cách 2: Netlify (Dễ nhất - Miễn phí)

1. **Truy cập:** https://app.netlify.com/drop
2. **Kéo thả** file `cart/payment-demo.html` vào
3. **Lấy URL** (ví dụ: `https://random-name-123.netlify.app`)
4. **Cập nhật** `$demo_base_url` trong `cart/mock-payment.php`

### Cách 3: Vercel (Miễn phí)

1. **Truy cập:** https://vercel.com
2. **Đăng nhập** bằng GitHub
3. **Import** repo hoặc upload file
4. **Lấy URL** và cập nhật code

### Cách 4: Sử dụng service URL shortener

Nếu bạn đã có một trang web, có thể tạo short link:
- Bit.ly
- TinyURL
- Hoặc bất kỳ service nào

## 🔧 Cập nhật code

Sau khi có URL công khai, sửa file `cart/mock-payment.php`:

```php
// Thay dòng này:
$demo_base_url = 'https://your-username.github.io/teddy-payment-demo/payment-demo.html';

// Bằng URL của bạn:
$demo_base_url = 'https://your-actual-url.com/payment-demo.html';
```

## ✅ Kết quả

- ✅ Không cần IP
- ✅ Không cần deploy toàn bộ website
- ✅ QR code hoạt động trên mọi thiết bị
- ✅ Chỉ cần host 1 file HTML đơn giản

## 🎨 Tùy chỉnh

Bạn có thể chỉnh sửa file `payment-demo.html` để:
- Thay đổi màu sắc
- Thêm logo
- Thay đổi thông báo
- Thêm animation

## 📱 Test

1. Tạo đơn hàng và chọn thanh toán online
2. Quét QR code bằng điện thoại
3. Sẽ mở trang demo công khai
4. Hiển thị "Thanh toán thành công"


