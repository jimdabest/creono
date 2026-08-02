<?php
/**
 * Header Template cho Creono Project
 * Sử dụng Vanilla JS, không phụ thuộc thư viện
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] : SITENAME; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo isset($data['description']) ? $data['description'] : 'Creono - Nền tảng mua bán tài liệu số C2C hàng đầu.'; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- CSRF Token cho JavaScript -->
    <meta name="csrf-token" content="<?php echo generateCsrfToken(); ?>">
    
    <!-- User Data cho JavaScript -->
    <?php if(isset($_SESSION['user_id'])) : ?>
        <meta name="user-id" content="<?php echo $_SESSION['user_id']; ?>">
        <meta name="user-email" content="<?php echo $_SESSION['user_email']; ?>">
        <meta name="user-role" content="<?php echo $_SESSION['user_role']; ?>">
        <meta name="user-name" content="<?php echo $_SESSION['user_name']; ?>">
    <?php endif; ?>
    
    <!-- Favicon (placeholder) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css">
    
    <!-- Preload các file quan trọng -->
    <link rel="preload" href="<?php echo URLROOT; ?>/js/main.js" as="script">
    
    <!-- Base URL cho JavaScript -->
    <script>
        // Global configuration cho JavaScript
        window.CREONO = {
            URLROOT: '<?php echo URLROOT; ?>',
            SITENAME: '<?php echo SITENAME; ?>',
            CSRF_TOKEN: '<?php echo generateCsrfToken(); ?>',
            USER: <?php echo isset($_SESSION['user_id']) ? json_encode([
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['user_role'] ?? 1
            ]) : 'null'; ?>
        };
        
        console.log('🚀 Creono loaded with Vanilla JS');
        console.log('📦 Environment:', {
            URLROOT: window.CREONO.URLROOT,
            SITENAME: window.CREONO.SITENAME,
            USER: window.CREONO.USER ? 'Authenticated' : 'Guest'
        });
    </script>
    
    <!-- JavaScript Modules - Load ở header để sẵn sàng -->
    <!-- Các module được load theo thứ tự: utils -> các module khác -->
    <script src="<?php echo URLROOT; ?>/js/modules/utils.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/flash.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/validation.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/auth.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/profile.js"></script>
    
    <!-- ====== MODULE MỚI: AJAX Form ====== -->
    <script src="<?php echo URLROOT; ?>/js/modules/ajax-form.js"></script>
    
    <!-- Main JavaScript -->
    <script src="<?php echo URLROOT; ?>/js/main.js"></script>
</head>
<body>
    <!-- ====================== NAVIGATION BAR ====================== -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="container">
            <!-- Logo / Brand -->
            <a class="logo" href="<?php echo URLROOT; ?>" aria-label="Trang chủ">
                <?php echo SITENAME; ?>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="navbar-toggle-icon">☰</span>
            </button>
            
            <!-- Navigation Links -->
            <ul class="nav-links" role="menubar">
                <li role="none">
                    <a href="<?php echo URLROOT; ?>" role="menuitem">Trang chủ</a>
                </li>
                <li role="none">
                    <a href="<?php echo URLROOT; ?>/pages/about" role="menuitem">Giới thiệu</a>
                </li>
                <li role="none">
                    <a href="<?php echo URLROOT; ?>/products/index" role="menuitem">Chợ tài liệu</a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])) : ?>
                    <!-- ====== ĐÃ ĐĂNG NHẬP ====== -->
                    <li role="none" class="nav-dropdown">
                        <a href="#" role="menuitem" class="nav-user" aria-haspopup="true" aria-expanded="false">
                            <span class="user-avatar">👤</span>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-label="User menu">
                            <li role="none">
                                <a href="<?php echo URLROOT; ?>/users/profile" role="menuitem">
                                    👤 Hồ sơ
                                </a>
                            </li>
                            <li role="none">
                                <a href="<?php echo URLROOT; ?>/wallets/index" role="menuitem">
                                    💰 Ví điện tử
                                </a>
                            </li>
                            
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) : ?>
                                <!-- Seller Dashboard -->
                                <li role="none">
                                    <a href="<?php echo URLROOT; ?>/seller/dashboard" role="menuitem">
                                        🏪 Dashboard người bán
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3) : ?>
                                <!-- Admin Dashboard -->
                                <li role="none">
                                    <a href="<?php echo URLROOT; ?>/admin/dashboard" role="menuitem">
                                        ⚙️ Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <li role="none" class="dropdown-divider"></li>
                            <li role="none">
                                <a href="<?php echo URLROOT; ?>/users/logout" role="menuitem" class="logout-link">
                                    🚪 Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php else : ?>
                    <!-- ====== CHƯA ĐĂNG NHẬP ====== -->
                    <li role="none">
                        <a href="<?php echo URLROOT; ?>/users/login" role="menuitem" class="btn-nav btn-login">
                            Đăng nhập
                        </a>
                    </li>
                    <li role="none">
                        <a href="<?php echo URLROOT; ?>/users/register" role="menuitem" class="btn-nav btn-register">
                            Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- ====================== MAIN CONTENT WRAPPER ====================== -->
    <main class="main-content" role="main">
        <div class="container">
            
            <!-- ====================== FLASH MESSAGES (PHP) ====================== -->
            <!-- VẪN GIỮ LẠI CHO CÁC TRANG KHÔNG DÙNG AJAX -->
            <?php if (function_exists('getFlash')) : ?>
                <?php 
                $successFlash = getFlash('success');
                $errorFlash = getFlash('error');
                $infoFlash = getFlash('info');
                $warningFlash = getFlash('warning');
                ?>
                
                <?php if ($successFlash) : ?>
                    <div class="alert alert-success" role="alert" data-auto-dismiss="5000">
                        <span class="alert-icon">✅</span>
                        <?php echo htmlspecialchars($successFlash['message']); ?>
                        <button type="button" class="alert-close" aria-label="Đóng thông báo">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorFlash) : ?>
                    <div class="alert alert-danger" role="alert" data-auto-dismiss="7000">
                        <span class="alert-icon">❌</span>
                        <?php echo htmlspecialchars($errorFlash['message']); ?>
                        <button type="button" class="alert-close" aria-label="Đóng thông báo">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($infoFlash) : ?>
                    <div class="alert alert-info" role="alert" data-auto-dismiss="4000">
                        <span class="alert-icon">ℹ️</span>
                        <?php echo htmlspecialchars($infoFlash['message']); ?>
                        <button type="button" class="alert-close" aria-label="Đóng thông báo">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($warningFlash) : ?>
                    <div class="alert alert-warning" role="alert" data-auto-dismiss="6000">
                        <span class="alert-icon">⚠️</span>
                        <?php echo htmlspecialchars($warningFlash['message']); ?>
                        <button type="button" class="alert-close" aria-label="Đóng thông báo">&times;</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- ====================== PAGE CONTENT ====================== -->
            <!-- Nội dung của từng trang sẽ được chèn vào đây -->