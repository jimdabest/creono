<?php

// ============================================================
// CẤU HÌNH SESSION COOKIE AN TOÀN (THÊM MỚI)
// ============================================================

// Chỉ cấu hình nếu session chưa được bắt đầu
if (session_status() === PHP_SESSION_NONE) {
    // Xác định xem có đang dùng HTTPS không
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

    // Cấu hình cookie parameters (hỗ trợ PHP 7.3+)
    session_set_cookie_params([
        'lifetime' => 0,          // Session hết hạn khi đóng trình duyệt
        'path'     => '/',        // Áp dụng cho toàn bộ domain
        'domain'   => '',         // Domain hiện tại (tự động)
        'secure'   => $secure,    // Chỉ gửi cookie qua HTTPS nếu có
        'httponly' => true,       // Không cho JavaScript truy cập cookie
        'samesite' => 'Lax'       // Bảo vệ CSRF (Lax cho cân bằng UX/security)
    ]);

    // Bắt đầu session
    session_start();
}

// Hàm kiểm tra xem người dùng đã đăng nhập chưa
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    } else {
        return false;
    }
}