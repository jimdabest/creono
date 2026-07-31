<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] : SITENAME; ?></title>
    
    <!-- CHỈ GIỮ LẠI FILE STYLE.CSS CỦA BẠN -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css">
</head>
<body>
    <!-- Navbar viết bằng HTML thuần -->
    <nav class="navbar">
        <div class="container">
            <a class="logo" href="<?php echo URLROOT; ?>"><?php echo SITENAME; ?></a>
            <ul class="nav-links">
                <li><a href="<?php echo URLROOT; ?>">Trang chủ</a></li>
                <li><a href="<?php echo URLROOT; ?>/pages/about">Giới thiệu</a></li>
                <li><a href="<?php echo URLROOT; ?>/users/login">Đăng nhập</a></li>
                <li><a href="<?php echo URLROOT; ?>/users/register">Đăng ký</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">