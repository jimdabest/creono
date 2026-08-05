<?php
class GuestMiddleware {
    public static function check() {
        if (isset($_SESSION['user_id'])) {
            header('location: ' . URLROOT);
            exit();
        }
    }
}