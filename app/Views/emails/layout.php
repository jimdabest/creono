<?php
/**
 * Email Layout dùng chung cho toàn hệ thống Creono
 * Các biến truyền vào:
 * @var string $emailTitle      Tiêu đề chính (VD: "Xin chào,")
 * @var string $emailContent    Nội dung chính (HTML)
 * @var string $ctaText         Chữ trên nút bấm (tùy chọn)
 * @var string $ctaLink         Link của nút bấm (tùy chọn)
 * @var string $footerNote      Ghi chú cuối email (tùy chọn)
 */
$emailTitle   = $emailTitle   ?? 'Xin chào,';
$emailContent = $emailContent ?? '';
$ctaText      = $ctaText      ?? '';
$ctaLink      = $ctaLink      ?? '';
$footerNote   = $footerNote   ?? 'Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($emailTitle) ?></title>
    <style>
        body {
            font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f7;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.06);
            border: 1px solid #d2d2d7;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #1d1d1f;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }
        .logo span {
            background: linear-gradient(135deg, #0071e3 0%, #30c5ba 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        h2 {
            font-size: 26px;
            font-weight: 600;
            color: #1d1d1f;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 17px;
            line-height: 1.5;
            color: #555;
            margin: 0 0 20px;
        }
        .btn {
            display: inline-block;
            background: #0071e3;
            color: #ffffff !important;
            font-size: 17px;
            font-weight: 500;
            padding: 14px 28px;
            border-radius: 980px;
            text-decoration: none;
            margin: 8px 0 20px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0077ed;
        }
        .link-fallback {
            font-size: 14px;
            color: #86868b;
            word-break: break-all;
            background: #f5f5f7;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 12px 0 0;
        }
        .footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #d2d2d7;
            font-size: 13px;
            color: #86868b;
            text-align: center;
        }
        .footer a {
            color: #0071e3;
            text-decoration: none;
        }
        strong {
            color: #1d1d1f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo"><span>Creono</span></div>

        <h2><?= htmlspecialchars($emailTitle) ?></h2>

        <div><?= $emailContent ?></div>

        <?php if (!empty($ctaText) && !empty($ctaLink)): ?>
            <a href="<?= htmlspecialchars($ctaLink) ?>" class="btn">
                <?= htmlspecialchars($ctaText) ?>
            </a>
            <p style="font-size:15px; color:#86868b;">Hoặc copy link này vào trình duyệt:</p>
            <div class="link-fallback"><?= htmlspecialchars($ctaLink) ?></div>
        <?php endif; ?>

        <p style="margin-top: 24px;"><?= htmlspecialchars($footerNote) ?></p>

        <div class="footer">
            &copy; <?= date('Y') ?> Creono – Nền tảng tài liệu số C2C.<br>
            <a href="<?= URLROOT ?>"><?= URLROOT ?></a>
        </div>
    </div>
</body>
</html>
