# Creono - C2C Digital Marketplace

Dự án Creono là nền tảng thương mại điện tử C2C chuyên mua bán ấn phẩm số, tài liệu. Hệ thống được phát triển dựa trên kiến trúc **MVC (Model - View - Controller) bằng PHP **, không sử dụng framework.

---

## 🛠 Hướng dẫn Cài đặt (Setup Project)

Dành cho Developer mới tham gia dự án, hãy làm theo các bước sau để chạy project ở môi trường Local (XAMPP/WAMP):

1. **Clone mã nguồn:**
```bash
git clone https://github.com/jimdabest/creono.git
```
*(Lưu ý: Clone trực tiếp vào thư mục `htdocs` của XAMPP).*

2. **Cấu hình Cơ sở dữ liệu:**
*   Mở công cụ quản trị MySQL (phpMyAdmin hoặc DBeaver).
*   Tạo một database mới tên là `creono_db` (Charset: `utf8mb4_unicode_ci`).
*   Import file `creono_db.sql` (nằm ở thư mục gốc dự án) vào database vừa tạo.

3. **Cấu hình Môi trường (Config):**
*   Vào thư mục `config/`.
*   Copy file `config.php.example` tên thành `config.php`.
*   Copy file `error.log.example` tên thành `error.log`.
*   Cập nhật thông số Database và `URLROOT` trong file `config.php` cho khớp với máy của bạn.

4. **Kiểm tra URL Rewrite (.htaccess):**
<!-- *   Hãy chắc chắn rằng XAMPP của bạn đã bật module `mod_rewrite` trong Apache (`httpd.conf`). -->
*   Truy cập trang chủ: `http://localhost/creono`. Nếu trang load thành công nghĩa là hệ thống đã hoạt động!

---

## 📜 Quy tắc Làm việc (Coding Rules)

Để mã nguồn dự án luôn sạch sẽ, dễ bảo trì và hạn chế conflict, toàn bộ team cần tuân thủ các quy định sau:

### 1. Quy tắc Đặt tên (Naming Convention)
*   **Controller:** Tên Class và tên File luôn là **Số nhiều** và viết hoa chữ cái đầu (PascalCase). VD: `Users.php`, `Products.php`, `Orders.php`.
*   **Model:** Tên Class và tên File luôn là **Số ít** (PascalCase). VD: `User.php`, `Product.php`.
*   **View:** Lưu trong thư mục viết thường, **số nhiều**. File bên trong viết thường (snake_case). VD: `app/Views/users/register.php`.

### 2. Quy tắc Thao tác Database & Giao dịch (Transactions)
*   Hạn chế tối đa việc viết lệnh SQL trực tiếp trong Controller. Hãy định nghĩa bảng (`$table`) và dùng các hàm có sẵn của `BaseModel` (`findAll`, `findById`, `create`, `update`, `delete`).
*   **Cực kỳ quan trọng đối với dữ liệu tài chính:** Sàn Creono liên tục xử lý thanh toán (Escrow), phân chia tiền và cập nhật trạng thái. Khi code các chức năng này, **bắt buộc** phải bọc logic trong Transaction để đảm bảo tính nhất quán dữ liệu:
```php
$this->db->beginTransaction();
try {
    // ... các lệnh update ...
    $this->db->commit();
} catch(Exception $e) {
    $this->db->rollBack();
}
```

### 3. Quy tắc Git Flow
Để đảm bảo code không bị conflict và luôn được review trước khi gộp vào nhánh chính, toàn bộ team tuân thủ quy trình tạo Pull Request sau đây:

**Bước 1: Cập nhật code mới nhất từ nhánh `main`**
Trước khi làm task mới, luôn phải lấy code mới nhất về máy để tránh lỗi cũ:
```bash
git checkout main
git pull origin main
```
**Bước 2: Tạo nhánh làm việc riêng (Branch)**
Tuyệt đối không code trực tiếp trên nhánh main. Hãy tạo nhánh mới với cú pháp:
Chức năng mới: ```feature/ten-chuc-nang```
Sửa lỗi: ```bugfix/ten-loi```

VD:
```bash
git checkout -b feature/user-login
```

**Bước 3: Code và Commit thay đổi**
Sau khi hoàn thành code chức năng, hãy thêm các thay đổi và commit với một thông điệp rõ ràng:
```bash
git add .
# Cú pháp commit chuẩn: [Loại] Thông điệp (Ví dụ: feat, fix, docs, refactor)
git commit -m "feat: hoàn thiện giao diện và logic đăng nhập"
```

**Bước 4: Đẩy nhánh lên GitHub (Push)**
Đẩy nhánh bạn vừa tạo lên repository remote:
```bash
git push -u origin feature/user-login
```

**Bước 5: Tạo Pull Request (Trên giao diện GitHub)**
```
1. Mở trang GitHub của dự án lên.

2. Bạn sẽ thấy một thông báo màu vàng gợi ý tạo PR cho nhánh vừa push, click vào nút "Compare & pull request".

3. Tiêu đề PR: Viết ngắn gọn, ví dụ: [Feature] Thêm tính năng đăng nhập.

4. Nội dung PR (Mô tả): Ghi rõ các mục sau để người review dễ đọc:

    Task này làm gì? (Ví dụ: Xử lý UI form login, băm mật khẩu, tạo Session).

    Có cần chạy lệnh SQL mới nào không? (Nếu có thay đổi database, nhớ ghi rõ để người review biết).

5. Click "Create pull request".
```
---

## 👨‍💻 Ví dụ Quy trình Phát triển: "Chức năng Tạo Danh Mục (Category)"

Khi được giao task tạo một chức năng CRUD, hãy thực hiện theo trình tự 3 bước (Model -> Controller -> View):

### Bước 1: Tạo Model (`app/Models/Category.php`)
Vì đã có `BaseModel`, bạn chỉ cần khai báo tên bảng. Model đã tự động có sẵn hàm Thêm/Sửa/Xóa.
```php
<?php
class Category extends BaseModel {
    // Chỉ định bảng database
    protected $table = 'categories';

    // (Tùy chọn) Hàm lấy danh mục theo trạng thái
    public function getActiveCategories() {
        $this->db->query("SELECT * FROM {$this->table} WHERE status = 'Active'");
        return $this->db->resultSet();
    }
}
```

### Bước 2: Tạo Controller (`app/Controllers/Categories.php`)
Điều hướng và xử lý logic (Nhận dữ liệu từ form, kiểm tra lỗi, gọi Model).
```php
<?php
class Categories extends Controller {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = $this->model('Category');
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Nhận và lọc dữ liệu
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $data = [
                'name' => trim($_POST['name']),
                'name_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Vui lòng nhập tên danh mục';
            }

            // Gọi BaseModel để insert
            if (empty($data['name_err'])) {
                $insertData = [
                    'id' => uuid_generate(), // Hàm tự sinh trong helper
                    'name' => $data['name']
                ];
                
                if($this->categoryModel->create($insertData)) {
                    header('location: ' . URLROOT . '/categories/index');
                }
            } else {
                $this->view('categories/create', $data); // Load lại form báo lỗi
            }
        } else {
            // Load form trắng (GET)
            $data = ['name' => '', 'name_err' => ''];
            $this->view('categories/create', $data);
        }
    }
}
```

### Bước 3: Tạo View (`app/Views/categories/create.php`)
Tạo giao diện hiển thị form và nạp layout Header/Footer.
```php
<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card">
    <h2>Thêm Danh Mục Mới</h2>
    <form action="<?php echo URLROOT; ?>/categories/create" method="POST">
        <div class="form-group">
            <label>Tên danh mục:</label>
            <input type="text" name="name" value="<?php echo $data['name']; ?>">
            <span class="error-text"><?php echo $data['name_err']; ?></span>
        </div>
        <input type="submit" value="Lưu dữ liệu" class="btn">
    </form>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>
```