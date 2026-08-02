// app/Controllers/Seller.php
<?php
require_once '../app/Middleware/AuthMiddleware.php';
require_once '../app/Middleware/RoleMiddleware.php';

class Seller extends Controller {
    public function __construct() {
        RoleMiddleware::check([2]); // Chỉ Seller (role=2)
    }

    public function dashboard() {
        $data = [
            'title' => 'Dashboard Người bán',
            'user' => $this->model('User')->getUserWithProfile($_SESSION['user_id'])
        ];
        $this->view('seller/dashboard', $data);
    }
}