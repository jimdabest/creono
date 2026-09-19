<?php
// app/Helpers/mail_helper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes (đường dẫn từ thư mục gốc dự án)
require_once __DIR__ . '/../../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/SMTP.php';

/**
 * Gửi email qua SMTP
 *
 * @param string $to      Địa chỉ người nhận
 * @param string $subject Tiêu đề email
 * @param string $body    Nội dung HTML
 * @param string $altBody Nội dung plain-text (fallback)
 * @return bool
 */
function sendEmail(string $to, string $subject, string $body, string $altBody = ''): bool
{
    // Đảm bảo file cấu hình email đã được load
    if (!defined('SMTP_HOST')) {
        require_once __DIR__ . '/../../config/email.php';
    }

    $mail = new PHPMailer(true);

    try {
         $mail->CharSet = 'UTF-8';
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        // Người gửi và người nhận
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        return $mail->send();
    } catch (Exception $e) {
        // Ghi log lỗi (hàm logError đã có trong error_helper.php)
        if (function_exists('logError')) {
            logError('Gửi email thất bại: ' . $e->getMessage(), ['to' => $to, 'subject' => $subject]);
        }
        return false;
    }
}

/**
 * Gửi email theo template chuẩn của Creono
 *
 * @param string $to
 * @param string $subject
 * @param string $title
 * @param string $content
 * @param string|null $ctaText
 * @param string|null $ctaLink
 * @param string $footerNote
 * @return bool
 */
function sendTemplatedEmail(
    string $to,
    string $subject,
    string $title,
    string $content,
    ?string $ctaText = null,
    ?string $ctaLink = null,
    string $footerNote = ''
): bool {
    $subject = trim($subject);
    if ($subject !== '' && stripos($subject, '[Creono]') !== 0) {
        $subject = '[Creono] ' . $subject;
    }

    $emailTitle = $title;
    $emailContent = $content;
    $ctaText = $ctaText ?? '';
    $ctaLink = $ctaLink ?? '';
    $footerNote = $footerNote ?: 'Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.';

    ob_start();
    require APPROOT . '/Views/emails/layout.php';
    $body = ob_get_clean();

    $altBody = trim(preg_replace('/\s+/', ' ', strip_tags($content)));

    $sent = sendEmail($to, $subject, $body, $altBody);

    if (!$sent && function_exists('logError')) {
        logError('Gửi email template thất bại', [
            'to' => $to,
            'subject' => $subject,
            'title' => $title,
        ]);
    }

    return $sent;
}