<?php /** @var array $data */ ?>
<!-- Nạp Header -->
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- Nội dung riêng của trang About -->
<div class="row justify-content-center">
    <div class="col-md-8 text-center mt-5">
        <h1 class="mb-3"><?php echo $data['title']; ?></h1>
        <p class="lead">Đây là dự án hệ thống C2C Marketplace bán tài liệu số được xây dựng hoàn toàn bằng kiến trúc MVC thuần kết hợp PHP & MySQL.</p>
        <p class="text-muted">Phiên bản: 1.0.0</p>
    </div>
</div>

<!-- Nạp Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>