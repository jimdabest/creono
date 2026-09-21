<?php
require_once '../app/Helpers/flash_helper.php';

class RoleMiddleware {
    public static function check(array $allowed_roles): void {
        AuthMiddleware::check(); // Phải đăng nhập trước
        $userRole = (int)($_SESSION['user_role'] ?? 0);
        
        // Hỗ trợ kiểm tra quyền Admin: 
        // Nếu allowed_roles chứa [1] mà không chứa [2] (Seller),
        // cho phép cả Admin hệ thống (role = 3) và role = 1.
        $effectiveRoles = $allowed_roles;
        if (in_array(1, $allowed_roles, true) && !in_array(2, $allowed_roles, true)) {
            $effectiveRoles[] = 3;
        }

        if (!in_array($userRole, $effectiveRoles)) {
            if (function_exists('setFlash')) {
                setFlash('error', 'Bạn không có quyền truy cập trang này.');
            }
            header('location: ' . URLROOT);
            exit();
        }
    }
}