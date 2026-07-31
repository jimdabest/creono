<?php
class Admin extends Controller {
    
    public function __construct() {
        // 1. Kiểm tra xem user đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT . '/users/login');
            exit();
        }
        
        // 2. Kiểm tra xem user có phải là Admin (role_id = 1) không
        if ($_SESSION['user_role'] != 1) {
            // Nếu không phải Admin, đá văng về trang chủ
            header('location: ' . URLROOT);
            exit();
        }
        
        // Nếu qua được 2 ải trên, có thể nạp các Model cần thiết cho Admin
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