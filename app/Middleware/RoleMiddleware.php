<?php
require_once '../app/Helpers/flash_helper.php';

class RoleMiddleware {
    public static function check(array $allowed_roles): void {
        AuthMiddleware::check(); // Phải đăng nhập trước
        if (!in_array($_SESSION['user_role'], $allowed_roles)) {
            if (function_exists('setFlash')) {
                setFlash('error', 'Bạn không có quyền truy cập trang này.');
            }
            header('location: ' . URLROOT);
            exit();
        }
    }
}