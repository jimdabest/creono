# Creono - Nền tảng tài liệu số C2C

![Creono](https://img.shields.io/badge/Creono-Digital%20Marketplace-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1)
![License](https://img.shields.io/badge/License-MIT-green)

## 📖 Giới thiệu dự án

**Creono** là một nền tảng thương mại điện tử C2C (Consumer-to-Consumer) chuyên về **tài liệu số**, cho phép người dùng mua bán, chia sẻ và trao đổi các sản phẩm số như:

- 📄 Tài liệu học tập, giáo trình, luận văn
- 💻 Source code, template, theme
- 🎨 Thiết kế đồ họa, UI/UX kit
- 📊 Biểu mẫu, báo cáo, tài liệu doanh nghiệp
- 🎓 Khóa học, video hướng dẫn

### ✨ Tính năng nổi bật

| Nhóm chức năng | Mô tả |
|---|---|
| **Người dùng** | Đăng ký, đăng nhập, xác minh KYC, quản lý hồ sơ cá nhân, ví điện tử |
| **Người bán** | Đăng ký mở cửa hàng, đăng tải sản phẩm, quản lý đơn hàng, thống kê doanh thu |
| **Người mua** | Tìm kiếm, mua sắm, yêu thích, đánh giá, khiếu nại, tải tài liệu |
| **Quản trị viên** | Duyệt cửa hàng, kiểm duyệt sản phẩm, quản lý người dùng, xử lý báo cáo, cấu hình hệ thống |
| **AI Detection** | Phát hiện nội dung do AI tạo ra (ChatGPT, Claude, Gemini), hỗ trợ kháng cáo |
| **Watermark** | Tự động đóng dấu bản quyền lên PDF/Ảnh, giới hạn xem trước 2 trang đầu |
| **Báo cáo & Tố cáo** | Khiếu nại sản phẩm, tố cáo đạo nhái, báo cáo vi phạm |
| **Hoàn tiền** | Xử lý hoàn tiền trong vòng 7 ngày với giao dịch ACID an toàn |

### 🏗️ Kiến trúc dự án

```
creono/
├── app/
│   ├── Controllers/        # Xử lý logic request/response
│   ├── Core/               # App, BaseModel, Controller, Database, Validator
│   ├── Helpers/            # CSRF, Flash, Mail, Session, Watermark
│   ├── Middleware/         # Auth, Guest, Role
│   ├── Models/             # Tương tác database
│   ├── Services/           # AiDetectionService, RefundService
│   └── Views/              # Giao diện (admin, seller, buyer)
├── config/                 # Cấu hình hệ thống
├── libs/                   # Thư viện bên thứ ba (PHPMailer, FPDF, FPDI)
├── public/                 # Document root
│   ├── css/                # Stylesheet (Apple-inspired UI)
│   ├── js/                 # JavaScript modules
│   ├── uploads/            # File upload (tài liệu, ảnh)
│   └── index.php           # Entry point
└── README.md
```

---

## 🚀 Hướng dẫn chạy dự án

### 📋 Yêu cầu hệ thống

- **PHP** >= 7.4 (khuyến nghị 8.0+)
- **MySQL** >= 5.7 hoặc **MariaDB** >= 10.3
- **Composer** (tùy chọn, nếu dùng thư viện qua Composer)
- **PHP Extensions**:
  - `pdo_mysql`
  - `mbstring`
  - `gd` (xử lý watermark ảnh)
  - `openssl`
  - `curl`
  - `fileinfo`

### 📥 Bước 1: Clone dự án

```bash
git clone https://github.com/your-username/creono.git
cd creono
```

### ⚙️ Bước 2: Cấu hình môi trường

#### 2.1. Tạo file cấu hình từ file example

Dự án sử dụng các file `*.example` làm mẫu. Bạn cần **copy và xóa đuôi `.example`** để tạo file cấu hình thực tế:

**Windows (CMD/PowerShell):**
```cmd
copy config\config.php.example config\config.php
copy config\email.php.example config\email.php
```

**Linux/macOS:**
```bash
cp config/config.php.example config/config.php
cp config/email.php.example config/email.php
```

> ⚠️ **Lưu ý:** Sau khi copy, **xóa đuôi `.example`** trong tên file để hệ thống nhận diện đúng file cấu hình. File `.example` chỉ là mẫu, hệ thống không đọc file này.

#### 2.2. Cấu hình Database

Mở file `config/config.php` và cập nhật thông tin:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Tên user MySQL
define('DB_PASS', '');                // Mật khẩu MySQL
define('DB_NAME', 'creono_db');       // Tên database

// URL gốc của dự án (tự động nhận diện)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $host;
define('URLROOT', $url . '/creono');

define('APPROOT', dirname(dirname(__FILE__)) . '/app');
define('SITENAME', 'Creono');
define('APP_ENV', 'development');     // 'development' hoặc 'production'

// Upload config
define('UPLOAD_MAX_SIZE', 5242880);   // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip']);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
```

#### 2.3. Cấu hình Email (SMTP)

Mở file `config/email.php` (đã tạo từ `email.php.example`) và cập nhật:

```php
<?php
// Cấu hình SMTP - Ví dụ với Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');    // App Password, không phải mật khẩu Gmail
define('SMTP_ENCRYPTION', 'tls');                // 'tls' hoặc 'ssl'
define('SMTP_FROM_EMAIL', 'no-reply@creono.vn');
define('SMTP_FROM_NAME', 'Creono');
```

> 💡 **Hướng dẫn tạo App Password Gmail:**
> 1. Truy cập [Google Account Security](https://myaccount.google.com/security)
> 2. Bật **2-Step Verification**
> 3. Vào **App passwords** → Tạo mật khẩu mới cho "Mail"
> 4. Copy mật khẩu 16 ký tự vào `SMTP_PASSWORD`

### 🗄️ Bước 3: Tạo Database

#### 3.1. Tạo database

```sql
CREATE DATABASE creono_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3.2. Import schema

```bash
mysql -u root -p creono_db < database/creono_db.sql
```

Hoặc import qua **phpMyAdmin**:
1. Truy cập `http://localhost/phpmyadmin`
2. Chọn database `creono_db`
3. Vào tab **Import** → Chọn file `.sql` → **Go**

> 📌 **Lưu ý:** Đảm bảo database có charset `utf8mb4` để hỗ trợ tiếng Việt và emoji.

### 📁 Bước 4: Phân quyền thư mục

**Linux/macOS:**
```bash
chmod -R 755 public/uploads
chmod -R 755 logs
```

**Windows:** Không cần thiết, nhưng đảm bảo thư mục `public/uploads/` có quyền ghi.

Tạo các thư mục cần thiết nếu chưa có:

```bash
mkdir -p public/uploads/products
mkdir -p public/uploads/stores/documents
mkdir -p public/uploads/avatars
mkdir -p public/uploads/cache/previews
mkdir -p logs
```

### 🌐 Bước 5: Cấu hình Web Server

#### 5.1. Apache (XAMPP/WAMP/Laragon)

**Cách 1: Sử dụng Virtual Host (khuyến nghị)**

Mở file `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName creono.local
    DocumentRoot "C:/xampp/htdocs/creono/public"
    
    <Directory "C:/xampp/htdocs/creono/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Thêm vào file `hosts` (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1    creono.local
```

**Cách 2: Chạy trực tiếp**

Truy cập: `http://localhost/creono/public`

> ⚠️ **Lưu ý:** Document root phải trỏ vào thư mục `public/`, không phải thư mục gốc dự án.

#### 5.2. PHP Built-in Server (nhanh nhất để test)

```bash
cd public
php -S localhost:8000
```

Truy cập: `http://localhost:8000`

> ⚠️ **Lưu ý:** PHP built-in server không xử lý `.htaccess`, cần điều chỉnh URL trong `config.php`:
> ```php
> define('URLROOT', 'http://localhost:8000');
> ```

### ✅ Bước 6: Kiểm tra và chạy

1. Mở trình duyệt: `http://localhost/creono` (hoặc URL đã cấu hình)
2. Trang chủ hiển thị → **Thành công!**
3. Đăng nhập với tài khoản Admin mặc định (nếu có trong file SQL):
   - Email: `admin@creono.vn`
   - Password: `admin123`

> 🔒 **Quan trọng:** Đổi mật khẩu Admin ngay sau lần đăng nhập đầu tiên!

---

## 🐛 Xử lý lỗi thường gặp

| Lỗi | Nguyên nhân | Cách khắc phục |
|---|---|---|
| `Lỗi kết nối DB` | Sai thông tin DB trong `config.php` | Kiểm tra lại `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` |
| `404 Not Found` | Chưa bật `mod_rewrite` | Bật module rewrite trong Apache, kiểm tra `.htaccess` |
| `CSRF token validation failed` | Session chưa khởi tạo hoặc token hết hạn | Kiểm tra `session_helper.php`, đảm bảo session được start |
| `Permission denied` khi upload | Thư mục `uploads/` không có quyền ghi | `chmod -R 755 public/uploads` |
| Email không gửi được | Sai cấu hình SMTP | Kiểm tra `email.php`, dùng App Password Gmail |
| Watermark PDF lỗi | Thiếu thư viện FPDI/FPDF | Chạy `composer install` hoặc tải thủ công vào `libs/` |
| Lỗi font tiếng Việt | Thiếu font TTF | Đảm bảo có font `arial.ttf` hoặc `DejaVuSans-Bold.ttf` |

---

## 🔧 Các file cấu hình cần tạo

Dưới đây là danh sách các file `.example` cần **copy và xóa đuôi `.example`**:

| File mẫu | File cần tạo | Mục đích |
|---|---|---|
| `config/config.php.example` | `config/config.php` | Cấu hình database, URL, môi trường |
| `config/email.php.example` | `config/email.php` | Cấu hình SMTP gửi email |

### Script tự động tạo file config

**Windows (PowerShell):**
```powershell
Copy-Item config\config.php.example config\config.php
Copy-Item config\email.php.example config\email.php
```

**Linux/macOS:**
```bash
#!/bin/bash
cp config/config.php.example config/config.php
cp config/email.php.example config/email.php
echo "✅ Đã tạo file config thành công!"
```

---

## 📚 Tài liệu tham khảo

- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)
- [FPDF Documentation](http://www.fpdf.org/)
- [FPDI Documentation](https://www.setasign.com/products/fpdi/)
- [PDF.js](https://mozilla.github.io/pdf.js/) - Render PDF preview

---
