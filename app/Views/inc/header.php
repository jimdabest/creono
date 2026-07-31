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
    
    <?php if(isset($_SESSION['user_id'])) : ?>
        <!-- Hiển thị khi ĐÃ đăng nhập -->
        <li><a href="#">Chào, <?php echo $_SESSION['user_email']; ?></a></li>
        
        <!-- Nút vào Admin (chỉ hiện nếu role_id = 1) -->
    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3) : ?>
        <li><a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Panel</a></li>
    <?php endif; ?>
        
        <li><a href="<?php echo URLROOT; ?>/users/logout">Đăng xuất</a></li>
    <?php else : ?>
        <!-- Hiển thị khi CHƯA đăng nhập -->
        <li><a href="<?php echo URLROOT; ?>/users/login">Đăng nhập</a></li>
        <li><a href="<?php echo URLROOT; ?>/users/register">Đăng ký</a></li>
    <?php endif; ?>
</ul>
        </div>
    </nav>
    
    <div class="container">