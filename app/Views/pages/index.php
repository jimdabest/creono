<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container hero-section">
    <div class="hero-content">
        <h1 class="hero-title"><?php echo $data['title']; ?></h1>
        <p class="hero-desc"><?php echo $data['description']; ?></p>
        
        <div class="hero-buttons">
            <a href="<?php echo URLROOT; ?>/products/index" class="btn btn-hero">🛒 Khám phá ngay</a>
            <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-light btn-hero">📝 Đăng ký tham gia</a>
        </div>
    </div>

    <!-- Features -->
    <div class="features-grid">
        <div class="card feature-card">
            <h3>📚 Tài liệu chất lượng</h3>
            <p>Hàng ngàn tài liệu, mã nguồn, template chất lượng từ cộng đồng.</p>
        </div>
        <div class="card feature-card">
            <h3>💰 Giao dịch an toàn</h3>
            <p>Hệ thống thanh toán bảo mật, bảo vệ quyền lợi người mua và người bán.</p>
        </div>
        <div class="card feature-card">
            <h3>🤝 Cộng đồng phát triển</h3>
            <p>Kết nối với hàng ngàn người dùng, cùng nhau phát triển kiến thức.</p>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>