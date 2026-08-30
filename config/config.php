<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Tên user MySQL của bạn
define('DB_PASS', '');            
define('DB_NAME', 'creono_db');  
// Tự động lấy URL hiện tại
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $host;
define('URLROOT', $url . '/creono');
define('APPROOT', dirname(dirname(__FILE__)) . '/app');
// define('URLROOT', 'http://localhost/creono');
define('SITENAME', 'Creono');

define('APP_ENV', 'development'); // 'development' hoặc 'production'

define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip']);



// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>