<?php
/**
 * Header view template for the application.
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
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCsrfToken(); ?>">
    
    <!-- User Data -->
    <?php if(isset($_SESSION['user_id'])) : ?>
        <meta name="user-id" content="<?php echo $_SESSION['user_id']; ?>">
        <meta name="user-email" content="<?php echo $_SESSION['user_email']; ?>">
        <meta name="user-role" content="<?php echo $_SESSION['user_role']; ?>">
        <meta name="user-name" content="<?php echo $_SESSION['user_name']; ?>">
    <?php endif; ?>
    
    <!-- Favicon - Không emoji -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' fill='%230071e3' rx='20'/><text x='50' y='68' font-size='48' text-anchor='middle' fill='white' font-family='Arial' font-weight='bold'>C</text></svg>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css">
    <link rel="preload" href="<?php echo URLROOT; ?>/js/main.js" as="script">
    
    <!-- JavaScript Config -->
    <script>
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
    </script>
    
    <!-- JavaScript Modules -->
    <script src="<?php echo URLROOT; ?>/js/modules/utils.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/flash.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/validation.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/auth.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/profile.js"></script>
    <script src="<?php echo URLROOT; ?>/js/modules/ajax-form.js"></script>
    <script src="<?php echo URLROOT; ?>/js/main.js"></script>
</head>
<body>
    <!-- ====== NAVBAR ====== -->
    <nav class="navbar" role="navigation">
        <div class="container">
            <a class="logo" href="<?php echo URLROOT; ?>" aria-label="Trang chủ">
                <?php echo SITENAME; ?>
            </a>
            
            <button class="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span>☰</span>
            </button>
            
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
                    <li role="none" class="nav-dropdown">
                        <a href="#" role="menuitem" class="nav-user" aria-haspopup="true">
                            <span class="user-avatar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?>
                            <span class="dropdown-arrow">
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 1l4 4 4-4"/>
                                </svg>
                            </span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li role="none"><a href="<?php echo URLROOT; ?>/users/profile" role="menuitem">Hồ sơ</a></li>
                            <li role="none"><a href="<?php echo URLROOT; ?>/wallets/index" role="menuitem">Ví điện tử</a></li>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) : ?>
                                <li role="none"><a href="<?php echo URLROOT; ?>/seller/dashboard" role="menuitem">Dashboard người bán</a></li>
                            <?php endif; ?>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3) : ?>
                                <li role="none"><a href="<?php echo URLROOT; ?>/admin/dashboard" role="menuitem">Admin Panel</a></li>
                            <?php endif; ?>
                            <li role="none" class="dropdown-divider"></li>
                            <li role="none"><a href="<?php echo URLROOT; ?>/users/logout" role="menuitem" class="logout-link">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else : ?>
                    <li role="none"><a href="<?php echo URLROOT; ?>/users/login" role="menuitem" class="btn-nav btn-login">Đăng nhập</a></li>
                    <li role="none"><a href="<?php echo URLROOT; ?>/users/register" role="menuitem" class="btn-nav btn-register">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- ====== MAIN CONTENT ====== -->
    <main class="main-content" role="main">
        <div class="container">
            
            <!-- Flash Messages -->
            <?php if (function_exists('getFlash')) : ?>
                <?php 
                $successFlash = getFlash('success');
                $errorFlash = getFlash('error');
                $infoFlash = getFlash('info');
                $warningFlash = getFlash('warning');
                ?>
                
                <?php if ($successFlash) : ?>
                    <div class="alert alert-success" role="alert" data-auto-dismiss="5000">
                        <?php echo htmlspecialchars($successFlash['message']); ?>
                        <button type="button" class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorFlash) : ?>
                    <div class="alert alert-danger" role="alert" data-auto-dismiss="7000">
                        <?php echo htmlspecialchars($errorFlash['message']); ?>
                        <button type="button" class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($infoFlash) : ?>
                    <div class="alert alert-info" role="alert" data-auto-dismiss="4000">
                        <?php echo htmlspecialchars($infoFlash['message']); ?>
                        <button type="button" class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($warningFlash) : ?>
                    <div class="alert alert-warning" role="alert" data-auto-dismiss="6000">
                        <?php echo htmlspecialchars($warningFlash['message']); ?>
                        <button type="button" class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>