<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div style="max-width: 700px; margin: 40px auto; text-align: center;">
    <h1 style="font-size: 40px; margin-bottom: 12px;"><?php echo $data['title']; ?></h1>
    <p style="font-size: 20px; color: var(--apple-gray);">
        Đây là dự án hệ thống C2C Marketplace bán tài liệu số được xây dựng hoàn toàn bằng kiến trúc MVC thuần kết hợp PHP & MySQL.
    </p>
    <p style="color: var(--apple-gray-light); margin-top: 20px; font-size: 15px;">Phiên bản: 1.0.0</p>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>