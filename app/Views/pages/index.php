<?php /** @var array $data */ ?>
<!-- Nạp Header -->
<?php require APPROOT . '/Views/inc/header.php'; ?>

<!-- Nội dung riêng của trang chủ -->
<div class="p-5 mb-4 bg-light rounded-3 text-center border">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-primary"><?php echo $data['title']; ?></h1>
        <p class="col-md-8 fs-4 mx-auto"><?php echo $data['description']; ?></p>
        <a class="btn btn-primary btn-lg" href="<?php echo URLROOT; ?>/pages/about" role="button">
            Tìm hiểu thêm về nền tảng
        </a>
    </div>
</div>

<!-- Nạp Footer -->
<?php require APPROOT . '/Views/inc/footer.php'; ?>