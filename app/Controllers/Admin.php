<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';

class Admin extends Controller {
    
    public function __construct() {
        RoleMiddleware::check([3]);
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