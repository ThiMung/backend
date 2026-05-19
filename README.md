# Community Event Platform - Backend API (Laravel 13.8.0)

Hệ thống API quản lý nền tảng sự kiện cộng đồng, được xây dựng dựa trên framework **Laravel 13.8.0**, tích hợp xác thực bảo mật **Laravel Sanctum** và quản lý phân quyền chặt chẽ bằng Middleware tùy biến.

## 🛠️ Công Nghệ Sử Dụng
- **Framework Core**: Laravel 13.8.0 & PHP 8.3+
- **Authentication**: Laravel Sanctum (Quản lý Token lưu trữ tập trung tại bảng `personal_access_tokens`)
- **Database**: MySQL
- **Kiến trúc code**: Logic xử lý trực tiếp trong Controller

---

## 📁 Cấu Trúc Thư Mục Hệ Thống
```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Xử lý Đăng ký, Đăng nhập, Đăng xuất Sanctum
│   │   │   ├── EventController.php         # Quản lý vòng đời Sự kiện (Draft -> Published -> ...)
│   │   │   ├── RegistrationController.php  # Đăng ký tham gia & Xử lý hàng đợi Waitlist
│   │   │   └── ReviewController.php        # Quản lý đánh giá từ Attendee
│   │   └── Middleware/
│   │       └── CheckRole.php               # Middleware phân quyền 'organizer' & 'attendee'
│   └── Models/
│       ├── User.php
│       ├── Event.php
│       ├── Registration.php
│       └── Review.php
├── database/
│   └── migrations/                         # Thiết lập cấu trúc 5 bảng cốt lõi
└── routes/
    └── api.php                             # Định tuyến hệ thống API Endpoint công khai & bảo vệ