<?php
// Bắt đầu Session cho toàn bộ ứng dụng
session_start();

// Hàm kiểm tra xem người dùng đã đăng nhập chưa
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    } else {
        return false;
    }
}