<?php
class Pages extends Controller {
    
    public function __construct() {
        // Nếu cần load Model nào đó ngay khi vào trang chủ thì gọi ở đây
        // Ví dụ: $this->productModel = $this->model('Product');
    }

    // Hàm index() là hàm mặc định được gọi khi người dùng truy cập trang chủ (URL rỗng)
    public function index() {
        // Chuẩn bị dữ liệu để gửi ra View
        $data = [
            'title' => 'Chào mừng đến với Creono',
            'description' => 'Nền tảng mua bán tài liệu số C2C hàng đầu.'
        ];

        // Gọi View 'pages/index' và truyền biến $data vào
        $this->view('pages/index', $data);
    }

    // Một ví dụ về trang phụ (vd: truy cập http://localhost/creono_project/pages/about)
    public function about() {
        $data = [
            'title' => 'Về chúng tôi'
        ];
        $this->view('pages/about', $data);
    }
    // app/Controllers/Pages.php
    public function js_test() {
        $data = [
            'title' => 'JavaScript Test'
        ];
        $this->view('pages/js_test', $data);
    }
}