<?php
/**
 * Email template – Đặt lại mật khẩu
 * @var string $resetLink
 */
$emailTitle = 'Xin chào,';
$emailContent = '<p>Bạn đã yêu cầu đặt lại mật khẩu trên <strong>Creono</strong>.</p>'
    . '<p>Nhấn vào nút bên dưới để đặt lại mật khẩu (có hiệu lực trong <strong>15 phút</strong>):</p>';
$ctaText = 'Đặt lại mật khẩu';
$ctaLink = $resetLink;
$footerNote = 'Nếu bạn không yêu cầu, vui lòng bỏ qua email này.';

require APPROOT . '/Views/emails/layout.php';