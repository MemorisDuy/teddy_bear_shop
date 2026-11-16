# SƠ ĐỒ UML VÀ ERD - TEDDY BEAR SHOP

File này chứa tất cả các sơ đồ UML và ERD cho báo cáo bài tập lớn.

---

## 1. USE CASE DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│                    USE CASE DIAGRAM                            │
│                  TEDDY BEAR SHOP SYSTEM                         │
└─────────────────────────────────────────────────────────────────┘

                    ┌──────────────┐
                    │   Customer   │
                    └──────┬───────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
   ┌────▼────┐      ┌──────▼──────┐    ┌─────▼─────┐
   │ Đăng ký │      │ Đăng nhập   │    │ Tìm kiếm  │
   └─────────┘      └─────────────┘    └───────────┘
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
   ┌───────────────────────▼───────────────────────┐
   │  Xem danh sách sản phẩm                        │
   │  Xem chi tiết sản phẩm                         │
   │  Thêm vào giỏ hàng                             │
   │  Quản lý giỏ hàng (cập nhật, xóa)             │
   │  Áp dụng mã giảm giá                           │
   │  Đặt hàng                                      │
   │  Thanh toán                                     │
   │  Xem lịch sử đơn hàng                          │
   │  Đánh giá sản phẩm                             │
   │  Đặt hàng theo mẫu riêng                       │
   │  Quản lý tài khoản (xem, cập nhật)            │
   └───────────────────────────────────────────────┘

                    ┌──────────────┐
                    │    Admin      │
                    └──────┬───────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
   ┌────▼────┐      ┌──────▼──────┐    ┌─────▼─────┐
   │Đăng nhập│      │Quản lý SP   │    │Quản lý ĐH │
   └─────────┘      └─────────────┘    └───────────┘
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
   ┌───────────────────────▼───────────────────────┐
   │  Quản lý sản phẩm (CRUD)                        │
   │  Quản lý danh mục                               │
   │  Quản lý đơn hàng                               │
   │  Cập nhật trạng thái đơn hàng                   │
   │  Quản lý người dùng                             │
   │  Quản lý mã giảm giá                            │
   │  Quản lý đơn đặt mẫu riêng                      │
   │  Xem thống kê và báo cáo                        │
   │  Phản hồi khách hàng                            │
   └───────────────────────────────────────────────┘

                    ┌──────────────┐
                    │    System     │
                    └──────┬───────┘
                           │
   ┌────────────────────────▼────────────────────────┐
   │  Gửi email xác nhận đơn hàng                     │
   │  Gửi email thông báo đơn đặt mẫu riêng           │
   │  Tính toán phí vận chuyển                       │
   │  Xử lý thanh toán                                │
   │  Cập nhật số lượng tồn kho                       │
   └─────────────────────────────────────────────────┘
```

---

## 2. CLASS DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLASS DIAGRAM                               │
│                  TEDDY BEAR SHOP SYSTEM                        │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│     User     │         │   Product   │         │    Order    │
├──────────────┤         ├──────────────┤         ├──────────────┤
│ -id: int     │         │ -id: int     │         │ -id: int     │
│ -username    │         │ -name: string│         │ -order_number│
│ -email       │         │ -slug: string│         │ -user_id: int│
│ -password    │         │ -price: decimal│       │ -total_amount│
│ -full_name   │         │ -stock_qty: int│       │ -status: enum│
│ -role: enum  │         │ -category_id│         │ -created_at  │
│ -status: enum│         │ -status: enum│         ├──────────────┤
├──────────────┤         ├──────────────┤         │ +create()    │
│ +login()     │         │ +getById()   │         │ +updateStatus│
│ +register()  │         │ +search()    │         │ +getByUser() │
│ +update()    │         │ +getByCat()  │         │ +calculate() │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │                        │                        │
       │         ┌──────────────▼──────────────┐         │
       │         │         Cart                │         │
       │         ├──────────────┤              │         │
       │         │ -id: int     │              │         │
       │         │ -user_id: int│              │         │
       │         │ -product_id  │              │         │
       │         │ -quantity    │              │         │
       │         ├──────────────┤              │         │
       │         │ +addItem()   │              │         │
       │         │ +updateQty() │              │         │
       │         │ +removeItem()│              │         │
       │         │ +getTotal()  │              │         │
       │         └──────┬───────┘              │         │
       │                │                      │         │
       │                │                      │         │
       └────────────────┼──────────────────────┘         │
                        │                                │
                        │                                │
       ┌────────────────▼────────────────┐                │
       │      OrderItem                 │                │
       ├────────────────────────────────┤                │
       │ -id: int                       │                │
       │ -order_id: int                 │◄───────────────┘
       │ -product_id: int               │
       │ -quantity: int                 │
       │ -price: decimal                 │
       │ -total: decimal                 │
       └────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   Coupon    │         │ CustomOrder │         │    Review    │
├──────────────┤         ├──────────────┤         ├──────────────┤
│ -id: int     │         │ -id: int     │         │ -id: int     │
│ -code: string│         │ -customer_name│        │ -product_id  │
│ -type: enum  │         │ -description │         │ -user_id: int│
│ -value: decimal│       │ -image_url   │         │ -rating: int │
│ -min_amount  │         │ -status: enum│         │ -comment     │
│ -status: enum│         │ -shop_message│         │ -status: enum│
├──────────────┤         ├──────────────┤         ├──────────────┤
│ +validate()  │         │ +create()    │         │ +create()    │
│ +apply()     │         │ +updateStatus│         │ +getByProduct│
│ +increment() │         │ +addResponse │         │ +approve()   │
└──────────────┘         └──────────────┘         └──────────────┘
```

---

## 3. ACTIVITY DIAGRAM - QUY TRÌNH ĐẶT HÀNG

```
┌─────────────────────────────────────────────────────────────────┐
│         ACTIVITY DIAGRAM - QUY TRÌNH ĐẶT HÀNG                   │
└─────────────────────────────────────────────────────────────────┘

[Start]
    ↓
[Khách hàng xem sản phẩm]
    ↓
[Thêm sản phẩm vào giỏ hàng]
    ↓
[Xem giỏ hàng]
    ↓
{Có sản phẩm trong giỏ?}
    ├─ No → [Kết thúc]
    └─ Yes ↓
[Áp dụng mã giảm giá (nếu có)]
    ↓
[Điền thông tin giao hàng]
    ↓
[Chọn phương thức thanh toán]
    ↓
{Tài khoản đã đăng nhập?}
    ├─ No → [Yêu cầu đăng nhập/đăng ký]
    │       ↓
    │   [Đăng nhập/Đăng ký]
    │       ↓
    └─ Yes ↓
[Xác nhận đơn hàng]
    ↓
[Lưu đơn hàng vào database]
    ↓
[Tạo OrderItems]
    ↓
[Cập nhật số lượng tồn kho]
    ↓
[Xóa giỏ hàng]
    ↓
[Gửi email xác nhận]
    ↓
[Hiển thị trang thành công]
    ↓
[End]
```

---

## 4. ACTIVITY DIAGRAM - QUẢN LÝ ĐƠN HÀNG (ADMIN)

```
┌─────────────────────────────────────────────────────────────────┐
│    ACTIVITY DIAGRAM - QUẢN LÝ ĐƠN HÀNG (ADMIN)                  │
└─────────────────────────────────────────────────────────────────┘

[Start]
    ↓
[Admin đăng nhập]
    ↓
[Vào trang quản lý đơn hàng]
    ↓
[Xem danh sách đơn hàng]
    ↓
[Chọn đơn hàng cần xử lý]
    ↓
[Xem chi tiết đơn hàng]
    ↓
{Trạng thái đơn hàng?}
    ├─ Pending → [Xác nhận đơn hàng]
    │              ↓
    │          [Cập nhật status = confirmed]
    │              ↓
    ├─ Confirmed → [Chuẩn bị hàng]
    │              ↓
    │          [Cập nhật status = processing]
    │              ↓
    ├─ Processing → [Giao hàng]
    │              ↓
    │          [Cập nhật status = shipping]
    │              ↓
    └─ Shipping → [Xác nhận giao hàng]
                  ↓
              [Cập nhật status = delivered]
                  ↓
              [Cập nhật payment_status = paid]
                  ↓
              [Gửi email thông báo]
                  ↓
              [End]
```

---

## 5. ACTIVITY DIAGRAM - ĐẶT HÀNG THEO MẪU RIÊNG

```
┌─────────────────────────────────────────────────────────────────┐
│    ACTIVITY DIAGRAM - ĐẶT HÀNG THEO MẪU RIÊNG                   │
└─────────────────────────────────────────────────────────────────┘

[Start]
    ↓
[Khách hàng tìm kiếm sản phẩm]
    ↓
{Không tìm thấy sản phẩm?}
    ├─ No → [Xem sản phẩm]
    │       ↓
    │   [End]
    └─ Yes ↓
[Click "Đặt mẫu riêng"]
    ↓
[Hiển thị form đặt hàng]
    ↓
[Điền thông tin: Tên, Email, SĐT, Mô tả]
    ↓
[Upload hình ảnh tham khảo (tùy chọn)]
    ↓
[Gửi yêu cầu]
    ↓
[Validate dữ liệu]
    ↓
{Valid?}
    ├─ No → [Hiển thị lỗi]
    │       ↓
    │   [Quay lại form]
    └─ Yes ↓
[Lưu vào database (custom_orders)]
    ↓
[Upload hình ảnh (nếu có)]
    ↓
[Gửi email xác nhận cho khách hàng]
    ↓
[Gửi email thông báo cho shop]
    ↓
[Hiển thị thông báo thành công]
    ↓
[End]

─────────────────────────────────────────────────────────────────

[Admin nhận email]
    ↓
[Đăng nhập admin]
    ↓
[Vào trang quản lý custom orders]
    ↓
[Xem yêu cầu mới]
    ↓
[Xem chi tiết: mô tả, hình ảnh]
    ↓
[Liên hệ khách hàng]
    ↓
[Cập nhật status = responded]
    ↓
[Thêm phản hồi]
    ↓
{Khách đồng ý?}
    ├─ Yes → [Tạo đơn hàng]
    │       ↓
    │   [Cập nhật status = ordered]
    │       ↓
    │   [End]
    └─ No → [Cập nhật status = rejected]
            ↓
        [End]
```

---

## 6. SEQUENCE DIAGRAM - QUY TRÌNH ĐẶT HÀNG

```
┌─────────────────────────────────────────────────────────────────┐
│         SEQUENCE DIAGRAM - QUY TRÌNH ĐẶT HÀNG                   │
└─────────────────────────────────────────────────────────────────┘

Customer    Browser    Server      Database    Email System
    │          │          │            │            │
    │──Xem SP──>│          │            │            │
    │          │──GET───>│            │            │
    │          │          │──SELECT───>│            │
    │          │          │<──Data─────│            │
    │          │<──HTML────│            │            │
    │<──SP──────│          │            │            │
    │          │          │            │            │
    │──Thêm GH─>│          │            │            │
    │          │──POST───>│            │            │
    │          │          │──INSERT───>│            │
    │          │          │<──Success──│            │
    │          │<──JSON────│            │            │
    │<──OK──────│          │            │            │
    │          │          │            │            │
    │──Checkout>│          │            │            │
    │          │──GET───>│            │            │
    │          │<──Form────│            │            │
    │<──Form────│          │            │            │
    │          │          │            │            │
    │──Đặt hàng>│          │            │            │
    │          │──POST───>│            │            │
    │          │          │──BEGIN TX──>│            │
    │          │          │──INSERT Order>│            │
    │          │          │──INSERT Items>│            │
    │          │          │──UPDATE Stock>│            │
    │          │          │──DELETE Cart>│            │
    │          │          │<──Success───│            │
    │          │          │──COMMIT────>│            │
    │          │          │──Send Email─>│            │
    │          │          │            │──Send──────>│
    │          │<──Success│            │            │
    │<──Success─│          │            │            │
    │          │          │            │            │
```

---

## 7. SEQUENCE DIAGRAM - QUẢN LÝ ĐƠN HÀNG (ADMIN)

```
┌─────────────────────────────────────────────────────────────────┐
│    SEQUENCE DIAGRAM - QUẢN LÝ ĐƠN HÀNG (ADMIN)                  │
└─────────────────────────────────────────────────────────────────┘

Admin       Browser    Server      Database    Email System
  │          │          │            │            │
  │──Login──>│          │            │            │
  │          │──POST───>│            │            │
  │          │          │──SELECT───>│            │
  │          │          │<──User─────│            │
  │          │          │──Verify───>│            │
  │          │<──Session│            │            │
  │<──OK──────│          │            │            │
  │          │          │            │            │
  │──Xem ĐH──>│          │            │            │
  │          │──GET───>│            │            │
  │          │          │──SELECT───>│            │
  │          │          │<──Orders───│            │
  │          │<──HTML────│            │            │
  │<──Danh sách│          │            │            │
  │          │          │            │            │
  │──Chọn ĐH─>│          │            │            │
  │          │──GET───>│            │            │
  │          │          │──SELECT───>│            │
  │          │          │<──Details──│            │
  │          │<──HTML────│            │            │
  │<──Chi tiết│          │            │            │
  │          │          │            │            │
  │──Cập nhật>│          │            │            │
  │          │──POST───>│            │            │
  │          │          │──UPDATE───>│            │
  │          │          │<──Success──│            │
  │          │          │──Send Email>│            │
  │          │          │            │──Send──────>│
  │          │<──Success│            │            │
  │<──OK──────│          │            │            │
  │          │          │            │            │
```

---

## 8. ERD - ENTITY RELATIONSHIP DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│              ERD - ENTITY RELATIONSHIP DIAGRAM                  │
│                  TEDDY BEAR SHOP SYSTEM                         │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│    users    │         │  categories │         │   products   │
├──────────────┤         ├──────────────┤         ├──────────────┤
│ id (PK)      │         │ id (PK)      │         │ id (PK)      │
│ username     │         │ name         │         │ name         │
│ email        │         │ slug         │         │ slug         │
│ password     │         │ description  │         │ description  │
│ full_name    │         │ image        │         │ price        │
│ phone        │         │ status       │         │ category_id  │◄──┐
│ address      │         │ created_at   │         │ main_image   │   │
│ role         │         └──────────────┘         │ stock_qty    │   │
│ status       │                  │               │ status       │   │
│ created_at   │                  │               │ created_at   │   │
└──────┬───────┘                  │               └──────┬───────┘   │
       │                          │                      │          │
       │                          │                      │          │
       │                          └──────────────────────┘          │
       │                                                              │
       │         ┌──────────────┐         ┌──────────────┐          │
       │         │     cart     │         │ order_items  │          │
       │         ├──────────────┤         ├──────────────┤          │
       │         │ id (PK)      │         │ id (PK)      │          │
       │         │ user_id (FK) │◄────────│ order_id (FK)│          │
       │         │ product_id   │         │ product_id   │──────────┘
       │         │ quantity     │         │ quantity     │
       │         │ created_at   │         │ price        │
       │         └──────────────┘         │ total        │
       │                                 └──────┬───────┘
       │                                        │
       │         ┌──────────────┐              │
       │         │    orders    │              │
       │         ├──────────────┤              │
       │         │ id (PK)      │◄─────────────┘
       │         │ order_number │
       │         │ user_id (FK) │◄─────────────┐
       │         │ customer_name│              │
       │         │ total_amount │              │
       │         │ order_status │              │
       │         │ payment_status│             │
       │         │ created_at   │              │
       │         └──────┬───────┘              │
       │                │                      │
       │                │                      │
       └────────────────┘                      │
                                                │
┌──────────────┐         ┌──────────────┐      │
│   reviews    │         │   coupons    │      │
├──────────────┤         ├──────────────┤      │
│ id (PK)      │         │ id (PK)      │      │
│ product_id   │─────────│ code         │      │
│ user_id      │─────────│ type         │      │
│ rating       │         │ value        │      │
│ comment      │         │ min_amount   │      │
│ status       │         │ status       │      │
│ created_at   │         │ created_at   │      │
└──────────────┘         └──────────────┘      │
                                                │
┌──────────────┐                               │
│ custom_orders│                               │
├──────────────┤                               │
│ id (PK)      │                               │
│ customer_name│                               │
│ customer_email│                              │
│ description  │                               │
│ image_url   │                               │
│ status      │                               │
│ created_at  │                               │
└──────────────┘                               │
```

---

## 9. ERD - CHI TIẾT QUAN HỆ

```
┌─────────────────────────────────────────────────────────────────┐
│              ERD - CHI TIẾT QUAN HỆ GIỮA CÁC BẢNG               │
└─────────────────────────────────────────────────────────────────┘

users (1) ────────< (N) cart
users (1) ────────< (N) orders
users (1) ────────< (N) reviews

categories (1) ────< (N) products

products (1) ──────< (N) cart
products (1) ──────< (N) order_items
products (1) ──────< (N) reviews

orders (1) ────────< (N) order_items

┌─────────────────────────────────────────────────────────────────┐
│  KÝ HIỆU:                                                       │
│  (1) = One (Một)                                                │
│  (N) = Many (Nhiều)                                             │
│  ────────< = Quan hệ một-nhiều                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. FLOWCHART - QUY TRÌNH XỬ LÝ ĐƠN HÀNG

```
┌─────────────────────────────────────────────────────────────────┐
│         FLOWCHART - QUY TRÌNH XỬ LÝ ĐƠN HÀNG                    │
└─────────────────────────────────────────────────────────────────┘

                    [Đơn hàng mới]
                         │
                         ▼
                    {Kiểm tra tồn kho}
                         │
            ┌────────────┴────────────┐
            │                         │
        [Có đủ hàng]            [Hết hàng]
            │                         │
            ▼                         ▼
    [Tạo đơn hàng]            [Thông báo hết hàng]
            │                         │
            ▼                         ▼
    [Cập nhật tồn kho]          [Kết thúc]
            │
            ▼
    [Gửi email xác nhận]
            │
            ▼
    {Phương thức thanh toán?}
            │
    ┌───────┼───────┬───────────┐
    │       │       │           │
  [COD]  [Bank]  [Momo]    [ZaloPay]
    │       │       │           │
    └───────┼───────┴───────────┘
            │
            ▼
    [Chờ thanh toán]
            │
            ▼
    {Đã thanh toán?}
            │
    ┌───────┴───────┐
    │               │
  [Có]            [Chưa]
    │               │
    ▼               ▼
[Chuẩn bị hàng]  [Nhắc nhở]
    │               │
    ▼               ▼
[Giao hàng]      [Hủy đơn]
    │               │
    ▼               ▼
[Hoàn thành]    [Kết thúc]
    │
    ▼
[Gửi email hoàn thành]
    │
    ▼
[Kết thúc]
```

---

## 11. STATE DIAGRAM - TRẠNG THÁI ĐƠN HÀNG

```
┌─────────────────────────────────────────────────────────────────┐
│         STATE DIAGRAM - TRẠNG THÁI ĐƠN HÀNG                    │
└─────────────────────────────────────────────────────────────────┘

    [PENDING]
    ─────────
    │ Đơn hàng mới
    │ Chờ xác nhận
    │
    │ [Admin xác nhận]
    ▼
    [CONFIRMED]
    ───────────
    │ Đã xác nhận
    │ Chuẩn bị hàng
    │
    │ [Bắt đầu chuẩn bị]
    ▼
    [PROCESSING]
    ────────────
    │ Đang xử lý
    │ Đóng gói
    │
    │ [Giao cho shipper]
    ▼
    [SHIPPING]
    ──────────
    │ Đang giao hàng
    │
    │ [Giao thành công]
    ▼
    [DELIVERED]
    ───────────
    │ Đã giao hàng
    │ Hoàn thành
    
    ──────────────────────────────────
    
    [CANCELLED]
    ───────────
    │ Đã hủy
    │ (Có thể hủy từ bất kỳ trạng thái nào)
```

---

## 12. COMPONENT DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPONENT DIAGRAM                            │
│                  TEDDY BEAR SHOP SYSTEM                         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   HTML/CSS   │  │ JavaScript   │  │   Bootstrap   │         │
│  │   Pages      │  │   (jQuery)   │  │   Framework   │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            │
                            │ HTTP Request/Response
                            │
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   PHP        │  │  Functions   │  │   API        │         │
│  │   Scripts    │  │   Library    │  │   Endpoints   │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            │
                            │ SQL Queries
                            │
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   MySQL      │  │   Tables     │  │   Indexes    │         │
│  │   Database   │  │   & Views    │  │   & Keys     │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            │
                            │ SMTP
                            │
┌─────────────────────────────────────────────────────────────────┐
│                      EXTERNAL SERVICES                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐                           │
│  │  PHPMailer   │  │  Payment    │                           │
│  │  (Email)     │  │  Gateways    │                           │
│  └──────────────┘  └──────────────┘                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## GHI CHÚ SỬ DỤNG

### Cách sử dụng các sơ đồ này:

1. **Copy vào báo cáo:**
   - Copy từng sơ đồ vào phần tương ứng trong báo cáo Word
   - Có thể chỉnh sửa format cho phù hợp

2. **Tạo file hình ảnh:**
   - Sử dụng tool online như: https://asciiflow.com/
   - Hoặc dùng draw.io, Lucidchart để vẽ lại

3. **Export sang PDF:**
   - Có thể export file markdown này sang PDF
   - Hoặc copy vào Word rồi export PDF

4. **Chỉnh sửa:**
   - Tất cả sơ đồ đều dạng text, dễ chỉnh sửa
   - Có thể thêm/bớt thành phần theo nhu cầu

---

**File này chứa tất cả các sơ đồ cần thiết cho báo cáo bài tập lớn.**

**Ngày tạo:** 2025-01-XX  
**Phiên bản:** 1.0

