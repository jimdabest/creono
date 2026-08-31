<?php

/**
 * Header view template for the application.
 */
?>
<!DOCTYPE html>
<html lang="vi">
<!-- Dán đoạn này ngay trước thẻ </head> -->
<style>
    /* ============================================
           1. GIAO DIỆN DESKTOP (Màn hình lớn hơn 735px)
           ============================================ */
    @media screen and (min-width: 736px) {

        /* Ẩn chữ */
        .nav-cart-link .cart-text-mobile,
        .nav-fav-link .fav-text-mobile {
            display: none !important;
        }

        /* Hiện icon */
        .nav-cart-link .cart-icon-desktop,
        .nav-fav-link .fav-icon-desktop {
            display: block !important;
        }
    }

    /* ============================================
           2. GIAO DIỆN MOBILE (Màn hình từ 735px trở xuống)
           ============================================ */
    @media screen and (max-width: 735px) {

        /* Ẩn icon */
        .nav-cart-link .cart-icon-desktop,
        .nav-fav-link .fav-icon-desktop {
            display: none !important;
        }

        /* Hiện chữ */
        .nav-cart-link .cart-text-mobile,
        .nav-fav-link .fav-text-mobile {
            display: inline-block !important;
            font-size: 16px;
            font-weight: 400;
            color: var(--apple-gray);
        }

        /* Chỉnh lại form nút cho Mobile */
        .nav-cart-link,
        .nav-fav-link {
            padding: 10px 22px !important;
            gap: 8px !important;
            width: 100% !important;
            justify-content: flex-start !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        /* Fix vị trí badge số lượng trên Mobile */
        .nav-cart-link #nav-cart-badge {
            margin-left: auto !important;
            position: relative !important;
            top: auto !important;
            right: auto !important;
        }
    }
</style>

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
    <?php if (isset($_SESSION['user_id'])) : ?>
        <meta name="user-id" content="<?php echo $_SESSION['user_id']; ?>">
        <meta name="user-email" content="<?php echo $_SESSION['user_email']; ?>">
        <meta name="user-role" content="<?php echo $_SESSION['user_role']; ?>">
        <meta name="user-name" content="<?php echo $_SESSION['user_name']; ?>">
    <?php endif; ?>

    <!-- Favicon - Không emoji -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' fill='%230071e3' rx='20'/><text x='50' y='68' font-size='48' text-anchor='middle' fill='white' font-family='Arial' font-weight='bold'>C</text></svg>">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css?v=<?php echo time(); ?>">
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

                <?php if (isset($_SESSION['user_id'])) : ?>
                    <!-- Favorites: Desktop icon + Mobile text -->
                    <li role="none">
                        <a href="<?php echo URLROOT; ?>/favorites/index" role="menuitem" class="nav-fav-link" aria-label="Yêu thích" title="Sản phẩm yêu thích" style="display: flex; align-items: center; gap: 4px; padding: 6px; position: relative; text-decoration: none; color: var(--apple-gray);">
                            <!-- Icon desktop -->
                            <svg class="fav-icon-desktop" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            <!-- Chữ mobile -->
                            <span class="fav-text-mobile">Yêu thích</span>
                        </a>
                    </li>

                    <!-- Cart: Desktop icon + Mobile text -->
                    <li role="none">
                        <a href="<?php echo URLROOT; ?>/carts/index" role="menuitem" class="nav-cart-link" aria-label="Giỏ hàng" title="Giỏ hàng" style="display: flex; align-items: center; gap: 4px; padding: 6px; position: relative; text-decoration: none; color: var(--apple-gray);">
                            <!-- Icon desktop -->
                            <svg class="cart-icon-desktop" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            <!-- Chữ mobile -->
                            <span class="cart-text-mobile">Giỏ hàng</span>
                            <!-- Badge -->
                            <span id="nav-cart-badge" style="display: none; background: #ff3b30; color: #fff; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; align-items: center; justify-content: center; margin-left: 2px;">0</span>
                        </a>
                    </li>

                    <li role="none" class="nav-dropdown">
                        <a href="#" role="menuitem" class="nav-user" aria-haspopup="true">
                            <span class="user-avatar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?>
                            <span class="dropdown-arrow">
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 1l4 4 4-4" />
                                </svg>
                            </span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li role="none"><a href="<?php echo URLROOT; ?>/users/profile" role="menuitem">Hồ sơ</a></li>
                            <li role="none"><a href="<?php echo URLROOT; ?>/wallets/index" role="menuitem">Ví điện tử</a></li>
                            <li role="none"><a href="<?php echo URLROOT; ?>/orders/myPurchases" role="menuitem">Kho tài liệu của tôi</a></li>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) : ?>
                                <li role="none"><a href="<?php echo URLROOT; ?>/seller/dashboard" role="menuitem">Dashboard người bán</a></li>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3) : ?>
                                <li role="none"><a href="<?php echo URLROOT; ?>/admin/dashboard" role="menuitem">Admin Panel</a></li>
                            <?php endif; ?>
                            <li role="none" class="dropdown-divider"></li>
                            <li role="none"><a href="<?php echo URLROOT; ?>/users/logout" role="menuitem" class="logout-link">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else : ?>
                    <!-- Guest Cart: Desktop icon + Mobile text -->
                    <li role="none">
                        <a href="<?php echo URLROOT; ?>/carts/index" role="menuitem" class="nav-cart-link" aria-label="Giỏ hàng" title="Giỏ hàng" style="display: flex; align-items: center; gap: 4px; padding: 6px; position: relative; text-decoration: none; color: var(--apple-gray);">
                            <!-- Icon desktop -->
                            <svg class="cart-icon-desktop" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            <!-- Chữ mobile -->
                            <span class="cart-text-mobile">Giỏ hàng</span>
                            <!-- Badge -->
                            <span id="nav-cart-badge" style="display: none; background: #ff3b30; color: #fff; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; align-items: center; justify-content: center; margin-left: 2px;">0</span>
                        </a>
                    </li>

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