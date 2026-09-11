<?php
class AuthMiddleware {
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            if (function_exists('setFlash')) {
                setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            }
            header('location: ' . URLROOT . '/users/login');
            exit();
        }

        // Kiểm tra xem tài khoản có bị khóa không
        if (class_exists('Database')) {
            try {
                $db = new Database();
                $db->query("SELECT is_locked FROM users WHERE id = :id");
                $db->bind(':id', (int)$_SESSION['user_id']);
                $user = $db->single();

                if (!$user || (!empty($user->is_locked) && (int)$user->is_locked === 1)) {
                    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['is_locked']);
                    session_regenerate_id(true);
                    if (function_exists('setFlash')) {
                        setFlash('error', 'Tài khoản của bạn đã bị khóa bởi Quản trị viên.', 'error');
                    }
                    header('location: ' . URLROOT . '/users/login');
                    exit();
                }
            } catch (Exception $e) {
                // Tiếp tục nếu có sự cố kết nối tạm thời
            }
        }
    }
}