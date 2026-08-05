<?php
class AuthMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            if (function_exists('setFlash')) {
                setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            }
            header('location: ' . URLROOT . '/users/login');
            exit();
        }
    }
}