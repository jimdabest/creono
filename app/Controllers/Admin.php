<?php
class Admin extends Controller {
    
    public function __construct() {
    // 1. Kiểm tra xem user đã đăng nhập chưa bằng cách check user_id
    if (!isset($_SESSION['user_id'])) {
        // Chưa đăng nhập thì đá về trang Login
        header('location: ' . URLROOT . '/users/login');
        exit();
    }
    
    // 2. Kiểm tra xem user có phải là Admin (role = 3) không
    if ($_SESSION['user_role'] != 3) {
        // Nếu đã đăng nhập nhưng KHÔNG phải Admin, đá văng về trang chủ
        header('location: ' . URLROOT);
        exit();
    }
    
    // Nếu lọt qua được 2 ải trên, chính thức được công nhận là Admin!
    // $this->userModel = $this->model('User');
}

    // Chức năng UC39: Dashboard Admin
    public function dashboard() {
        $data = [
            'title' => 'Tổng quan Quản trị viên'
        ];
        
        // Nhớ tạo thư mục app/Views/admin/ và file dashboard.php nhé
        $this->view('admin/dashboard', $data);
    }
}